<?php

use backoffice\data\Operations;
use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ArrayDataProvider $provider */
/** @var string $date */
/** @var string $room */
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => 'Sessões',
		'description' => 'Organize a programação e consulte a disponibilidade.',
		'action' => [
			'label' => 'Nova sessão',
			'url' => ['/sessions/create'],
		],
	]) ?>

	<?= Html::beginForm(
		['/sessions/index'],
		'get',
		[
			'class' => 'filters',
		],
	) ?>

	<?= $this->render('../components/field', [
		'name' => 'date',
		'label' => 'Data',
		'type' => 'date',
		'value' => $date,
	]) ?>

	<?= $this->render('../components/field', [
		'name' => 'room',
		'label' => 'Sala',
		'value' => $room,
		'items' => ['' => 'Todas as salas'] + array_column(Operations::rooms(), 'name', 'id'),
	]) ?>

	<?= Button::widget([
		'label' => 'Filtrar',
		'variant' => 'secondary',
		'options' => ['type' => 'submit'],
	]) ?>

	<?= Html::a(
		'Limpar',
		[
			'/sessions/index',
			'date' => '',
		],
		[
			'class' => 'text-link',
		],
	) ?>

	<?= Html::endForm() ?>

	<?= $this->render('../components/table', [
		'provider' => $provider,
		'caption' => 'Programação de sessões',
		'columns' => [
			[
				'label' => 'Data / hora',
				'value' => static fn (array $record): string => date('d/m', strtotime($record['date'])).' · '.$record['time'],
			],
			['label' => 'Filme', 'value' => 'movie'],
			['label' => 'Sala', 'value' => 'room'],
			[
				'label' => 'Preço',
				'value' => static fn (array $record): string => Operations::money($record['price']),
			],
			[
				'label' => 'Ações',
				'raw' => true,
				'value' => static fn (array $record): string => '<div class="row-actions">'
					.Html::a(
						'Editar',
						[
							'/sessions/edit',
							'id' => $record['id'],
						],
						['aria-label' => 'Editar sessão de '.$record['movie']],
					)
					.Html::a(
						'Bilhetes',
						[
							'/tickets/index',
							'session' => $record['id'],
						],
						['aria-label' => 'Bilhetes de '.$record['movie']],
					)
					.'</div>',
			],
		],
	]) ?>
</main>
