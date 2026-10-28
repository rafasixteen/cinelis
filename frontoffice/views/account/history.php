<?php

use yii\helpers\Html;

/** @var yii\web\View $this */

$this->title = 'Histórico de compras';
?>

<header class="page-heading">
	<h1>Histórico de compras</h1>
	<p>Consulte as suas compras e os detalhes de cada sessão.</p>
</header>
<?= $this->render('../components/history') ?>
<?= Html::a(
	'Voltar à minha conta',
	['/account/index'],
	[
		'class' => 'text-link',
	],
) ?>
