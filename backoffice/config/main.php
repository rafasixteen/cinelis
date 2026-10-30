<?php

return [
	'id' => 'app-backoffice',
	'language' => 'pt-PT',
	'basePath' => dirname(__DIR__),
	'vendorPath' => dirname(__DIR__, 2).'/vendor',
	'aliases' => [
		'@backoffice' => dirname(__DIR__),
		'@bower' => dirname(__DIR__, 2).'/vendor/bower-asset',
		'@npm' => dirname(__DIR__, 2).'/vendor/npm-asset',
	],
	'controllerNamespace' => 'backoffice\controllers',
	'bootstrap' => YII_ENV_DEV ? ['gii'] : [],
	'modules' => YII_ENV_DEV ? [
		'gii' => [
			'allowedIPs' => ['127.0.0.1', '::1', '172.16.0.0/12'],
			'class' => 'yii\gii\Module',
		],
	] : [],
	'defaultRoute' => 'home/index',
	'layout' => 'main',
	'components' => [
		'request' => [
			'cookieValidationKey' => $_ENV['BACKOFFICE_COOKIE_VALIDATION_KEY'],
			'baseUrl' => $_SERVER['HTTP_X_FORWARDED_PREFIX'] ?? '',
		],
		'db' => require dirname(__DIR__, 2).'/common/config/db.php',
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => [
				'' => 'home/index',
				'login' => 'auth/login',
				'movies' => 'movies/index',
				'movies/create' => 'movies/create',
				'movies/<id:\d+>/edit' => 'movies/edit',
				'rooms' => 'rooms/index',
				'rooms/create' => 'rooms/create',
				'rooms/<id:\d+>/layout' => 'rooms/layout',
				'sessions' => 'sessions/index',
				'sessions/create' => 'sessions/create',
				'sessions/<id:\d+>/edit' => 'sessions/edit',
				'sessions/<session:\d+>/tickets' => 'tickets/index',
				'tickets' => 'tickets/index',
				'users' => 'users/index',
				'users/create' => 'users/create',
				'users/<id:\d+>/edit' => 'users/edit',
				'orders' => 'orders/index',
				'orders/<id:\d+>' => 'orders/view',
				'settings' => 'settings/index',
			],
		],
	],
];
