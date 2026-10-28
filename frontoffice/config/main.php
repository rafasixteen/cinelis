<?php

return [
	'id' => 'app-frontoffice',
	'basePath' => dirname(__DIR__),
	'vendorPath' => dirname(__DIR__, 2).'/vendor',
	'aliases' => [
		'@bower' => dirname(__DIR__, 2).'/vendor/bower-asset',
		'@npm' => dirname(__DIR__, 2).'/vendor/npm-asset',
	],
	'controllerNamespace' => 'frontoffice\controllers',
	'defaultRoute' => 'home/index',
	'language' => 'pt-PT',
	'layout' => 'main',
	'components' => [
		'request' => [
			'cookieValidationKey' => $_ENV['FRONTOFFICE_COOKIE_VALIDATION_KEY'],
			'baseUrl' => $_SERVER['HTTP_X_FORWARDED_PREFIX'] ?? '',
		],
		'errorHandler' => ['errorAction' => 'site/error'],
		'db' => require dirname(__DIR__, 2).'/common/config/db.php',
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => [
				'' => 'home/index',
				'movies' => 'movie/index',
				'movies/<id:[a-z0-9-]+>' => 'movie/view',
				'sessions' => 'session/index',
				'sessions/<id:[a-z0-9-]+>/seats' => 'session/seats',
				'checkout' => 'checkout/index',
				'checkout/confirmation' => 'checkout/confirmation',
				'tickets' => 'ticket/index',
				'tickets/<id:[A-Za-z0-9-]+>' => 'ticket/view',
				'account' => 'account/index',
				'account/<action:login|register|history|recover>' => 'account/<action>',
				'<action:contact|help|privacy>' => 'site/<action>',
			],
		],
	],
];
