<?php

declare(strict_types=1);

namespace backoffice\controllers;

use common\models\LoginForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

final class AuthController extends Controller
{
	public function behaviors(): array
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'actions' => ['login'],
						'allow' => true,
						'roles' => ['?'],
					],
					[
						'actions' => ['logout'],
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'login' => ['GET', 'POST'],
					'logout' => ['POST'],
				],
			],
		];
	}

	public function actionLogin(): Response|string
	{
		$model = new LoginForm();

		if (
			Yii::$app->request->isPost
			&& $model->load(Yii::$app->request->post())
			&& $model->login()
		) {
			return $this->redirect(['/home/index']);
		}

		return $this->render('login', ['model' => $model]);
	}

	public function actionLogout(): Response
	{
		Yii::$app->user->logout();

		return $this->redirect(['/auth/login']);
	}
}
