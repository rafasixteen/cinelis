<?php

return [
	'id' => 'app-api',
	'basePath' => dirname(__DIR__),
	'controllerNamespace' => 'api\controllers',
	'components' => [
		'request' => [
			'baseUrl' => $_SERVER['HTTP_X_FORWARDED_PREFIX'] ?? '',
		],
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => [
				'GET health' => 'health/index',
			],
		],
		'response' => [
			'format' => yii\web\Response::FORMAT_JSON,
		],
	],
];
