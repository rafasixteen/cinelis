<?php

use yii\helpers\ArrayHelper;

return ArrayHelper::merge(
	require dirname(__DIR__, 2).'/common/config/main.php',
	[
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
				'generators' => [
					'model' => [
						'class' => 'yii\gii\generators\model\Generator',
						'ns' => 'common\\models',
						'queryNs' => 'common\\models',
					],
				],
			],
		] : [],
		'defaultRoute' => 'home/index',
		'layout' => 'main',
		'components' => [
			'request' => [
				'cookieValidationKey' => $_ENV['BACKOFFICE_COOKIE_VALIDATION_KEY'],
				'baseUrl' => $_SERVER['HTTP_X_FORWARDED_PREFIX'] ?? '',
			],
			'user' => [
				'identityClass' => common\models\User::class,
				'enableAutoLogin' => true,
				'loginUrl' => ['/auth/login'],
				'identityCookie' => [
					'name' => '_cinelis_identity',
					'path' => '/',
					'httpOnly' => true,
				],
			],
			'db' => require dirname(__DIR__, 2).'/common/config/db.php',
			'urlManager' => [
				'enablePrettyUrl' => true,
				'showScriptName' => false,
				'rules' => [
					'' => 'home/index',
					'login' => 'auth/login',
					'logout' => 'auth/logout',
					'movies' => 'movies/index',
					'movies/create' => 'movies/create',
					'movies/<id:[A-Fa-f0-9-]+>/edit' => 'movies/update',
					'rooms' => 'rooms/index',
					'rooms/create' => 'rooms/create',
					'rooms/<id:[A-Fa-f0-9-]+>/layout' => 'rooms/layout',
					'sessions' => 'sessions/index',
					'sessions/create' => 'sessions/create',
					'sessions/<id:[A-Fa-f0-9-]+>/edit' => 'sessions/update',
					'sessions/<id:[A-Fa-f0-9-]+>/cancel' => 'sessions/cancel',
					'tickets' => 'tickets/index',
					'tickets/<session:[A-Fa-f0-9-]+>' => 'tickets/index',
					'users' => 'users/index',
					'users/create' => 'users/create',
					'users/<id:[A-Za-z0-9-]+>' => 'users/view',
					'users/<id:[A-Za-z0-9-]+>/edit' => 'users/update',
					'users/<id:[A-Za-z0-9-]+>/delete' => 'users/delete',
					'users/<id:[A-Za-z0-9-]+>/restore' => 'users/restore',
					'orders' => 'orders/index',
					'orders/<id:[A-Fa-f0-9-]+>' => 'orders/view',
				],
			],
		],
	],
);
