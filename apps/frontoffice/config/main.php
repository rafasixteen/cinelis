<?php

return [
	'id' => 'app-frontoffice',
	'basePath' => dirname(__DIR__),
	'controllerNamespace' => 'frontoffice\controllers',
	'defaultRoute' => 'home/index',
	'layout' => 'main',
	'components' => [
		'request' => [
			'cookieValidationKey' => $_ENV['FRONTOFFICE_COOKIE_VALIDATION_KEY'],
		],
		'db' => require dirname(__DIR__, 3)	. '/common/config/db.php',
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => [
				'' => 'home/index',
			],
		],
	],
];
