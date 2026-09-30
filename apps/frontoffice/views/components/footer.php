<?php

use yii\helpers\Html;
?>

<footer class="site-footer">
	<div class="container">
		<div class="main">
			<div class="brand">
				<?= Html::a(
					'Cinelis',
					['/'],
					['class' => 'logo'],
				) ?>

				<p class="description">
					Your local cinema for great films and memorable experiences.
				</p>
			</div>

			<nav class="nav">
				<?= Html::a(
					'Movies',
					['/movie/index'],
					['class' => 'link'],
				) ?>

				<?= Html::a(
					'Sessions',
					['/session/index'],
					['class' => 'link'],
				) ?>

				<?= Html::a(
					'Contact',
					['/site/contact'],
					['class' => 'link'],
				) ?>
			</nav>
		</div>

		<div class="bottom">
			<p class="copyright">
				© <?= date('Y') ?> Cinelis
			</p>
		</div>
	</div>
</footer>