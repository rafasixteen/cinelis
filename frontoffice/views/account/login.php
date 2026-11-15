<?php

use common\widgets\Button;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var bool $register */
/** @var common\models\LoginForm $loginModel */
/** @var common\models\User $registerModel */

$this->title = $register ? 'Criar conta' : 'Iniciar sessão';
?>

<div class="auth-grid <?= $register ? 'auth-register' : '' ?>">
	<section class="panel login-panel">
		<?= Html::tag($register ? 'h2' : 'h1', 'Iniciar sessão') ?>
		<p class="muted">Os seus bilhetes, sempre consigo.</p>
		<?php $loginForm = ActiveForm::begin([
			'action' => Url::to(['/account/login']),
			'options' => ['novalidate' => true],
			'fieldConfig' => [
				'options' => ['class' => 'ui-field'],
				'inputOptions' => ['class' => 'ui-input'],
			],
		]) ?>
			<?= $loginForm->field($loginModel, 'email')->input('email', [
				'autocomplete' => 'email',
				'placeholder' => 'ana@exemplo.pt',
			]) ?>
			<?= $loginForm->field($loginModel, 'password')->passwordInput([
				'autocomplete' => 'current-password',
				'placeholder' => '••••••••',
			]) ?>
			<?= Html::a('Recuperar palavra-passe', ['/account/recover'], ['class' => 'recover-link']) ?>
			<?= Html::submitButton('Entrar', ['class' => 'ui-button ui-button--primary']) ?>
		<?php ActiveForm::end() ?>
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
		<?php $registerForm = ActiveForm::begin([
			'action' => Url::to(['/account/register']),
			'options' => ['novalidate' => true],
			'fieldConfig' => [
				'options' => ['class' => 'ui-field'],
				'inputOptions' => ['class' => 'ui-input'],
			],
		]) ?>
			<?= $registerForm->field($registerModel, 'username')->textInput([
				'autocomplete' => 'name',
				'placeholder' => 'Ana Silva',
			]) ?>
			<?= $registerForm->field($registerModel, 'email')->input('email', [
				'autocomplete' => 'email',
				'placeholder' => 'ana@exemplo.pt',
			]) ?>
			<?= $registerForm->field($registerModel, 'password')->passwordInput([
				'autocomplete' => 'new-password',
				'placeholder' => 'Pelo menos 8 caracteres',
			]) ?>
			<?= Html::submitButton('Criar conta', ['class' => 'ui-button ui-button--primary']) ?>
		<?php ActiveForm::end() ?>
		<div class="auth-switch">
			<?= Html::a('Já tenho conta. Iniciar sessão', ['/account/login']) ?>
		</div>
	</section>
</div>
