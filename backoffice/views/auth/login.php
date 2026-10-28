<?php

use common\widgets\Badge;
use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */

$this->title = 'Login interno · CineLis Backoffice';
$this->params['section'] = 'Login interno';
?>

<main id="main-content" class="page login-page">
	<section class="panel login">
		<?= Badge::widget(
			[
				'label' => 'ACESSO INTERNO',
			],
		) ?>
		<h1>Bem-vindo ao backoffice</h1>
		<p>Entre com a sua conta de colaborador.</p>
		<form method="post" data-demo-form novalidate>
			<?= $this->render('../components/field', [
				'name' => 'email',
				'label' => 'Email',
				'type' => 'email',
				'options' => [
					'autocomplete' => 'username',
					'placeholder' => 'ana@cinelis.pt',
				],
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'password',
				'label' => 'Palavra-passe',
				'type' => 'password',
				'options' => [
					'autocomplete' => 'current-password',
					'placeholder' => '••••••••',
				],
			]) ?>

			<?= Button::widget([
				'label' => 'Entrar',
				'options' => ['type' => 'submit'],
			]) ?>
		</form>
		<p class="demo-note">Acesso demonstrativo, sem autenticação.</p>
		<?= Html::a(
			'Explorar dashboard →',
			['/home/index'],
			[
				'class' => 'text-link',
			],
		) ?>
	</section>
</main>
