<?php

namespace backoffice\controllers;

use yii\web\Controller;

final class HomeController extends Controller
{
	public function actionIndex(): string
	{
		return $this->render('index');
	}
}
