<?php

use yii\helpers\Html;
?>

<header class="site-header">
	<div class="container">
		<?= Html::a(
			'Cinelis',
			['/'],
			['class' => 'brand'],
		) ?>

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
		</nav>

		<div class="actions">
			<?= Html::a(
				'Sign in',
				['/account/login'],
				['class' => 'login'],
			) ?>
		</div>
	</div>
</header>