<?php

use common\models\Room;
use common\models\Session;
use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $provider */
/** @var string $date */
/** @var string $room */
/** @var list<Room> $rooms */

$formatDateTime = static function (Session $session): string {
	$date = new \DateTimeImmutable((string) $session->starts_at, new \DateTimeZone('Europe/Lisbon'));

	return $date->format('d/m/Y').' · '.$date->format('H:i');
};
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

	<?= Html::beginForm(['/sessions/index'], 'get', ['class' => 'filters']) ?>
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
		'items' => ['' => 'Todas as salas'] + array_reduce(
			$rooms,
			static function (array $items, Room $model): array {
				$items[(string) $model->id] = $model->name;

				return $items;
			},
			[],
		),
	]) ?>
	<?= Button::widget([
		'label' => 'Filtrar',
		'variant' => 'secondary',
		'options' => ['type' => 'submit'],
	]) ?>
	<?= Html::a('Limpar', ['/sessions/index'], ['class' => 'text-link']) ?>
	<?= Html::endForm() ?>

	<?= $this->render('../components/table', [
		'provider' => $provider,
		'caption' => 'Programação de sessões',
		'columns' => [
			[
				'label' => 'Data / hora',
				'value' => $formatDateTime,
			],
			[
				'label' => 'Filme',
				'value' => static fn (Session $record): string => $record->movie?->title ?? '—',
			],
			[
				'label' => 'Sala',
				'value' => static fn (Session $record): string => $record->room?->name ?? '—',
			],
			[
				'label' => 'Capacidade',
				'value' => static fn (Session $record): string => (string) ($record->room?->getSeats()->count() ?? 0),
			],
			[
				'label' => 'Preço',
				'value' => static fn (Session $record): string => number_format((float) $record->price, 2, ',', '').' €',
			],
			[
				'label' => 'Estado',
				'value' => static fn (Session $record): string => $record->displayStatus(),
			],
			[
				'label' => 'Ações',
				'raw' => true,
				'headerOptions' => ['class' => 'table-actions-header'],
				'contentOptions' => ['class' => 'table-actions-cell'],
				'value' => static function (Session $record): string {
					$actions = '<div class="row-actions">'.Button::widget([
						'label' => 'Editar',
						'tag' => 'a',
						'url' => ['/sessions/update', 'id' => $record->id],
						'variant' => 'secondary',
						'options' => ['aria-label' => 'Editar sessão de '.($record->movie?->title ?? '')],
					]).Button::widget([
						'label' => 'Bilhetes',
						'tag' => 'a',
						'url' => ['/tickets/index', 'session' => $record->id],
						'variant' => 'secondary',
						'options' => ['aria-label' => 'Bilhetes de '.($record->movie?->title ?? '')],
					]);

					if ($record->isStatusScheduled()) {
						$actions .= Html::beginForm(['/sessions/cancel', 'id' => $record->id], 'post', [
							'class' => 'row-action-form',
						])
							.Button::widget([
								'label' => 'Cancelar',
								'variant' => 'danger',
								'options' => ['type' => 'submit'],
							])
							.Html::endForm();
					}

					return $actions.'</div>';
				},
			],
		],
	]) ?>
</main>
