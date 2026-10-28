<?php

use common\widgets\Button;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var bool $register */

$this->title = $register ? 'Criar conta' : 'Iniciar sessão';
?>

<div class="auth-grid <?= $register ? 'auth-register' : '' ?>">
	<section class="panel login-panel">
		<?= Html::tag($register ? 'h2' : 'h1', 'Iniciar sessão') ?>
		<p class="muted">Os seus bilhetes, sempre consigo.</p>
		<form data-demo-form novalidate method="post" action="<?= Url::to(['/account/login']) ?>">
			<div class="ui-field">
				<label for="login-email">Email</label>
				<input class="ui-input" id="login-email" type="email" autocomplete="email" placeholder="ana@exemplo.pt" required>
			</div>
			<div class="ui-field">
				<label for="login-password">Palavra-passe</label>
				<input class="ui-input" id="login-password" type="password" autocomplete="current-password" placeholder="••••••••" required>
			</div>
			<?= Html::a(
				'Recuperar palavra-passe',
				['/account/recover'],
				[
					'class' => 'recover-link',
				],
			) ?>
			<button class="ui-button ui-button--primary" type="submit" data-demo-action>Entrar</button>
		</form>
		<div class="auth-switch">
			<p>Ainda não tem conta?</p>
			<?= Button::widget([
				'tag' => 'a',
				'label' => 'Criar conta',
				'url' => ['/account/register'],
				'variant' => 'secondary',
			]) ?>
		</div>
	</section>
	<section class="panel register-panel">
		<?= Html::tag($register ? 'h1' : 'h2', 'Criar conta') ?>
		<p class="muted">Faça parte da próxima sessão.</p>
		<form data-demo-form novalidate method="post" action="<?= Url::to(['/account/register']) ?>">
			<div class="ui-field">
				<label for="register-name">Nome</label>
				<input class="ui-input" id="register-name" autocomplete="name" placeholder="Ana Silva" required>
			</div>
			<div class="ui-field">
				<label for="register-email">Email</label>
				<input class="ui-input" id="register-email" type="email" autocomplete="email" placeholder="ana@exemplo.pt" required>
			</div>
			<div class="ui-field">
				<label for="register-password">Palavra-passe</label>
				<input class="ui-input" id="register-password" type="password" autocomplete="new-password" placeholder="Pelo menos 8 caracteres" minlength="8" required>
			</div>
			<button class="ui-button ui-button--primary" type="submit" data-demo-action>Criar conta</button>
		</form>
		<div class="auth-switch">
			<?= Html::a(
				'Já tenho conta. Iniciar sessão',
				['/account/login'],
			) ?>
		</div>
	</section>
</div>