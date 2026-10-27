<?php

require dirname(__DIR__, 2) . '/vendor/autoload.php';
require dirname(__DIR__, 2) . '/vendor/yiisoft/yii2/Yii.php';

require dirname(__DIR__, 2) . '/common/config/bootstrap.php';

defined('YII_DEBUG') or define(
	'YII_DEBUG',
	filter_var($_ENV['YII_DEBUG'] ?? false, FILTER_VALIDATE_BOOL),
);
defined('YII_ENV') or define('YII_ENV', $_ENV['YII_ENV'] ?? 'prod');

$config = require dirname(__DIR__) . '/config/main.php';

(new yii\web\Application($config))->run();
