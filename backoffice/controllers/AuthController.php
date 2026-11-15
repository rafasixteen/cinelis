<?php

namespace backoffice\controllers;

use common\models\LoginForm;
use Yii;
use yii\web\Response;

final class AuthController extends MockController
{
	public function actionLogin(): Response|string
	{
		$model = new LoginForm();

		if (Yii::$app->request->isPost
			&& $model->load(Yii::$app->request->post())
			&& $model->login()) {
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
