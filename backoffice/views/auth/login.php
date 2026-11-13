<?php

use common\widgets\Badge;
use common\widgets\Button;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\LoginForm $model */

$this->title = 'Login interno · CineLis Backoffice';
$this->params['section'] = 'Login interno';
?>

<main id="main-content" class="page login-page">
	<section class="panel login">
		<?= Badge::widget(['label' => 'ACESSO INTERNO']) ?>
		<h1>Bem-vindo ao backoffice</h1>
		<p>Entre com a sua conta de colaborador.</p>
		<?php $form = ActiveForm::begin([
			'options' => ['novalidate' => true],
			'fieldConfig' => [
				'options' => ['class' => 'field'],
				'inputOptions' => ['class' => 'input'],
			],
		]) ?>
			<?= $form->field($model, 'email')->input('email', [
				'autocomplete' => 'username',
				'placeholder' => 'ana@cinelis.pt',
			]) ?>
			<?= $form->field($model, 'password')->passwordInput([
				'autocomplete' => 'current-password',
				'placeholder' => '••••••••',
			]) ?>
			<?= Button::widget([
				'label' => 'Entrar',
				'options' => ['type' => 'submit'],
			]) ?>
		<?php ActiveForm::end() ?>
		<p class="demo-note">Acesso reservado a funcionários e administradores.</p>
	</section>
</main>
