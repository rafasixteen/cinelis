<?php

namespace backoffice\controllers;

use backoffice\data\Operations;

final class MoviesController extends MockController
{
	public function actionIndex(): string
	{
		$q = $this->query('q');
		$status = $this->query('status');
		$records = array_filter(
			Operations::movies(),
			static fn (array $movie): bool => (
				($q === '' || mb_stripos($movie['title'], $q) !== false)
				&& ($status === '' || $movie['status'] === $status)
			),
		);

		return $this->render(
			'index',
			[
				'provider' => $this->provider($records),
				'q' => $q,
				'status' => $status,
			],
		);
	}

	public function actionCreate(): string
	{
		return $this->render(
			'form',
			[
				'record' => [
					'title' => '',
					'duration' => 120,
					'rating' => 'M/12',
					'premiere' => Operations::DATE,
					'genre' => 'Drama',
					'status' => 'Em breve',
					'synopsis' => '',
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
				'record' => Operations::find('movies', $id),
				'isNew' => false,
			],
		);
	}
}
