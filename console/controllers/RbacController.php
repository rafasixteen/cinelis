<?php

namespace console\controllers;

use common\models\User;
use common\rbac\RbacInstaller;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

final class RbacController extends Controller
{
	public function actionInit(): int
	{
		RbacInstaller::install(Yii::$app->authManager);
		$this->stdout("RBAC roles and permissions installed.\n");

		return ExitCode::OK;
	}

	public function actionCreateAdmin(
		string $email,
		string $password,
		string $username = '',
	): int {
		RbacInstaller::install(Yii::$app->authManager);

		$user = User::findOne(['email' => $email]) ?? new User();
		$user->username = $username !== '' ? $username : $email;
		$user->email = $email;
		$user->setPassword($password);
		$user->generateAuthKey();
		$user->status = User::STATUS_ACTIVE;

		if (!$user->save()) {
			$this->stderr("Unable to create administrator:\n");
			$this->stderr(implode("\n", $user->getErrorSummary(true))."\n");

			return ExitCode::DATAERR;
		}

		RbacInstaller::assignRole(
			Yii::$app->authManager,
			$user->getId(),
			RbacInstaller::ROLE_ADMIN,
		);
		$this->stdout("Administrator {$user->email} is ready.\n");

		return ExitCode::OK;
	}
}
