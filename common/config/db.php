<?php

use yii\db\Connection;

return [
	'class' => Connection::class,
	'dsn' => sprintf(
		'pgsql:host=%s;port=%s;dbname=%s',
		getenv('DB_HOST'),
		getenv('DB_PORT'),
		getenv('DB_NAME'),
	),
	'username' => getenv('DB_USER'),
	'password' => getenv('DB_PASSWORD'),
	'charset' => 'utf8',
];
