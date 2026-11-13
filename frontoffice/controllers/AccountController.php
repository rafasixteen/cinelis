<?php

namespace frontoffice\controllers;

use common\models\LoginForm;
use common\models\User;
use common\rbac\RbacInstaller;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

final class AccountController extends PreviewController
{
	public function behaviors(): array
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'actions' => ['login', 'register', 'recover'],
						'allow' => true,
						'roles' => ['?'],
					],
					[
						'actions' => ['index', 'history', 'logout'],
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'login' => ['GET', 'POST'],
					'register' => ['GET', 'POST'],
					'recover' => ['GET', 'POST'],
					'logout' => ['POST'],
				],
			],
		];
	}

	public function actionIndex(): string
	{
		return $this->render('index', ['identity' => Yii::$app->user->identity]);
	}

	public function actionLogin(): Response|string
	{
		$model = new LoginForm();

		if (Yii::$app->request->isPost
			&& $model->load(Yii::$app->request->post())
			&& $model->login()) {
			return $this->redirect(['/account/index']);
		}

		return $this->render(
			'login',
			[
				'register' => false,
				'loginModel' => $model,
				'registerModel' => new User(),
			],
		);
	}

	public function actionRegister(): Response|string
	{
		$model = new User();
		$model->status = User::STATUS_ACTIVE;

		if (Yii::$app->request->isPost
			&& $model->load(Yii::$app->request->post())
			&& $model->save()) {
			RbacInstaller::assignRole(
				Yii::$app->authManager,
				$model->getId(),
				RbacInstaller::ROLE_CUSTOMER,
			);
			Yii::$app->user->login($model, 30 * 24 * 60 * 60);

			return $this->redirect(['/account/index']);
		}

		return $this->render(
			'login',
			[
				'register' => true,
				'loginModel' => new LoginForm(),
				'registerModel' => $model,
			],
		);
	}

	public function actionHistory(): string
	{
		return $this->render('history');
	}

	public function actionRecover(): string
	{
		return $this->render('recover');
	}

	public function actionLogout(): Response
	{
		Yii::$app->user->logout();

		return $this->redirect(['/home/index']);
	}
}
