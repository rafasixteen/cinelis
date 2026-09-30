<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require dirname(__DIR__, 3) . '/vendor/autoload.php';
require dirname(__DIR__, 3) . '/vendor/yiisoft/yii2/Yii.php';

require dirname(__DIR__, 3) . '/packages/common/config/bootstrap.php';

$config = require dirname(__DIR__) . '/config/main.php';

(new yii\web\Application($config))->run();
