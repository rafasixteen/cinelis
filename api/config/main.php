<?php

return [
	'id' => 'app-api',
	'basePath' => dirname(__DIR__),
	'controllerNamespace' => 'api\controllers',
	'components' => [
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
