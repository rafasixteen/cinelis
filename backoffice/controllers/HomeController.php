<?php

namespace backoffice\controllers;

use backoffice\data\Operations;

final class HomeController extends MockController
{
	public function actionIndex(): string
	{
		return $this->render(
			'index',
			[
				'sessions' => Operations::sessions(),
				'orders' => array_slice(Operations::orders(), 0, 3),
			],
		);
	}
}
