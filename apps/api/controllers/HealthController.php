<?php

namespace api\controllers;

use yii\web\Controller;

class HealthController extends Controller
{
	public function actionIndex(): array
	{
		return [
			'status' => 'ok',
		];
	}
}
