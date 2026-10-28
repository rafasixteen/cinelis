<?php

namespace backoffice\controllers;

use backoffice\data\Operations;

final class SessionsController extends MockController
{
	public function actionIndex(): string
	{
		$date = $this->query('date', Operations::DATE);
		$room = $this->query('room');
		$records = array_filter(
			Operations::sessions(),
			static fn (array $session): bool => (
				($date === '' || $session['date'] === $date)
				&& ($room === '' || (string) $session['roomId'] === $room)
			),
		);

		return $this->render(
			'index',
			[
				'provider' => $this->provider($records),
				'date' => $date,
				'room' => $room,
			],
		);
	}

	public function actionCreate(): string
	{
		return $this->render(
			'form',
			[
				'record' => [
					'movieId' => 1,
					'roomId' => 1,
					'date' => Operations::DATE,
					'time' => '20:30',
					'price' => 7.50,
				],
				'isNew' => true,
			],
		);
	}

	public function actionEdit(int $id): string
	{
		return $this->render(
			'form',
			[
				'record' => Operations::find('sessions', $id),
				'isNew' => false,
			],
		);
	}
}
