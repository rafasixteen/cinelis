<?php

declare(strict_types=1);

ob_start();

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/vendor/yiisoft/yii2/Yii.php';
require dirname(__DIR__).'/common/config/bootstrap.php';

use common\models\Room;
use common\models\Seat;
use common\models\User;
use common\rbac\RbacInstaller;
use yii\web\Response;

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/admin/rooms';
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

$layout = static function (): array {
	return [
		'version' => 1,
		'grid' => ['rows' => 2, 'columns' => 3],
		'screen' => ['label' => 'Ecrã grande', 'row' => 0, 'column' => 0, 'rowSpan' => 1, 'columnSpan' => 3],
		'entrances' => [['id' => 'main', 'label' => 'Entrada principal', 'row' => 3, 'column' => 1]],
		'exits' => [['id' => 'emergency', 'label' => 'Saída de emergência', 'row' => 3, 'column' => 3]],
		'cells' => [
			['row' => 1, 'column' => 1, 'type' => 'seat'],
			['row' => 1, 'column' => 2, 'type' => 'seat'],
			['row' => 1, 'column' => 3, 'type' => 'aisle'],
		],
	];
};

$employee = new User([
	'username' => 'room-test-'.uniqid(),
	'email' => 'room-'.uniqid().'@example.test',
	'password' => 'password123',
	'status' => User::STATUS_ACTIVE,
]);
$assert($employee->save(), implode('; ', $employee->getErrorSummary(true)));
RbacInstaller::assignRole(Yii::$app->authManager, $employee->id, RbacInstaller::ROLE_EMPLOYEE);
$runAction = static fn (string $route, array $params = []): mixed => Yii::$app->runAction($route, $params);
Yii::$app->user->login($employee, 0);
Yii::$app->user->logout();

$run('room routes use UUIDs', static function () use ($config, $assert): void {
	$rules = array_keys($config['components']['urlManager']['rules']);
	$assert(in_array('rooms/<id:[A-Fa-f0-9-]+>/layout', $rules, true), 'room layout route must accept UUIDs');
});

$run('anonymous users are denied room access', static function () use ($runAction, $request, $assert): void {
	Yii::$app->user->logout();
	$request('GET');
	$runAction('rooms/index');
	$assert(Yii::$app->response->statusCode === 302, 'anonymous access must redirect to login');
});

$request('GET');
Yii::$app->user->login($employee, 0);
$createdRoomId = null;
$firstSeatId = null;

$run('room creation persists layout metadata and seats', static function () use ($runAction, $request, $layout, $assert, &$createdRoomId, &$firstSeatId): void {
	$name = 'Room controller test '.uniqid();
	$request('POST', [], [
		'Room' => ['name' => $name],
		'layout' => json_encode($layout(), JSON_THROW_ON_ERROR),
	]);
	$response = $runAction('rooms/create');
	$room = Room::findOne(['name' => $name]);

	$assert($response instanceof Response && $response->statusCode === 302, 'valid room create must redirect');
	$assert($room instanceof Room, 'room must be persisted');
	$assert(Seat::find()->where(['room_id' => $room->id])->count() === 2, 'seat cells must create seats');
	$assert(is_array($room->layout) && $room->layout['screen']['label'] === 'Ecrã grande', 'layout metadata must be persisted');
	$createdRoomId = $room->id;
	$firstSeatId = (string) Seat::find()->where(['room_id' => $room->id, 'row' => 'A', 'number' => 1])->select('id')->scalar();
});

$run('layout update preserves seat UUIDs and metadata', static function () use ($runAction, $request, $layout, $assert, &$createdRoomId, &$firstSeatId): void {
	$assert(is_string($createdRoomId) && is_string($firstSeatId), 'room creation prerequisite failed');
	$next = $layout();
	$next['screen']['label'] = 'Ecrã atualizado';
	$next['cells'][] = ['row' => 2, 'column' => 1, 'type' => 'seat'];
	$request('POST', [], ['layout' => json_encode($next, JSON_THROW_ON_ERROR)]);
	$response = $runAction('rooms/layout', ['id' => $createdRoomId]);

	$room = Room::findOne($createdRoomId);
	$updatedSeatId = (string) Seat::find()->where(['room_id' => $createdRoomId, 'row' => 'A', 'number' => 1])->select('id')->scalar();
	$assert($response instanceof Response && $response->statusCode === 302, 'valid layout update must redirect');
	$assert($updatedSeatId === $firstSeatId, 'existing seat UUID must remain stable');
	$assert(Seat::find()->where(['room_id' => $createdRoomId])->count() === 3, 'new layout seat must be created');
	$assert(is_array($room->layout) && $room->layout['screen']['label'] === 'Ecrã atualizado', 'updated layout metadata must persist');
});

$run('missing room produces a controlled 404', static function () use ($runAction, $request, $assert): void {
	$request('GET');
	$thrown = false;

	try {
		$runAction('rooms/layout', ['id' => '00000000-0000-0000-0000-000000000000']);
	} catch (yii\web\NotFoundHttpException) {
		$thrown = true;
	}
	$assert($thrown, 'missing room must produce NotFoundHttpException');
});

$run('malformed layout input does not mutate the room', static function () use ($runAction, $request, $createdRoomId, $assert): void {
	$assert(is_string($createdRoomId), 'room creation prerequisite failed');
	$request('POST', [], ['layout' => '{invalid-json']);
	$response = $runAction('rooms/layout', ['id' => $createdRoomId]);
	$room = Room::findOne($createdRoomId);
	$assert(is_string($response), 'malformed layout must re-render the form');
	$assert(is_array($room->layout) && $room->layout['screen']['label'] === 'Ecrã atualizado', 'malformed layout must preserve persisted metadata');
});

$run('invalid layout rolls back room creation', static function () use ($runAction, $request, $layout, $assert): void {
	$invalid = $layout();
	$invalid['cells'][] = $invalid['cells'][0];
	$name = 'Room rollback '.uniqid();
	$request('POST', [], [
		'Room' => ['name' => $name],
		'layout' => json_encode($invalid, JSON_THROW_ON_ERROR),
	]);
	$response = $runAction('rooms/create');
	$assert(is_string($response), 'invalid layout must re-render the room form');
	$assert(Room::find()->where(['name' => $name])->exists() === false, 'invalid layout must roll back the room row');
});

if ($transaction->isActive) {
	$transaction->rollBack();
}

ob_end_flush();
exit($failures === 0 ? 0 : 1);
