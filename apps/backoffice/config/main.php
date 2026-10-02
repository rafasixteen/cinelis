<?php

return [
	'id' => 'app-backoffice',
	'basePath' => dirname(__DIR__),
	'controllerNamespace' => 'backoffice\controllers',
	'defaultRoute' => 'home/index',
	'layout' => 'main',
	'components' => [
		'request' => [
			'cookieValidationKey' => $_ENV['BACKOFFICE_COOKIE_VALIDATION_KEY'],
		],
		'db' => require dirname(__DIR__, 3) . '/packages/common/config/db.php',
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => [
				'' => 'home/index',
			],
		],
	],
];
