<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;

/** @var yii\data\ArrayDataProvider $provider */
/** @var string $caption */
/** @var array $columns */

$models = $provider->getModels();
$total = $provider->getTotalCount();
$pagination = $provider->getPagination();
?>

<div class="table-wrap" role="region" aria-label="<?= Html::encode($caption) ?>
" tabindex="0">
	<table class="table">
		<caption class="sr-only"><?= Html::encode($caption) ?>
		</caption>
		<thead>
			<tr>
				<?php foreach ($columns as $column): ?>
					<th scope="col"><?= Html::encode($column['label']) ?>
					</th>

				<?php endforeach ?>
			</tr>
		</thead>
	<tbody>
		<?php foreach ($models as $record): ?>
				<tr>
				<?php foreach ($columns as $column): ?>

						<?php
					$value = is_callable($column['value'])
						? $column['value']($record)
						: $record[$column['value']];
					?>
						<td><?= !empty($column['raw']) ? $value : Html::encode($value) ?>
						</td>

					<?php endforeach ?>
				</tr>

			<?php endforeach ?>

			<?php if (!$models): ?>
				<tr>
				<td colspan="<?= count($columns) ?>
">
					<div class="empty">
						<strong>Sem resultados</strong>
						<p>Experimente alterar ou limpar os filtros.</p>
					</div>
				</td>
			</tr>

			<?php endif ?>
		</tbody>
	</table>
</div>
<div class="table-footer">
	<p>
		<?= $total ? 'A mostrar '.($pagination->getOffset() + 1).'–'.($pagination->getOffset() + count($models)).' de '.$total : '0 resultados' ?>
	</p>
	<nav aria-label="Paginação">
		<?= LinkPager::widget([
			'pagination' => $pagination,
			'prevPageLabel' => 'Anterior',
			'nextPageLabel' => 'Seguinte',
			'maxButtonCount' => 3,
			'options' => ['class' => 'pagination'],
		]) ?>
	</nav>
</div>
