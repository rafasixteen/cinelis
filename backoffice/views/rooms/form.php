<?php

use common\models\Room;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var Room $model */
/** @var array<string, mixed> $layout */

$this->title = 'Adicionar sala · CineLis Backoffice';
$this->params['section'] = 'Salas';
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => 'Adicionar sala',
		'section' => 'Salas',
		'description' => 'A sala será criada com um layout inicial editável.',
	]) ?>

	<?php $form = ActiveForm::begin([
		'options' => ['class' => 'edit-form', 'novalidate' => true],
		'fieldConfig' => [
			'options' => ['class' => 'field'],
			'inputOptions' => ['class' => 'input'],
		],
	]) ?>
	<div class="panel form-grid">
		<?= $form->field($model, 'name')->textInput([
			'autocomplete' => 'off',
			'placeholder' => 'Sala 4',
		]) ?>
		<p class="notice field--full">O layout inicial cria <?= count($layout['cells']) ?> lugares. Depois de guardar, poderá editar o ecrã, entradas, saídas e células individuais.</p>
		<?= Html::hiddenInput('layout', Json::encode($layout)) ?>
	</div>
	<div class="form-actions">
		<?= Html::a('Cancelar', ['/rooms/index'], ['class' => 'ui-button ui-button--secondary']) ?>
		<?= Html::submitButton('Criar sala', ['class' => 'ui-button ui-button--primary']) ?>
	</div>
	<?php ActiveForm::end() ?>
</main>
