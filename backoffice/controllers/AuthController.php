<?php

namespace backoffice\controllers;

final class AuthController extends MockController
{
	public function actionLogin(): string
	{
		return $this->render('login');
	}
}
