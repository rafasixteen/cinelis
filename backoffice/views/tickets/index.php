<?php

use common\models\Session;
use common\models\Ticket;
use common\widgets\Badge;
use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Session|null $session */
/** @var list<Session> $sessions */
/** @var yii\data\ActiveDataProvider $provider */
/** @var string $q */
/** @var string $used */

$sold = $session ? (int) $session->getTickets()->count() : 0;
$capacity = $session?->room ? (int) $session->room->getSeats()->count() : 0;
$occupancy = $capacity > 0 ? (int) round($sold / $capacity * 100) : 0;
$sessionLabel = $session?->movie?->title ?? 'Todas as sessões';
$revenue = $session ? (float) ($session->getTickets()->sum('price') ?? 0) : 0;
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => 'Bilhetes vendidos',
		'section' => 'Bilhetes',
		'description' => $session
			? $sessionLabel.' · '.date('d/m/Y H:i', strtotime((string) $session->starts_at)).' · '.($session->room?->name ?? '')
			: 'Consulte os bilhetes emitidos.',
	]) ?>
	<?php if ($session): ?>
		<div class="ticket-metrics">
			<?= Badge::widget(['label' => $sold.' / '.$capacity.' lugares · '.$occupancy.'%']) ?>
			<?= Badge::widget(['label' => 'Receita: '.number_format($revenue, 2, ',', '').' €']) ?>
		</div>
	<?php endif ?>
	<?= Html::beginForm(['/tickets/index'], 'get', ['class' => 'filters']) ?>
	<?php if ($session): ?>
		<?= $this->render('../components/field', [
			'name' => 'session',
			'label' => 'Sessão',
			'value' => (string) $session->id,
			'items' => array_reduce(
				$sessions,
				static function (array $items, Session $model): array {
					$items[(string) $model->id] = date('d/m/Y H:i', strtotime((string) $model->starts_at))
						.' · '.($model->movie?->title ?? 'Sessão');

					return $items;
				},
				[],
			),
		]) ?>
	<?php else: ?>
		<?= $this->render('../components/field', [
			'name' => 'session',
			'label' => 'Sessão',
			'value' => '',
			'items' => ['' => 'Todas as sessões'] + array_reduce(
				$sessions,
				static function (array $items, Session $model): array {
					$items[(string) $model->id] = date('d/m/Y H:i', strtotime((string) $model->starts_at))
						.' · '.($model->movie?->title ?? 'Sessão');

					return $items;
				},
				[],
			),
		]) ?>
	<?php endif ?>
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
		'items' => ['' => 'Todos', '1' => 'Sim', '0' => 'Não'],
	]) ?>
	<?= Button::widget([
		'label' => 'Filtrar',
		'variant' => 'secondary',
		'options' => ['type' => 'submit'],
	]) ?>
	<?= Html::a('Limpar', ['/tickets/index', 'session' => $session?->id], ['class' => 'text-link']) ?>
	<?= Html::endForm() ?>

	<?= $this->render('../components/table', [
		'provider' => $provider,
		'caption' => 'Bilhetes vendidos por sessão',
		'columns' => [
			[
				'label' => 'Lugar',
				'value' => static fn (Ticket $record): string => $record->seat
					? $record->seat->row.(string) $record->seat->number
					: '—',
			],
			[
				'label' => 'Cliente',
				'value' => static fn (Ticket $record): string => $record->order?->user?->username ?? '—',
			],
			['label' => 'Código', 'value' => static fn (Ticket $record): string => $record->code],
			[
				'label' => 'Preço',
				'value' => static fn (Ticket $record): string => number_format((float) $record->price, 2, ',', '').' €',
			],
			[
				'label' => 'Utilizado',
				'raw' => true,
				'value' => static fn (Ticket $record): string => Badge::widget([
					'label' => $record->used_at ? 'Sim' : 'Não',
					'variant' => $record->used_at ? 'success' : 'default',
				]),
			],
		],
	]) ?>
</main>
