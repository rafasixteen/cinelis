<?php

use common\rbac\RbacInstaller;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\User $model */
/** @var string $role */
/** @var bool $isNew */

$this->title = $isNew ? 'Adicionar utilizador' : 'Editar utilizador';
$this->params['section'] = 'Utilizadores';
$displayName = trim((string) $model->username) !== '' ? $model->username : 'Novo utilizador';
$initials = mb_strtoupper(mb_substr($displayName, 0, 2));
$roleLabel = RbacInstaller::getRoleLabel($role);
?>

<main id="main-content" class="page user-editor-page">
	<?= $this->render('../components/page-heading', [
		'title' => $isNew ? 'Adicionar utilizador' : 'Editar utilizador',
		'section' => 'Utilizadores',
		'description' => $isNew ? 'Nova conta interna' : $model->email,
	]) ?>

	<?php $form = ActiveForm::begin([
		'options' => ['novalidate' => true],
		'fieldConfig' => [
			'options' => ['class' => 'field'],
			'inputOptions' => ['class' => 'input'],
		],
	]) ?>
		<section class="panel user-editor-card">
			<header class="user-editor-card__header">
				<div class="user-editor-card__identity">
					<div class="user-editor-card__avatar" aria-hidden="true"><?= Html::encode($initials) ?></div>
					<div>
						<h2><?= Html::encode($displayName) ?></h2>
						<p><?= Html::encode($model->email ?: 'Email por definir') ?></p>
					</div>
				</div>
				<span class="user-editor-card__role"><?= Html::encode($roleLabel) ?></span>
			</header>

			<div class="form-grid">
				<?= $form->field($model, 'username')->textInput([
					'autocomplete' => 'username',
					'placeholder' => 'Nome de utilizador',
				]) ?>
				<?= $form->field($model, 'email')->input('email', [
					'autocomplete' => 'email',
					'placeholder' => 'utilizador@cinelis.pt',
				]) ?>
				<div class="field field--full">
					<?= Html::label('Roles', 'role', ['class' => 'control-label']) ?>
					<?= Html::dropDownList('role', $role, [
						'customer' => 'Cliente',
						'employee' => 'Funcionário',
						'admin' => 'Administrador',
					], ['id' => 'role', 'class' => 'input']) ?>
				</div>
				<?php if ($isNew): ?>
					<?= $form->field($model, 'password', ['options' => ['class' => 'field field--full']])->passwordInput([
						'autocomplete' => 'new-password',
						'placeholder' => 'Pelo menos 8 caracteres',
					])->hint('Use pelo menos 8 caracteres.') ?>
				<?php endif ?>
			</div>

			<div class="form-actions user-editor-card__actions">
				<?= Html::a('Cancelar', ['/users/index'], ['class' => 'ui-button ui-button--secondary']) ?>
				<?= Html::submitButton(
					$isNew ? 'Criar utilizador' : 'Guardar alterações',
					['class' => 'ui-button ui-button--primary'],
				) ?>
			</div>
		</section>
	<?php ActiveForm::end() ?>
</main>
