<?php

namespace frontoffice\controllers;

final class AccountController extends PreviewController
{
	public function actionIndex(): string
	{
		return $this->render('index');
	}

	public function actionLogin(): string
	{
		return $this->render(
			'login',
			[
				'register' => false,
			],
		);
	}

	public function actionRegister(): string
	{
		return $this->render(
			'login',
			[
				'register' => true,
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
}
