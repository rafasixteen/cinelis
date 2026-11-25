<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/vendor/yiisoft/yii2/Yii.php';
require dirname(__DIR__).'/common/config/bootstrap.php';

$config = require dirname(__DIR__).'/console/config/main.php';
new yii\console\Application($config);

use common\rbac\RbacDefinitions;

$manager = Yii::$app->authManager;
Yii::$app->runAction('rbac/init');

$backOfficePermissions = [
	'backoffice.dashboard.view',
	'movies.manage',
	'rooms.manage',
	'sessions.manage',
	'tickets.view',
	'orders.view',
];

$assertions = [];

foreach ($backOfficePermissions as $permission) {
	$assertions[] = [
		'name' => "visitor lacks {$permission}",
		'passed' => !in_array($permission, array_keys($manager->getPermissionsByRole(RbacDefinitions::ROLE_VISITOR)), true),
	];
	$assertions[] = [
		'name' => "customer lacks {$permission}",
		'passed' => !in_array($permission, array_keys($manager->getPermissionsByRole(RbacDefinitions::ROLE_CUSTOMER)), true),
	];
	$assertions[] = [
		'name' => "employee has {$permission}",
		'passed' => in_array($permission, array_keys($manager->getPermissionsByRole(RbacDefinitions::ROLE_EMPLOYEE)), true),
	];
	$assertions[] = [
		'name' => "admin has {$permission}",
		'passed' => in_array($permission, array_keys($manager->getPermissionsByRole(RbacDefinitions::ROLE_ADMIN)), true),
	];
}

$assertions[] = [
	'name' => 'admin has users.manage',
	'passed' => in_array(
		RbacDefinitions::PERMISSION_USERS_MANAGE,
		array_keys($manager->getPermissionsByRole(RbacDefinitions::ROLE_ADMIN)),
		true,
	),
];

$failed = array_filter($assertions, static fn (array $assertion): bool => !$assertion['passed']);

foreach ($assertions as $assertion) {
	echo ($assertion['passed'] ? 'PASS' : 'FAIL').": {$assertion['name']}\n";
}

exit($failed === [] ? 0 : 1);
