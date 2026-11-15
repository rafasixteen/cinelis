<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\User $identity */

$this->title = 'A minha conta';
?>

<header class="page-heading">
	<h1>A minha conta</h1>
	<p>Uma conta de demonstração para explorar o CineLis.</p>
</header>
<section class="panel profile">
	<span class="avatar" aria-hidden="true"><?= Html::encode(mb_strtoupper(mb_substr($identity->username, 0, 2))) ?></span>
	<div>
		<h2><?= Html::encode($identity->username) ?></h2>
		<p class="muted"><?= Html::encode($identity->email) ?></p>
	</div>
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
	<?= Html::beginForm(['/account/logout'], 'post') ?>
		<?= Html::submitButton('Terminar sessão', ['class' => 'ui-button ui-button--secondary']) ?>
	<?= Html::endForm() ?>
</div>
