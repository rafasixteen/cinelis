<?php

declare(strict_types=1);

ob_start();

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/vendor/yiisoft/yii2/Yii.php';
require dirname(__DIR__).'/common/config/bootstrap.php';

use backoffice\controllers\HomeController;
use common\models\User;
use common\rbac\RbacDefinitions;
use yii\base\InvalidRouteException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/admin/';
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

$request = static function (string $method = 'GET'): void {
	$_SERVER['REQUEST_METHOD'] = $method;
	Yii::$app->request->setQueryParams([]);
	Yii::$app->request->setBodyParams([]);
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

$run('dashboard occupancy date range uses the selected valid date', static function () use ($assert): void {
	$today = new DateTimeImmutable('2026-11-15 00:00:00', new DateTimeZone('Europe/Lisbon'));
	$range = HomeController::dateRange('2026-11-16', $today);

	$assert($range['date'] === '2026-11-16', 'dashboard should retain the selected date');
	$assert($range['start'] === '2026-11-16 00:00:00', 'dashboard range should start at selected date');
	$assert($range['end'] === '2026-11-17 00:00:00', 'dashboard range should end at the next date');
});

$run('dashboard occupancy date range falls back to today for invalid dates', static function () use ($assert): void {
	$today = new DateTimeImmutable('2026-11-15 00:00:00', new DateTimeZone('Europe/Lisbon'));
	$range = HomeController::dateRange('not-a-date', $today);

	$assert($range['date'] === '2026-11-15', 'invalid dashboard date should fall back to today');
	$assert($range['start'] === '2026-11-15 00:00:00', 'fallback range should start at today');
	$assert($range['end'] === '2026-11-16 00:00:00', 'fallback range should end tomorrow');
});

$controllerFiles = glob(dirname(__DIR__).'/backoffice/controllers/*Controller.php');
$run('backoffice controllers have no mock boundary or fixture imports', static function () use ($controllerFiles, $assert): void {
	foreach ($controllerFiles as $file) {
		$contents = file_get_contents($file);
		$assert(!str_contains($contents, 'MockController'), basename($file).' must not extend MockController');
		$assert(!str_contains($contents, 'backoffice\\data\\Operations'), basename($file).' must not import Operations');
		$assert(str_contains($contents, 'function behaviors'), basename($file).' must declare explicit behaviors');
	}
	$assert(!is_file(dirname(__DIR__).'/backoffice/controllers/MockController.php'), 'MockController must be removed');
	$assert(!is_file(dirname(__DIR__).'/backoffice/data/Operations.php'), 'Operations fixture must be removed');
	$assert(!is_file(dirname(__DIR__).'/backoffice/controllers/SettingsController.php'), 'settings controller must be removed');
	$assert(!is_file(dirname(__DIR__).'/backoffice/views/settings/index.php'), 'settings view must be removed');
});

$visitor = new User([
	'username' => 'auth-visitor-'.uniqid(),
	'email' => 'auth-visitor-'.uniqid().'@example.test',
	'password' => 'password123',
	'status' => User::STATUS_ACTIVE,
]);
$assert($visitor->save(), implode('; ', $visitor->getErrorSummary(true)));
Yii::$app->authManager->assign(
	Yii::$app->authManager->getRole(RbacDefinitions::ROLE_CUSTOMER),
	(string) $visitor->id,
);
$employee = new User([
	'username' => 'auth-employee-'.uniqid(),
	'email' => 'auth-employee-'.uniqid().'@example.test',
	'password' => 'password123',
	'status' => User::STATUS_ACTIVE,
]);
$assert($employee->save(), implode('; ', $employee->getErrorSummary(true)));
Yii::$app->authManager->assign(
	Yii::$app->authManager->getRole(RbacDefinitions::ROLE_EMPLOYEE),
	(string) $employee->id,
);
$admin = new User([
	'username' => 'auth-admin-'.uniqid(),
	'email' => 'auth-admin-'.uniqid().'@example.test',
	'password' => 'password123',
	'status' => User::STATUS_ACTIVE,
]);
$assert($admin->save(), implode('; ', $admin->getErrorSummary(true)));
Yii::$app->authManager->assign(
	Yii::$app->authManager->getRole(RbacDefinitions::ROLE_ADMIN),
	(string) $admin->id,
);

$runAction = static fn (string $route, array $params = []): mixed => Yii::$app->runAction($route, $params);
$denied = static function (callable $action): void {
	try {
		$action();
	} catch (ForbiddenHttpException) {
		return;
	}

	throw new RuntimeException('request should be denied');
};

$run('visitors cannot access the backoffice', static function () use ($runAction, $request, $denied, $assert): void {
	Yii::$app->user->logout();
	$request();

	try {
		$runAction('home/index');
	} catch (Throwable) {
		// AccessControl redirects guests to the login route.
	}
	$assert(Yii::$app->response->statusCode === 302, 'visitor should be sent to login');
});

$run('customers cannot access backoffice reports', static function () use ($runAction, $request, $visitor, $denied): void {
	Yii::$app->user->login($visitor, 0);
	foreach (['home/index', 'movies/index', 'tickets/index', 'orders/index'] as $route) {
		$request();
		$denied(static fn () => $runAction($route));
	}
});

$run('employees can access reports but not user management', static function () use ($runAction, $request, $employee, $denied, $assert): void {
	Yii::$app->user->login($employee, 0);
	foreach (['home/index', 'tickets/index', 'orders/index'] as $route) {
		$request();
		$response = $runAction($route);
		$assert(is_string($response), $route.' must be available to employees');
	}
	$request();
	$denied(static fn () => $runAction('users/index'));
});

$run('administrators can manage users', static function () use ($runAction, $request, $admin, $assert): void {
	Yii::$app->user->login($admin, 0);
	$request();
	$response = $runAction('users/index');
	$assert(is_string($response), 'administrators must access user management');
});

$run('settings route is gone', static function () use ($config, $runAction, $request, $assert): void {
	$rules = array_keys($config['components']['urlManager']['rules']);
	$assert(!in_array('settings', $rules, true), 'settings route must be removed');
	$request();
	$thrown = false;

	try {
		$runAction('settings/index');
	} catch (NotFoundHttpException|InvalidRouteException) {
		$thrown = true;
	}
	$assert($thrown, 'settings action must not resolve');
});

if ($transaction->isActive) {
	$transaction->rollBack();
}

ob_end_flush();
exit($failures === 0 ? 0 : 1);
