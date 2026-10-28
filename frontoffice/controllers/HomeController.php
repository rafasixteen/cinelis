<?php

namespace frontoffice\controllers;

use frontoffice\data\Catalog;

final class HomeController extends PreviewController
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
}
