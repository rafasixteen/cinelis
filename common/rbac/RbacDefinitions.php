<?php

namespace common\rbac;

/**
 * Canonical RBAC names and metadata used throughout the applications.
 *
 * This class describes the authorization vocabulary; it does not persist
 * anything and it does not assign roles to users. The console
 * 'rbac/init' command reads these definitions and uses Yii's authManager to
 * seed the roles, permissions, and hierarchy in the configured backend.
 * User assignments are performed separately with authManager->assign().
 */
final class RbacDefinitions
{
	// Roles are named groups of permissions that can be assigned to users.
	public const ROLE_VISITOR = 'visitor';
	public const ROLE_CUSTOMER = 'customer';
	public const ROLE_EMPLOYEE = 'employee';
	public const ROLE_ADMIN = 'admin';

	// Permission names are the capabilities checked by AccessControl and can().
	public const PERMISSION_FRONT_OFFICE_VIEW = 'frontoffice.view';
	public const PERMISSION_FRONT_OFFICE_REGISTER = 'frontoffice.register';
	public const PERMISSION_FRONT_OFFICE_ACCOUNT = 'frontoffice.account';
	public const PERMISSION_BACK_OFFICE_ACCESS = 'backoffice.access';
	public const PERMISSION_BACK_OFFICE_DASHBOARD_VIEW = 'backoffice.dashboard.view';
	public const PERMISSION_MOVIES_MANAGE = 'movies.manage';
	public const PERMISSION_ROOMS_MANAGE = 'rooms.manage';
	public const PERMISSION_SESSIONS_MANAGE = 'sessions.manage';
	public const PERMISSION_TICKETS_VIEW = 'tickets.view';
	public const PERMISSION_ORDERS_VIEW = 'orders.view';
	public const PERMISSION_USERS_MANAGE = 'users.manage';

	/**
	 * Permission descriptions stored in Yii's auth_item table.
	 *
	 * The array key is the stable permission name used by authorization checks;
	 * the value is only descriptive metadata for administrators and tooling.
	 */
	public const PERMISSIONS = [
		self::PERMISSION_FRONT_OFFICE_VIEW => 'View the public front-office',
		self::PERMISSION_FRONT_OFFICE_REGISTER => 'Register a front-office account',
		self::PERMISSION_FRONT_OFFICE_ACCOUNT => 'Use an authenticated front-office account',
		self::PERMISSION_BACK_OFFICE_ACCESS => 'Access the back-office',
		self::PERMISSION_BACK_OFFICE_DASHBOARD_VIEW => 'View the back-office dashboard',
		self::PERMISSION_MOVIES_MANAGE => 'Manage movies in the back-office',
		self::PERMISSION_ROOMS_MANAGE => 'Manage rooms and seats in the back-office',
		self::PERMISSION_SESSIONS_MANAGE => 'Manage cinema sessions in the back-office',
		self::PERMISSION_TICKETS_VIEW => 'View tickets in the back-office',
		self::PERMISSION_ORDERS_VIEW => 'View orders in the back-office',
		self::PERMISSION_USERS_MANAGE => 'Manage users in the back-office',
	];

	/**
	 * Role descriptions stored in Yii's auth_item table.
	 *
	 * Roles do not grant access until the console initializer links them to
	 * permissions through ROLE_CHILDREN and users are assigned a role.
	 */
	public const ROLES = [
		self::ROLE_VISITOR => 'Anonymous front-office visitor',
		self::ROLE_CUSTOMER => 'Registered front-office customer',
		self::ROLE_EMPLOYEE => 'Back-office employee',
		self::ROLE_ADMIN => 'Back-office administrator',
	];

	/**
	 * Labels used when displaying a user's role in the backoffice.
	 * These labels are presentation text, not authorization names.
	 */
	public const ROLE_LABELS = [
		self::ROLE_VISITOR => 'Visitante',
		self::ROLE_CUSTOMER => 'Cliente',
		self::ROLE_EMPLOYEE => 'Funcionário',
		self::ROLE_ADMIN => 'Administrador',
	];

	/**
	 * RBAC inheritance and permission mapping used by the console initializer.
	 *
	 * Each key is a parent role and each value is a list of children. In Yii's
	 * hierarchy, the parent inherits every child, so an admin receives the
	 * employee role and the users.manage permission, while an employee receives
	 * the visitor role and the backoffice permissions listed below.
	 *
	 * Keep this structure free of user-specific assignments. Assignments belong
	 * to registration, user administration, or the create-admin console action.
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
			self::PERMISSION_BACK_OFFICE_DASHBOARD_VIEW,
			self::PERMISSION_MOVIES_MANAGE,
			self::PERMISSION_ROOMS_MANAGE,
			self::PERMISSION_SESSIONS_MANAGE,
			self::PERMISSION_TICKETS_VIEW,
			self::PERMISSION_ORDERS_VIEW,
		],
		self::ROLE_ADMIN => [
			self::ROLE_EMPLOYEE,
			self::PERMISSION_USERS_MANAGE,
		],
	];

	/**
	 * Returns the UI label for a role without changing its authorization name.
	 */
	public static function roleLabel(string $role): string
	{
		return self::ROLE_LABELS[$role] ?? $role;
	}
}
