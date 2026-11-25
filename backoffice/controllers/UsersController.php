<?php

declare(strict_types=1);

namespace backoffice\controllers;

use common\models\User;
use common\rbac\RbacDefinitions;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class UsersController extends Controller
{
	public function behaviors(): array
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow' => true,
						'roles' => [RbacDefinitions::PERMISSION_USERS_MANAGE],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'index' => ['GET'],
					'view' => ['GET'],
					'create' => ['GET', 'POST'],
					'update' => ['GET', 'POST'],
					'delete' => ['POST'],
					'restore' => ['POST'],
				],
			],
		];
	}

	public function actionIndex(): string
	{
		$query = User::find();
		$search = trim((string) Yii::$app->request->get('q', ''));

		if ($search !== '') {
			$query->andWhere([
				'or',
				['like', 'username', $search],
				['like', 'email', $search],
			]);
		}

		return $this->render(
			'index',
			[
				'provider' => new ActiveDataProvider([
					'query' => $query->orderBy(['created_at' => SORT_DESC]),
					'pagination' => [
						'pageSize' => 8,
						'pageSizeLimit' => false,
					],
				]),
				'q' => $search,
			],
		);
	}

	public function actionView(string $id): string
	{
		$model = $this->findUser($id);

		return $this->render('view', [
			'model' => $model,
			'role' => $this->getAssignedRole($model->getId()),
		]);
	}

	public function actionCreate(): string|Response
	{
		$model = new User();
		$model->status = User::STATUS_ACTIVE;
		$role = RbacDefinitions::ROLE_EMPLOYEE;

		if ($model->load(Yii::$app->request->post())) {
			$role = (string) Yii::$app->request->post('role', $role);

			if ($this->isValidRole($role) && $this->roleIsInitialized($role) && $model->save()) {
				$this->assignRole($model->getId(), $role);

				return $this->redirect(['view', 'id' => $model->id]);
			}
		}

		return $this->render('form', [
			'model' => $model,
			'role' => $role,
			'isNew' => true,
		]);
	}

	public function actionUpdate(string $id): string|Response
	{
		$model = $this->findUser($id);

		$role = $this->getAssignedRole($model->getId());

		if ($model->load(Yii::$app->request->post())) {
			$role = (string) Yii::$app->request->post('role', $role);

			if ($this->isValidRole($role) && $this->roleIsInitialized($role) && $model->save()) {
				$this->assignRole($model->getId(), $role);

				return $this->redirect(['view', 'id' => $model->id]);
			}
		}

		return $this->render('form', [
			'model' => $model,
			'role' => $role,
			'isNew' => false,
		]);
	}

	public function actionDelete(string $id): Response
	{
		$model = $this->findUser($id);

		if ((string) $model->id === (string) Yii::$app->user->id) {
			throw new ForbiddenHttpException('Não pode eliminar a sua própria conta.');
		}

		$model->status = User::STATUS_DELETED;
		$model->save(false, ['status']);

		return $this->redirect(['index']);
	}

	public function actionRestore(string $id): Response
	{
		$model = $this->findUser($id);
		$model->status = User::STATUS_ACTIVE;
		$model->save(false, ['status']);

		return $this->redirect(['index']);
	}

	private function findUser(string $id): User
	{
		if (!$this->isUuid($id)) {
			throw new NotFoundHttpException('Utilizador não encontrado.');
		}

		$model = User::findOne($id);

		if (!$model instanceof User) {
			throw new NotFoundHttpException('Utilizador não encontrado.');
		}

		return $model;
	}

	private function isValidRole(string $role): bool
	{
		return array_key_exists($role, RbacDefinitions::ROLES);
	}

	private function assignRole(string|int $userId, string $role): void
	{
		$authManager = Yii::$app->authManager;
		$roleItem = $authManager->getRole($role);

		if ($roleItem === null) {
			throw new \RuntimeException('The selected role is not initialized. Run yii rbac/init first.');
		}

		$authManager->revokeAll((string) $userId);
		$authManager->assign($roleItem, (string) $userId);
	}

	private function roleIsInitialized(string $role): bool
	{
		if (Yii::$app->authManager->getRole($role) !== null) {
			return true;
		}

		throw new \RuntimeException('The selected role is not initialized. Run yii rbac/init first.');
	}

	private function getAssignedRole(string|int $userId): string
	{
		$authManager = Yii::$app->authManager;

		foreach (array_keys($authManager->getAssignments((string) $userId)) as $role) {
			if ($authManager->getRole($role) !== null) {
				return $role;
			}
		}

		return RbacDefinitions::ROLE_VISITOR;
	}

	private function isUuid(string $value): bool
	{
		return preg_match(
			'/\A[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\z/i',
			$value,
		) === 1;
	}
}
