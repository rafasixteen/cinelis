<?php

use yii\helpers\Html;

?>

<footer class="site-footer container">
	<p>CineLis · O seu lugar no cinema.</p>
	<nav aria-label="Informações">
		<?= Html::a(
			'Contacto',
			['/site/contact'],
		) ?>

		<?= Html::a(
			'Ajuda',
			['/site/help'],
		) ?>

		<?= Html::a(
			'Privacidade',
			['/site/privacy'],
		) ?>
		<span lang="pt">PT</span>
	</nav>
</footer>
