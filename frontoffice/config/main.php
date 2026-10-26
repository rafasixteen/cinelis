<?php

return [
	'id' => 'app-frontoffice',
	'basePath' => dirname(__DIR__),
	'vendorPath' => dirname(__DIR__, 2) . '/vendor',
	'aliases' => [
		'@bower' => dirname(__DIR__, 2) . '/vendor/bower-asset',
		'@npm' => dirname(__DIR__, 2) . '/vendor/npm-asset',
	],
	'controllerNamespace' => 'frontoffice\controllers',
	'defaultRoute' => 'home/index',
	'layout' => 'main',
	'components' => [
		'request' => [
			'cookieValidationKey' => $_ENV['FRONTOFFICE_COOKIE_VALIDATION_KEY'],
		],
		'db' => require dirname(__DIR__, 2) . '/common/config/db.php',
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => [
				'' => 'home/index',
			],
		],
	],
];
