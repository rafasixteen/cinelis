<?php

namespace backoffice\controllers;

use common\models\User;
use common\rbac\RbacInstaller;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

final class UsersController extends MockController
{
	public function behaviors(): array
	{
		return array_merge(
			parent::behaviors(),
			[
				'user-management' => [
					'class' => AccessControl::class,
					'only' => ['index', 'view', 'create', 'update', 'delete', 'restore'],
					'rules' => [
						[
							'allow' => true,
							'roles' => ['users.manage'],
						],
					],
				],
			],
		);
	}

	public function actionIndex(): string
	{
		$query = User::find();
		$search = $this->query('q');

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
			'role' => RbacInstaller::getUserRole(Yii::$app->authManager, $model->getId()),
		]);
	}

	public function actionCreate(): string
	{
		$model = new User();
		$model->status = User::STATUS_ACTIVE;
		$role = RbacInstaller::ROLE_EMPLOYEE;

		if ($model->load(Yii::$app->request->post())) {
			$role = (string) Yii::$app->request->post('role', $role);

			if ($this->isValidRole($role) && $model->save()) {
				RbacInstaller::assignRole(Yii::$app->authManager, $model->getId(), $role);

				return $this->redirect(['view', 'id' => $model->id]);
			}
		}

		return $this->render('form', [
			'model' => $model,
			'role' => $role,
			'isNew' => true,
		]);
	}

	public function actionUpdate(string $id): string
	{
		$model = $this->findUser($id);

		$role = RbacInstaller::getUserRole(Yii::$app->authManager, $model->getId());

		if ($model->load(Yii::$app->request->post())) {
			$role = (string) Yii::$app->request->post('role', $role);

			if ($this->isValidRole($role) && $model->save()) {
				RbacInstaller::assignRole(Yii::$app->authManager, $model->getId(), $role);

				return $this->redirect(['view', 'id' => $model->id]);
			}
		}

		return $this->render('form', [
			'model' => $model,
			'role' => $role,
			'isNew' => false,
		]);
	}

	public function actionDelete(string $id): string
	{
		$model = $this->findUser($id);

		if ((string) $model->id === (string) Yii::$app->user->id) {
			throw new ForbiddenHttpException('Não pode eliminar a sua própria conta.');
		}

		$model->status = User::STATUS_DELETED;
		$model->save(false, ['status']);

		return $this->redirect(['index']);
	}

	public function actionRestore(string $id): string
	{
		$model = $this->findUser($id);
		$model->status = User::STATUS_ACTIVE;
		$model->save(false, ['status']);

		return $this->redirect(['index']);
	}

	private function findUser(string $id): User
	{
		$model = User::findOne($id);

		if (!$model instanceof User) {
			throw new NotFoundHttpException('Utilizador não encontrado.');
		}

		return $model;
	}

	private function isValidRole(string $role): bool
	{
		return array_key_exists($role, RbacInstaller::ROLES);
	}
}
