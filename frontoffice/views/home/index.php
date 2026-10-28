<?php

/** @var yii\web\View $this */
/** @var array $movies */
/** @var string $q */
/** @var string $genre */

$this->title = 'Filmes em exibição';

echo $this->render(
	'/movie/index',
	compact('movies', 'q', 'genre'),
);
