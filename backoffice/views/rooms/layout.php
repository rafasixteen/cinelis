<?php

use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $room */
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => $room['name'].' · Editor de layout',
		'section' => 'Salas',
		'description' => $room['capacity'].' lugares · '.$room['rows'].' filas · '.$room['columns'].' lugares por fila',
	]) ?>
	<div class="layout-actions">
		<?= Button::widget([
			'label' => 'Guardar layout',
			'options' => ['data-demo-action' => true],
		]) ?>
	</div>
	<div class="layout-editor">
		<aside class="panel tools">
			<h2>Ferramentas</h2>
			<?= Button::widget([
				'label' => 'Selecionar',
				'options' => [
					'aria-pressed' => 'true',
					'data-seat-select-tool' => true,
				],
			]) ?>

			<?php foreach (['Lugar', 'Ecrã', 'Entrada', 'Corredor', 'Apagar'] as $tool): ?>

				<?= Button::widget([
					'label' => $tool,
					'variant' => 'secondary',
					'options' => ['data-demo-action' => true],
				]) ?>

			<?php endforeach ?>
		</aside>
		<section class="panel seat-panel" aria-label="Disposição de lugares da <?= Html::encode($room['name']) ?>
">
			<div class="screen">ECRÃ</div>
			<div class="seat-scroll" tabindex="0" aria-label="Mapa de lugares, deslize para ver todos os lugares">
				<div class="seat-grid">
					<?php for ($row = 0; $row < $room['rows']; $row++): ?>
						<div class="seat-row">
							<span class="row-label"><?= chr(65 + $row) ?>
							</span>

							<?php for ($column = 1; $column <= $room['columns']; $column++): ?>

								<?php
								$seat = chr(65 + $row).$column;
								?>
								<button
									type="button"
									class="seat"
									data-seat="<?= $seat ?>
"
									aria-label="Lugar <?= $seat ?>
"
									aria-pressed="false"
								>
									<?= $column ?>
								</button>

							<?php endfor ?>
						</div>

					<?php endfor ?>
				</div>
			</div>
			<div class="seat-footer">
				<span>Entrada →</span>
				<output data-seat-selection aria-live="polite">Nenhum lugar selecionado</output>
			</div>
		</section>
	</div>
	<p class="demo-note">Selecione um lugar para o inspecionar. As alterações ao layout são apenas demonstrativas.</p>
	<?= Html::a(
		'← Voltar às salas',
		['/rooms/index'],
		[
			'class' => 'text-link',
		],
	) ?>
</main>
