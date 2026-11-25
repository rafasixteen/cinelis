<?php

namespace console\controllers;

use common\models\User;
use common\rbac\RbacDefinitions;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\rbac\ManagerInterface;

final class RbacController extends Controller
{
	public function actionInit(): int
	{
		$this->installDefinitions(Yii::$app->authManager);
		$this->stdout("RBAC roles and permissions installed.\n");

		return ExitCode::OK;
	}

	public function actionCreateAdmin(
		string $email,
		string $password,
		string $username = '',
	): int {
		$authManager = Yii::$app->authManager;
		$adminRole = $authManager->getRole(RbacDefinitions::ROLE_ADMIN);

		if ($adminRole === null) {
			$this->stderr("Administrator role is not initialized. Run yii rbac/init first.\n");

			return ExitCode::CONFIG;
		}

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

		$authManager->revokeAll((string) $user->getId());
		$authManager->assign($adminRole, (string) $user->getId());
		$this->stdout("Administrator {$user->email} is ready.\n");

		return ExitCode::OK;
	}

	private function installDefinitions(ManagerInterface $authManager): void
	{
		foreach (RbacDefinitions::PERMISSIONS as $name => $description) {
			$permission = $authManager->getPermission($name);

			if ($permission === null) {
				$permission = $authManager->createPermission($name);
				$permission->description = $description;
				$authManager->add($permission);
			}
		}

		foreach (RbacDefinitions::ROLES as $name => $description) {
			if ($authManager->getRole($name) === null) {
				$role = $authManager->createRole($name);
				$role->description = $description;
				$authManager->add($role);
			}
		}

		foreach (RbacDefinitions::ROLE_CHILDREN as $parent => $children) {
			foreach ($children as $child) {
				$this->ensureChild($authManager, $parent, $child);
			}
		}
	}

	private function ensureChild(
		ManagerInterface $authManager,
		string $parent,
		string $child,
	): void {
		if (isset($authManager->getChildren($parent)[$child])) {
			return;
		}

		$parentItem = $authManager->getRole($parent) ?? $authManager->getPermission($parent);
		$childItem = $authManager->getRole($child) ?? $authManager->getPermission($child);

		if ($parentItem === null || $childItem === null) {
			throw new \LogicException("Cannot link missing RBAC item: {$parent} -> {$child}");
		}

		$authManager->addChild($parentItem, $childItem);
	}
}
