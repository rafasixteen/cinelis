<?php

use Dotenv\Dotenv;

$root = dirname(__DIR__, 3);

Dotenv::createImmutable($root)->safeLoad();

Yii::setAlias('@root', $root);
Yii::setAlias('@common', dirname(__DIR__));
Yii::setAlias('@api', $root . '/apps/api');
Yii::setAlias('@backoffice', $root . '/apps/backoffice');
Yii::setAlias('@frontoffice', $root . '/apps/frontoffice');
Yii::setAlias('@console', $root . '/apps/console');
