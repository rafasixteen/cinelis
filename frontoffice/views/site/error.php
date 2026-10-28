<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */

$this->title = 'Página não encontrada';
?>

<section class="empty">
	<h1><?= Html::encode($name) ?>
	</h1>
	<p><?= Html::encode($message) ?>
	</p>
	<?= Html::a(
		'Voltar aos filmes',
		['/movie/index'],
		[
			'class' => 'ui-button ui-button--primary',
		],
	) ?>
</section>
