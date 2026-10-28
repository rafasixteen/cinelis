<?php

namespace frontoffice\controllers;

use frontoffice\data\Catalog;

final class MovieController extends PreviewController
{
	public function actionIndex(string $q = '', string $genre = ''): string
	{
		$movies = array_filter(
			Catalog::movies(),
			static fn (array $movie): bool => (
				($q === '' || mb_stripos($movie['title'].' '.$movie['genre'], $q) !== false)
				&& ($genre === '' || $movie['genre'] === $genre)
			),
		);

		return $this->render(
			'index',
			compact('movies', 'q', 'genre'),
		);
	}

	public function actionView(string $id, string $date = '2026-10-27'): string
	{
		$movie = Catalog::movie($id);
		$date = Catalog::day($date);
		$sessions = Catalog::sessions($id, $date);

		return $this->render(
			'view',
			compact('movie', 'sessions', 'date'),
		);
	}
}
