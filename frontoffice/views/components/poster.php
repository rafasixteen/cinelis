<?php

use yii\helpers\Html;

/** @var array $movie */
?>

<div
	class="poster poster--<?= Html::encode($movie['art']) ?>
"
	role="img"
	aria-label="Cartaz ilustrado de <?= Html::encode($movie['title']) ?>
"
>
	<div class="poster-art" aria-hidden="true">
		<i></i>
		<b></b>
		<span></span>
	</div>
	<div class="poster-type">
		<span>CINELIS</span>
		<strong><?= Html::encode(mb_strtoupper($movie['title'])) ?>
		</strong>
	</div>
</div>
