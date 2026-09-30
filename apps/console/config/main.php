<?php

return [
	'id' => 'app-console',
	'basePath' => dirname(__DIR__),
	'controllerNamespace' => 'console\controllers',
	'components' => [
		'db' => require dirname(__DIR__, 3) . '/packages/common/config/db.php',
	],
	'controllerMap' => [
		'migrate' => [
			'class' => yii\console\controllers\MigrateController::class,
			'migrationPath' => [
				'@console/migrations',
			],
		],
	],
];
