<?php

use Dotenv\Dotenv;

$root = dirname(__DIR__, 2);

Dotenv::createImmutable($root)->safeLoad();

Yii::setAlias('@root', $root);
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@api', $root.'/api');
Yii::setAlias('@backoffice', $root.'/backoffice');
Yii::setAlias('@frontoffice', $root.'/frontoffice');
Yii::setAlias('@console', $root.'/console');
