<?php

use backoffice\data\Operations;
use common\widgets\Badge;
use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $session */
/** @var yii\data\ArrayDataProvider $provider */
/** @var string $q */
/** @var string $used */
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => 'Bilhetes vendidos',
		'section' => 'Bilhetes',
		'description' => $session['movie'].' · 27 out. 2026 · '.$session['time'].' · '.$session['room'],
	]) ?>
	<div class="ticket-metrics">
		<?= Badge::widget([
			'label' => $session['sold'].' / '.$session['capacity'].' lugares · '.$session['occupancy'].'%',
		]) ?>

		<?= Badge::widget([
			'label' => 'Receita: '.Operations::money($session['sold'] * $session['price']),
		]) ?>
	</div>
	<?= Html::beginForm(
		['/tickets/index'],
		'get',
		[
			'class' => 'filters',
		],
	) ?>

	<?= $this->render('../components/field', [
		'name' => 'session',
		'label' => 'Sessão',
		'value' => $session['id'],
		'items' => array_column(
			array_map(
				static fn (array $record): array => [
					'id' => $record['id'],
					'label' => $record['time'].' · '.$record['movie'],
				],
				Operations::sessions(),
			),
			'label',
			'id',
		),
	]) ?>

	<?= $this->render('../components/field', [
		'name' => 'q',
		'label' => 'Pesquisar bilhete',
		'value' => $q,
		'options' => ['placeholder' => 'Cliente, lugar ou código…'],
	]) ?>

	<?= $this->render('../components/field', [
		'name' => 'used',
		'label' => 'Utilizado',
		'value' => $used,
		'items' => [
			'' => 'Todos',
			'1' => 'Sim',
			'0' => 'Não',
		],
	]) ?>

	<?= Button::widget([
		'label' => 'Filtrar',
		'variant' => 'secondary',
		'options' => ['type' => 'submit'],
	]) ?>

	<?= Html::a(
		'Limpar',
		[
			'/tickets/index',
			'session' => $session['id'],
		],
		[
			'class' => 'text-link',
		],
	) ?>

	<?= Html::endForm() ?>

	<?= $this->render('../components/table', [
		'provider' => $provider,
		'caption' => 'Bilhetes vendidos por sessão',
		'columns' => [
			['label' => 'Lugar', 'value' => 'seat'],
			['label' => 'Cliente', 'value' => 'customer'],
			['label' => 'Código', 'value' => 'code'],
			[
				'label' => 'Preço',
				'value' => static fn (array $record): string => Operations::money($record['price']),
			],
			[
				'label' => 'Utilizado',
				'raw' => true,
				'value' => static fn (array $record): string => Badge::widget([
					'label' => $record['used'] ? 'Sim' : 'Não',
					'variant' => $record['used'] ? 'success' : 'default',
				]),
			],
		],
	]) ?>
</main>
