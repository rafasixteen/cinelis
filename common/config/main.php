<?php

use common\rbac\RbacInstaller;

return [
	'components' => [
		'db' => require __DIR__.'/db.php',
		'authManager' => [
			'class' => yii\rbac\DbManager::class,
			'defaultRoles' => [RbacInstaller::ROLE_VISITOR],
		],
	],
];
