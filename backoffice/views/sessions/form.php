<?php

use common\models\Movie;
use common\models\Room;
use common\models\Session;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var Session $model */
/** @var list<Movie> $movies */
/** @var list<Room> $rooms */
/** @var bool $isNew */

$model->starts_at = $model->starts_at
	? date('Y-m-d\TH:i', strtotime((string) $model->starts_at))
	: '';
$movieItems = ArrayHelper::map($movies, 'id', 'title');
$roomItems = [];
foreach ($rooms as $room) {
	$roomItems[(string) $room->id] = $room->name.' · '.$room->getSeats()->count().' lugares';
}
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => $isNew ? 'Criar sessão' : 'Editar sessão',
		'section' => 'Sessões',
		'description' => $isNew ? 'Defina o filme, a sala e o horário.' : ($model->movie?->title ?? 'Sessão'),
	]) ?>

	<?php $form = ActiveForm::begin([
		'options' => ['class' => 'edit-form', 'novalidate' => true],
		'fieldConfig' => [
			'options' => ['class' => 'field'],
			'inputOptions' => ['class' => 'input'],
		],
	]) ?>
	<div class="panel form-grid">
		<?= $form->field($model, 'movie_id')->dropDownList($movieItems, ['prompt' => 'Selecionar filme']) ?>
		<?= $form->field($model, 'room_id')->dropDownList($roomItems, ['prompt' => 'Selecionar sala']) ?>
		<?= $form->field($model, 'starts_at')->input('datetime-local', [
			'data-localized-date' => 'true',
			'lang' => 'pt-PT',
		]) ?>
		<?= $form->field($model, 'price')->input('number', [
			'min' => 0,
			'step' => '0.50',
		]) ?>
		<?php if (!$isNew): ?>
			<div class="notice field--full">
				<h2>Estado atual</h2>
				<p><?= Html::encode($model->displayStatus()) ?>. Sessões com bilhetes ou reservas ativas não podem mudar de filme, sala ou horário.</p>
			</div>
		<?php endif ?>
	</div>
	<div class="form-actions">
		<?= Html::a('Cancelar', ['/sessions/index'], ['class' => 'ui-button ui-button--secondary']) ?>
		<?= Html::submitButton($isNew ? 'Criar sessão' : 'Guardar alterações', ['class' => 'ui-button ui-button--primary']) ?>
	</div>
	<?php ActiveForm::end() ?>
</main>
