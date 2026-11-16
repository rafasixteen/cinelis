<?php

declare(strict_types=1);

ob_start();

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/vendor/yiisoft/yii2/Yii.php';
require dirname(__DIR__).'/common/config/bootstrap.php';

use common\models\Genre;
use common\models\Movie;
use common\models\MovieGenre;
use common\models\User;
use common\rbac\RbacInstaller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/admin/movies';
$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__).'/backoffice/web/index.php';
$_SERVER['SCRIPT_NAME'] = '/admin/index.php';

$config = require dirname(__DIR__).'/backoffice/config/main.php';
$config['components']['request']['enableCsrfValidation'] = false;
$config['components']['user']['enableAutoLogin'] = false;
new yii\web\Application($config);

$db = Yii::$app->db;
$transaction = $db->beginTransaction();
$failures = 0;

$assert = static function (bool $condition, string $message): void {
	if (!$condition) {
		throw new RuntimeException($message);
	}
};

$request = static function (string $method, array $query = [], array $body = []): void {
	$_SERVER['REQUEST_METHOD'] = $method;
	Yii::$app->request->setQueryParams($query);
	Yii::$app->request->setBodyParams($body);
};

$run = static function (string $name, callable $case) use (&$failures): void {
	try {
		$case();
		echo "PASS: {$name}\n";
	} catch (Throwable $error) {
		$failures++;
		fwrite(STDERR, "FAIL: {$name}: {$error->getMessage()}\n");
	}
};

$employee = new User([
	'username' => 'movie-test-'.uniqid(),
	'email' => 'movie-'.uniqid().'@example.test',
	'password' => 'password123',
	'status' => User::STATUS_ACTIVE,
]);
$assert($employee->save(), implode('; ', $employee->getErrorSummary(true)));
RbacInstaller::assignRole(Yii::$app->authManager, $employee->id, RbacInstaller::ROLE_EMPLOYEE);
$genres = Genre::find()->orderBy(['name' => SORT_ASC])->limit(2)->all();
$assert(count($genres) >= 2, 'the database must provide at least two genres');
$runAction = static fn (string $route, array $params = []): mixed => Yii::$app->runAction($route, $params);

$run('anonymous users are denied movie access', static function () use ($runAction, $request, $assert): void {
	Yii::$app->user->logout();
	$request('GET');

	$response = $runAction('movies/index');
	$actual = $response instanceof Response ? 'return status '.$response->statusCode : 'application status '.Yii::$app->response->statusCode;
	$assert(Yii::$app->response->statusCode === 302, 'anonymous access must redirect to login; got '.$actual);
});

Yii::$app->user->login($employee, 0);

$run('movie routes accept UUID parameters', static function () use ($config, $assert): void {
	$rules = array_keys($config['components']['urlManager']['rules']);
	$assert(in_array('movies/<id:[A-Fa-f0-9-]+>/edit', $rules, true), 'movie edit route must accept UUIDs');
});

$run('invalid movie creation is not persisted', static function () use ($runAction, $request, $assert): void {
	$before = Movie::find()->count();
	$request('POST', [], ['Movie' => ['duration_minutes' => 100]]);
	$runAction('movies/create');
	$assert(Movie::find()->count() === $before, 'invalid movie must not be persisted');
});

$createdMovieId = null;
$run('valid movie creation persists and synchronizes genres', static function () use ($runAction, $request, $genres, $assert, &$createdMovieId): void {
	$title = 'Movie controller test '.uniqid();
	$request('POST', [], [
		'Movie' => [
			'title' => $title,
			'duration_minutes' => 123,
			'release_date' => '2026-12-01',
			'age_rating' => 'M/12',
			'synopsis' => 'Persisted from the back-office controller.',
		],
		'genreIds' => [$genres[0]->id, $genres[1]->id],
	]);
	$response = $runAction('movies/create');
	$movie = Movie::findOne(['title' => $title]);

	$assert($response instanceof Response && $response->statusCode === 302, 'valid create must redirect');
	$assert($movie instanceof Movie, 'valid movie must be persisted');
	$assert($movie->duration_minutes === 123, 'movie attributes must be persisted');
	$assert(MovieGenre::find()->where(['movie_id' => $movie->id])->count() === 2, 'genre relations must be persisted');
	$createdMovieId = $movie->id;
});

$run('movie update persists changes and replaces genres', static function () use ($runAction, $request, $genres, $assert, &$createdMovieId): void {
	$assert(is_string($createdMovieId), 'movie creation prerequisite failed');
	$request('POST', [], [
		'Movie' => [
			'title' => 'Updated movie controller test',
			'duration_minutes' => 140,
			'release_date' => '2026-12-05',
			'age_rating' => 'M/16',
			'synopsis' => 'Updated from the back-office controller.',
		],
		'genreIds' => [$genres[0]->id],
	]);
	$response = $runAction('movies/update', ['id' => $createdMovieId]);
	$movie = Movie::findOne($createdMovieId);

	$assert($response instanceof Response && $response->statusCode === 302, 'valid update must redirect');
	$assert($movie->title === 'Updated movie controller test', 'movie title must update');
	$assert((int) $movie->duration_minutes === 140, 'movie duration must update');
	$assert(MovieGenre::find()->where(['movie_id' => $createdMovieId])->count() === 1, 'genre relations must be replaced');
});

$run('missing movie returns 404', static function () use ($runAction, $request, $assert): void {
	$request('GET');
	$thrown = false;

	try {
		$runAction('movies/update', ['id' => '00000000-0000-0000-0000-000000000000']);
	} catch (NotFoundHttpException) {
		$thrown = true;
	}
	$assert($thrown, 'missing movie must produce NotFoundHttpException');
});

if ($transaction->isActive) {
	$transaction->rollBack();
}

ob_end_flush();
exit($failures === 0 ? 0 : 1);
