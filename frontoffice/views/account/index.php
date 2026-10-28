<?php

use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */

$this->title = 'A minha conta';
?>

<header class="page-heading">
	<h1>A minha conta</h1>
	<p>Uma conta de demonstração para explorar o CineLis.</p>
</header>
<section class="panel profile">
	<span class="avatar" aria-hidden="true">AS</span>
	<div>
		<h2>Ana Silva</h2>
		<p class="muted">ana@exemplo.pt</p>
	</div>
	<?= Button::widget([
		'tag' => 'a',
		'label' => 'Iniciar sessão',
		'url' => [
			'/account/login',
		],
		'variant' => 'secondary',
	]) ?>
</section>
<div class="section-heading">
	<h2>Histórico de compras</h2>
	<?= Html::a(
		'Ver histórico',
		['/account/history'],
		[
			'class' => 'text-link',
		],
	) ?>
</div>
<?= $this->render('../components/history') ?>
<div class="account-actions">
	<?= Button::widget([
		'label' => 'Terminar sessão',
		'variant' => 'secondary',
		'options' => [
			'data-demo-action' => true,
		],
	]) ?>
</div>
