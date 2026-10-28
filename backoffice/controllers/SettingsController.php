<?php

namespace backoffice\controllers;

final class SettingsController extends MockController
{
	public function actionIndex(): string
	{
		return $this->render('index');
	}
}
