<?php

use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $movie */
?>

<article class="movie-card">
	<?= Html::a(
		$this->render(
			'poster',
			[
				'movie' => $movie,
			],
		),
		[
			'/movie/view',
			'id' => $movie['id'],
		],
		[
			'tabindex' => '-1',
			'aria-hidden' => 'true',
		],
	) ?>
	<h3>
		<?= Html::a(
			Html::encode($movie['title']),
			[
				'/movie/view',
				'id' => $movie['id'],
			],
		) ?>
	</h3>
	<p class="muted"><?= $movie['rating'] ?>
· <?= $movie['duration'] ?>
min</p>
	<?= Button::widget([
		'tag' => 'a',
		'label' => 'Ver sessões',
		'url' => [
			'/movie/view',
			'id' => $movie['id'],
		],
		'variant' => 'secondary',
		'options' => [
			'aria-label' => 'Ver sessões de '.$movie['title'],
		],
	]) ?>
</article>
