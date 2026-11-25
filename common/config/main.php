<?php

use common\rbac\RbacDefinitions;

return [
	'components' => [
		'assetManager' => [
			'appendTimestamp' => true,
		],
		'db' => require __DIR__.'/db.php',
		'authManager' => [
			'class' => yii\rbac\DbManager::class,
			'defaultRoles' => [RbacDefinitions::ROLE_VISITOR],
		],
	],
];
