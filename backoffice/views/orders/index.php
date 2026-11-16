<?php

use common\models\Order;
use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $provider */
/** @var string $q */
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => 'Encomendas',
		'description' => 'Consulte as vendas e os bilhetes associados.',
	]) ?>
	<?= Html::beginForm(['/orders/index'], 'get', ['class' => 'filters']) ?>
	<?= $this->render('../components/field', [
		'name' => 'q',
		'label' => 'Pesquisar encomenda',
		'value' => $q,
		'options' => ['placeholder' => 'UUID, cliente ou filme…'],
	]) ?>
	<?= Button::widget([
		'label' => 'Filtrar',
		'variant' => 'secondary',
		'options' => ['type' => 'submit'],
	]) ?>
	<?= Html::a('Limpar', ['/orders/index'], ['class' => 'text-link']) ?>
	<?= Html::endForm() ?>

	<?= $this->render('../components/table', [
		'provider' => $provider,
		'caption' => 'Encomendas de bilhetes',
		'columns' => [
			[
				'label' => 'Encomenda',
				'raw' => true,
				'value' => static fn (Order $record): string => Html::a(
					'#'.substr((string) $record->id, 0, 8),
					['/orders/view', 'id' => $record->id],
					['class' => 'text-link', 'title' => (string) $record->id],
				),
			],
			[
				'label' => 'Cliente',
				'value' => static fn (Order $record): string => $record->user?->username ?? '—',
			],
			[
				'label' => 'Filme',
				'value' => static fn (Order $record): string => $record->tickets[0]?->session?->movie?->title ?? '—',
			],
			[
				'label' => 'Bilhetes',
				'value' => static fn (Order $record): string => (string) count($record->tickets),
			],
			[
				'label' => 'Total',
				'value' => static fn (Order $record): string => number_format((float) $record->total, 2, ',', '').' €',
			],
			[
				'label' => 'Criada em',
				'value' => static fn (Order $record): string => date('d/m/Y H:i', strtotime((string) $record->created_at)),
			],
		],
	]) ?>
</main>
