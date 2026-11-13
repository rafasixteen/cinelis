<?php

namespace common\rbac;

use yii\rbac\ManagerInterface;

final class RbacInstaller
{
	public const ROLE_VISITOR = 'visitor';
	public const ROLE_CUSTOMER = 'customer';
	public const ROLE_EMPLOYEE = 'employee';
	public const ROLE_ADMIN = 'admin';

	public const PERMISSION_FRONT_OFFICE_VIEW = 'frontoffice.view';
	public const PERMISSION_FRONT_OFFICE_REGISTER = 'frontoffice.register';
	public const PERMISSION_FRONT_OFFICE_ACCOUNT = 'frontoffice.account';
	public const PERMISSION_BACK_OFFICE_ACCESS = 'backoffice.access';
	public const PERMISSION_USERS_MANAGE = 'users.manage';

	public const PERMISSIONS = [
		self::PERMISSION_FRONT_OFFICE_VIEW => 'View the public front-office',
		self::PERMISSION_FRONT_OFFICE_REGISTER => 'Register a front-office account',
		self::PERMISSION_FRONT_OFFICE_ACCOUNT => 'Use an authenticated front-office account',
		self::PERMISSION_BACK_OFFICE_ACCESS => 'Access the back-office',
		self::PERMISSION_USERS_MANAGE => 'Manage users in the back-office',
	];

	public const ROLES = [
		self::ROLE_VISITOR => 'Anonymous front-office visitor',
		self::ROLE_CUSTOMER => 'Registered front-office customer',
		self::ROLE_EMPLOYEE => 'Back-office employee',
		self::ROLE_ADMIN => 'Back-office administrator',
	];

	public const ROLE_LABELS = [
		self::ROLE_VISITOR => 'Visitante',
		self::ROLE_CUSTOMER => 'Cliente',
		self::ROLE_EMPLOYEE => 'Funcionário',
		self::ROLE_ADMIN => 'Administrador',
	];

	/**
	 * RBAC inheritance and permission mapping.
	 *
	 * Each role inherits every child role or permission listed here.
	 */
	public const ROLE_CHILDREN = [
		self::ROLE_VISITOR => [
			self::PERMISSION_FRONT_OFFICE_VIEW,
			self::PERMISSION_FRONT_OFFICE_REGISTER,
		],
		self::ROLE_CUSTOMER => [
			self::ROLE_VISITOR,
			self::PERMISSION_FRONT_OFFICE_ACCOUNT,
		],
		self::ROLE_EMPLOYEE => [
			self::ROLE_VISITOR,
			self::PERMISSION_BACK_OFFICE_ACCESS,
		],
		self::ROLE_ADMIN => [
			self::ROLE_EMPLOYEE,
			self::PERMISSION_USERS_MANAGE,
		],
	];

	/**
	 * Creates the permissions, roles, inheritance graph, and default roles.
	 *
	 * This operation is idempotent and can safely be run during migrations or
	 * from the console RBAC initialization command.
	 *
	 * @param ManagerInterface $authManager Yii RBAC manager used to persist the definitions.
	 */
	public static function install(ManagerInterface $authManager): void
	{
		foreach (self::PERMISSIONS as $name => $description) {
			$permission = $authManager->getPermission($name);

			if ($permission === null) {
				$permission = $authManager->createPermission($name);
				$permission->description = $description;
				$authManager->add($permission);
			}
		}

		foreach (self::ROLES as $name => $description) {
			if ($authManager->getRole($name) === null) {
				$role = $authManager->createRole($name);
				$role->description = $description;
				$authManager->add($role);
			}
		}

		foreach (self::ROLE_CHILDREN as $parent => $children) {
			foreach ($children as $child) {
				self::ensureChild($authManager, $parent, $child);
			}
		}

		$authManager->setDefaultRoles([self::ROLE_VISITOR]);
	}

	/**
	 * Replaces all role assignments for a user with one explicit role.
	 *
	 * The assignment is persisted by Yii in the auth_assignment table. Role
	 * names are validated against the definitions installed by install().
	 *
	 * @param ManagerInterface $authManager Yii RBAC manager used to persist the assignment.
	 * @param string|int $userId User identity ID used by auth_assignment.
	 * @param string $role Role name to assign to the user.
	 * @throws \InvalidArgumentException when the role is not defined.
	 */
	public static function assignRole(
		ManagerInterface $authManager,
		string|int $userId,
		string $role,
	): void {
		self::install($authManager);
		$roleItem = $authManager->getRole($role);

		if ($roleItem === null) {
			throw new \InvalidArgumentException('Unknown RBAC role: '.$role);
		}

		$userId = (string) $userId;
		$authManager->revokeAll($userId);
		$authManager->assign($roleItem, $userId);
	}

	/**
	 * Returns the first directly assigned role for a user, or the visitor role
	 * when the user has no explicit assignment.
	 */
	public static function getUserRole(ManagerInterface $authManager, string|int $userId): string
	{
		$roles = $authManager->getRolesByUser((string) $userId);

		return array_key_first($roles) ?? self::ROLE_VISITOR;
	}

	/**
	 * Returns the localized display label for a role name.
	 */
	public static function getRoleLabel(string $role): string
	{
		return self::ROLE_LABELS[$role] ?? $role;
	}

	/**
	 * Adds one role or permission as a child of another RBAC item.
	 *
	 * A child role or permission is inherited by its parent. Existing links are
	 * left untouched so the installer remains safe to run repeatedly.
	 *
	 * @param ManagerInterface $authManager Yii RBAC manager containing the items.
	 * @param string $parent Parent role that receives the inherited access.
	 * @param string $child Child role or permission inherited by the parent.
	 * @throws \LogicException when either RBAC item is missing.
	 */
	private static function ensureChild(
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
