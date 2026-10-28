<?php

use backoffice\data\Operations;
use common\widgets\Badge;
use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ArrayDataProvider $provider */
/** @var string $q */
/** @var string $status */
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => 'Encomendas',
		'description' => 'Consulte as vendas e os bilhetes associados.',
	]) ?>

	<?= Html::beginForm(
		['/orders/index'],
		'get',
		[
			'class' => 'filters',
		],
	) ?>

	<?= $this->render('../components/field', [
		'name' => 'q',
		'label' => 'Pesquisar encomenda',
		'value' => $q,
		'options' => ['placeholder' => 'Número, cliente ou filme…'],
	]) ?>

	<?= $this->render('../components/field', [
		'name' => 'status',
		'label' => 'Estado',
		'value' => $status,
		'items' => [
			'' => 'Todos',
			'Paga' => 'Paga',
			'Cancelada' => 'Cancelada',
		],
	]) ?>

	<?= Button::widget([
		'label' => 'Filtrar',
		'variant' => 'secondary',
		'options' => ['type' => 'submit'],
	]) ?>

	<?= Html::a(
		'Limpar',
		['/orders/index'],
		[
			'class' => 'text-link',
		],
	) ?>

	<?= Html::endForm() ?>

	<?= $this->render('../components/table', [
		'provider' => $provider,
		'caption' => 'Encomendas de bilhetes',
		'columns' => [
			[
				'label' => 'Encomenda',
				'raw' => true,
				'value' => static fn (array $record): string => Html::a(
					'#'.$record['id'],
					[
						'/orders/view',
						'id' => $record['id'],
					],
					['class' => 'text-link'],
				),
			],
			['label' => 'Cliente', 'value' => 'customer'],
			['label' => 'Filme', 'value' => 'movie'],
			['label' => 'Bilhetes', 'value' => 'quantity'],
			[
				'label' => 'Total',
				'value' => static fn (array $record): string => Operations::money($record['total']),
			],
			[
				'label' => 'Estado',
				'raw' => true,
				'value' => static fn (array $record): string => Badge::widget([
					'label' => $record['status'],
					'variant' => $record['status'] === 'Paga' ? 'success' : 'default',
				]),
			],
		],
	]) ?>
</main>
