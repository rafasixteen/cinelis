<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */

$this->title = 'Recuperar palavra-passe';
?>

<section class="panel narrow">
	<h1>Recuperar palavra-passe</h1>
	<p class="muted">Indique o email associado à sua conta.</p>
	<form data-demo-form novalidate method="post" action="<?= Url::to(
		['/account/recover'],
	) ?>
">
		<div class="ui-field">
			<label for="recover-email">Email</label>
			<input class="ui-input" id="recover-email" type="email" autocomplete="email" placeholder="ana@exemplo.pt">
		</div>
		<button class="ui-button ui-button--primary" type="submit" data-demo-action>Enviar ligação de recuperação</button>
	</form>
	<?= Html::a(
		'Voltar a iniciar sessão',
		['/account/login'],
		[
			'class' => 'text-link',
		],
	) ?>
</section>
