<?php

use common\models\Room;
use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Room $room */
/** @var array<string, mixed> $layout */

$this->title = $room->name.' · Editor de layout · CineLis Backoffice';
$this->params['section'] = 'Salas';
$rows = (int) $layout['grid']['rows'];
$columns = (int) $layout['grid']['columns'];
$cells = [];
foreach ($layout['cells'] as $cell) {
	$cells[$cell['row'].':'.$cell['column']] = $cell['type'];
}
$entranceLabel = $layout['entrances'][0]['label'] ?? 'Entrada';
$exitLabel = $layout['exits'][0]['label'] ?? 'Saída';
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => $room->name.' · Editor de layout',
		'section' => 'Salas',
		'description' => count($room->seats).' lugares persistidos na base de dados.',
	]) ?>

	<?php if ($room->hasErrors('layout')): ?>
		<div class="notice notice--error" role="alert">
			<?= Html::encode(implode(' ', $room->getErrors('layout'))) ?>
		</div>
	<?php endif ?>

	<?= Html::beginForm(['/rooms/layout', 'id' => $room->id], 'post', ['class' => 'edit-form']) ?>
	<div class="panel form-grid">
		<?= Html::label('Ecrã', 'screen-label', ['class' => 'control-label']) ?>
		<?= Html::textInput('screen_label', $layout['screen']['label'] ?? 'Ecrã', ['id' => 'screen-label', 'class' => 'input']) ?>
		<?= Html::label('Entrada', 'entrance-label', ['class' => 'control-label']) ?>
		<?= Html::textInput('entrance_label', $entranceLabel, ['id' => 'entrance-label', 'class' => 'input']) ?>
		<?= Html::label('Saída', 'exit-label', ['class' => 'control-label']) ?>
		<?= Html::textInput('exit_label', $exitLabel, ['id' => 'exit-label', 'class' => 'input']) ?>
		<?= Html::label('Filas', 'grid-rows', ['class' => 'control-label']) ?>
		<?= Html::input('number', 'grid_rows', $rows, ['id' => 'grid-rows', 'class' => 'input', 'min' => 1, 'max' => 26]) ?>
		<?= Html::label('Lugares por fila', 'grid-columns', ['class' => 'control-label']) ?>
		<?= Html::input('number', 'grid_columns', $columns, ['id' => 'grid-columns', 'class' => 'input', 'min' => 1, 'max' => 40]) ?>
	</div>

	<section class="panel layout-editor" aria-labelledby="layout-grid-heading">
		<div class="panel-heading">
			<h2 id="layout-grid-heading">Mapa da sala</h2>
			<p>Escolha se cada célula é lugar, corredor ou espaço vazio. Os lugares serão sincronizados com a tabela <code>seats</code>.</p>
		</div>
		<div class="seat-grid" role="grid" aria-label="Mapa editável da sala">
			<?php for ($row = 1; $row <= $rows; $row++): ?>
				<div class="seat-row" role="row">
					<span class="row-label" aria-hidden="true"><?= chr(64 + $row) ?></span>
					<?php for ($column = 1; $column <= $columns; $column++): ?>
						<?php $coordinate = $row.':'.$column; ?>
						<label class="seat" title="Célula <?= $coordinate ?>">
							<span class="sr-only">Célula <?= $coordinate ?></span>
							<?= Html::dropDownList(
								"cell_types[{$coordinate}]",
								$cells[$coordinate] ?? 'empty',
								['empty' => '·', 'seat' => chr(64 + $row).$column, 'aisle' => '↕'],
								['aria-label' => "Tipo da célula {$coordinate}"],
							) ?>
						</label>
					<?php endfor ?>
				</div>
			<?php endfor ?>
		</div>
	</section>

	<div class="form-actions">
		<?= Html::a('Voltar às salas', ['/rooms/index'], ['class' => 'ui-button ui-button--secondary']) ?>
		<?= Button::widget([
			'label' => 'Guardar layout',
			'options' => ['type' => 'submit'],
		]) ?>
	</div>
	<?= Html::endForm() ?>
</main>
