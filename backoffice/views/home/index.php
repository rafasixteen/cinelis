<?php

use common\models\Order;
use common\models\Session;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var list<Session> $sessions */
/** @var list<Order> $orders */
/** @var float $sales */
/** @var int $ticketCount */
/** @var int $occupancy */
/** @var string $todayDate */
/** @var string $occupancyDate */
/** @var int $occupancySessionCount */
/** @var int $occupancyCapacity */
/** @var int $occupancySold */

$money = static fn (float $amount): string => number_format($amount, 2, ',', '').' €';
$occupancyDateLabel = (new DateTimeImmutable($occupancyDate))->format('d/m/Y');
$occupancySummary = $occupancySold.' de '.$occupancyCapacity.' lugares ocupados';
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => 'Dashboard',
		'section' => 'Dashboard',
		'description' => 'Resumo de hoje e ocupação por data selecionada.',
		'action' => [
			'label' => 'Nova sessão',
			'url' => ['/sessions/create'],
		],
	]) ?>
	<section class="metrics" aria-label="Resumo do cinema">
		<article class="panel metric">
			<p>Vendas hoje</p>
			<strong><?= Html::encode($money($sales)) ?></strong>
			<small>Encomendas criadas hoje</small>
		</article>
		<article class="panel metric">
			<p>Bilhetes vendidos hoje</p>
			<strong><?= (int) $ticketCount ?></strong>
			<small>Bilhetes criados hoje</small>
		</article>
		<article class="panel metric">
			<p>Ocupação média hoje</p>
			<strong><?= (int) $occupancy ?>%</strong>
			<small>Baseada nas sessões de hoje</small>
		</article>
	</section>
	<div class="dashboard-grid">
		<section class="panel">
			<div class="panel-heading dashboard-occupancy-heading">
				<div>
					<h2>Ocupação por sessão</h2>
					<p class="panel-description">Sessões agendadas para <?= Html::encode($occupancyDateLabel) ?>.</p>
				</div>
				<div class="dashboard-occupancy-controls">
					<?= Html::beginForm(['/home/index'], 'get', ['class' => 'dashboard-date-filter']) ?>
						<?= Html::label('Data', 'dashboard-occupancy-date') ?>
						<?= Html::input('date', 'date', $occupancyDate, [
							'id' => 'dashboard-occupancy-date',
							'class' => 'input',
							'data-localized-date' => 'true',
							'lang' => 'pt-PT',
							'onchange' => 'this.form.submit()',
							'aria-label' => 'Selecionar data das sessões',
						]) ?>
					<?= Html::endForm() ?>
					<?php if ($occupancyDate !== $todayDate): ?>
						<?= Html::a('Hoje', ['/home/index'], ['class' => 'text-link']) ?>
					<?php endif ?>
					<?= Html::a('Ver sessões →', ['/sessions/index', 'date' => $occupancyDate], ['class' => 'text-link']) ?>
				</div>
			</div>
			<div class="dashboard-occupancy-summary" aria-label="Resumo de ocupação">
				<strong><?= (int) $occupancySessionCount ?> <?= $occupancySessionCount === 1 ? 'sessão' : 'sessões' ?></strong>
				<span><?= Html::encode($occupancySummary) ?></span>
			</div>
			<div class="occupancies">
				<?php foreach ($sessions as $session): ?>
					<?php
					$capacity = count($session->room?->seats ?? []);
					$sold = count($session->tickets);
					$sessionOccupancy = $capacity > 0 ? (int) round($sold / $capacity * 100) : 0;
					?>
					<?= Html::beginTag('a', [
						'href' => Url::to(['/tickets/index', 'session' => $session->id]),
						'class' => 'occupancy',
						'aria-label' => ($session->movie?->title ?? 'Sessão').', '.$sessionOccupancy.'% de ocupação',
					]) ?>
						<span><?= Html::encode(($session->movie?->title ?? 'Sessão').' · '.($session->room?->name ?? '')) ?></span>
						<span class="progress"><span style="width: <?= $sessionOccupancy ?>%"></span></span>
						<strong><?= $sessionOccupancy ?>%</strong>
					<?= Html::endTag('a') ?>
				<?php endforeach ?>
				<?php if (!$sessions): ?>
					<p class="empty">Não existem sessões agendadas para esta data.</p>
				<?php endif ?>
			</div>
		</section>
		<section class="panel">
			<div class="panel-heading"><h2>Encomendas recentes</h2></div>
			<ul class="recent">
				<?php foreach ($orders as $order): ?>
					<?php $movie = $order->tickets[0]?->session?->movie?->title ?? 'Sem sessão associada'; ?>
					<li>
						<?= Html::a(
							Html::tag('strong', Html::encode($movie))
								.Html::tag('span', $money((float) $order->total).' · '.date('d/m/Y H:i', strtotime((string) $order->created_at))),
							['/orders/view', 'id' => $order->id],
						) ?>
					</li>
				<?php endforeach ?>
			</ul>
			<?php if (!$orders): ?><p class="empty">Ainda não existem encomendas.</p><?php endif ?>
			<?= Html::a('Ver encomendas →', ['/orders/index'], ['class' => 'text-link']) ?>
		</section>
	</div>
</main>
