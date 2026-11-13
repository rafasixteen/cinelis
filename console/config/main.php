<?php

use yii\helpers\ArrayHelper;

return ArrayHelper::merge(
	require dirname(__DIR__, 2).'/common/config/main.php',
	[
		'id' => 'app-console',
		'basePath' => dirname(__DIR__),
		'controllerNamespace' => 'console\controllers',
		'components' => [
			'db' => require dirname(__DIR__, 2).'/common/config/db.php',
		],
		'controllerMap' => [
			'migrate' => [
				'class' => yii\console\controllers\MigrateController::class,
				'migrationPath' => [
					'@console/migrations',
				],
			],
		],
	],
);
