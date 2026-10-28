<?php

namespace backoffice\controllers;

use backoffice\data\Operations;

final class RoomsController extends MockController
{
	public function actionIndex(): string
	{
		return $this->render(
			'index',
			[
				'rooms' => Operations::rooms(),
			],
		);
	}

	public function actionCreate(): string
	{
		return $this->render('form');
	}

	public function actionLayout(int $id): string
	{
		return $this->render(
			'layout',
			[
				'room' => Operations::find('rooms', $id),
			],
		);
	}
}
