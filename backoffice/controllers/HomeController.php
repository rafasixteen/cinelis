<?php

namespace backoffice\controllers;

use yii\data\ArrayDataProvider;
use yii\web\Controller;

final class HomeController extends Controller
{
	public function actionIndex(): string
	{
		$sessions = new ArrayDataProvider([
			'allModels' => [
				[
					'movie' => 'Dune: Part Two',
					'room' => 'Room 1',
					'time' => '18:30',
					'occupancy' => 86,
					'status' => 'Upcoming',
					'statusVariant' => 'info',
					'accent' => 'warning',
				],
				[
					'movie' => 'Past Lives',
					'room' => 'Room 3',
					'time' => '19:00',
					'occupancy' => 64,
					'status' => 'Selling',
					'statusVariant' => 'success',
					'accent' => 'info',
				],
				[
					'movie' => 'The Zone of Interest',
					'room' => 'Room 2',
					'time' => '20:15',
					'occupancy' => 48,
					'status' => 'Selling',
					'statusVariant' => 'success',
					'accent' => 'success',
				],
				[
					'movie' => 'Poor Things',
					'room' => 'Room 4',
					'time' => '21:00',
					'occupancy' => 91,
					'status' => 'Almost full',
					'statusVariant' => 'danger',
					'accent' => 'danger',
				],
			],
			'pagination' => false,
			'sort' => false,
		]);

		return $this->render('index', [
			'sessions' => $sessions,
		]);
	}
}
