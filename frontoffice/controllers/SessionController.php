<?php

namespace frontoffice\controllers;

use frontoffice\data\Catalog;

final class SessionController extends PreviewController
{
	public function actionIndex(string $movie = '', string $date = '2026-10-27'): string
	{
		$date = Catalog::day($date);
		$sessions = Catalog::sessions($movie === '' ? null : $movie, $date);

		return $this->render(
			'index',
			compact('sessions', 'movie', 'date'),
		);
	}

	public function actionSeats(string $id, string $seats = 'A2,A3'): string
	{
		return $this->render(
			'seats',
			[
				'session' => Catalog::session($id),
				'selectedSeats' => Catalog::seats($seats),
			],
		);
	}
}
