<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\User $model */
/** @var string $role */
/** @var bool $isNew */

$this->title = $isNew ? 'Adicionar utilizador' : 'Editar utilizador';
$this->params['section'] = 'Utilizadores';
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => $isNew ? 'Adicionar utilizador' : 'Editar utilizador',
		'section' => 'Utilizadores',
		'description' => $isNew ? 'Defina os dados e o perfil do utilizador.' : $model->email,
	]) ?>

	<?php $form = ActiveForm::begin([
		'options' => ['novalidate' => true],
		'fieldConfig' => [
			'options' => ['class' => 'field'],
			'inputOptions' => ['class' => 'input'],
		],
	]) ?>
		<div class="panel form-grid">
			<?= $form->field($model, 'username')->textInput([
				'autocomplete' => 'username',
				'placeholder' => 'Nome de utilizador',
			]) ?>
			<?= $form->field($model, 'email')->input('email', [
				'autocomplete' => 'email',
				'placeholder' => 'utilizador@cinelis.pt',
			]) ?>
			<?= Html::label('Perfil', 'role', ['class' => 'control-label']) ?>
			<?= Html::dropDownList('role', $role, [
				'customer' => 'Cliente',
				'employee' => 'Funcionário',
				'admin' => 'Administrador',
			], ['id' => 'role', 'class' => 'form-control']) ?>
			<?= $form->field($model, 'password')->passwordInput([
				'autocomplete' => 'new-password',
				'placeholder' => $isNew ? 'Pelo menos 8 caracteres' : 'Manter palavra-passe atual',
			])->hint($isNew ? 'Use pelo menos 8 caracteres.' : 'Deixe vazio para manter a palavra-passe atual.') ?>
		</div>
		<div class="form-actions">
			<?= Html::a('Cancelar', ['/users/index'], ['class' => 'ui-button ui-button--secondary']) ?>
			<?= Html::submitButton(
				$isNew ? 'Criar utilizador' : 'Guardar alterações',
				['class' => 'ui-button ui-button--primary'],
			) ?>
		</div>
	<?php ActiveForm::end() ?>
</main>
