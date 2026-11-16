<?php

use common\models\Order;
use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Order $order */

$firstTicket = $order->tickets[0] ?? null;
$movie = $firstTicket?->session?->movie?->title ?? '—';
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => 'Encomenda #'.substr((string) $order->id, 0, 8),
		'section' => 'Encomendas',
		'description' => date('d/m/Y H:i', strtotime((string) $order->created_at)),
	]) ?>
	<section class="panel">
		<div class="panel-heading"><h2>Resumo da encomenda</h2></div>
		<dl class="details">
			<div><dt>UUID</dt><dd><?= Html::encode((string) $order->id) ?></dd></div>
			<div><dt>Cliente</dt><dd><?= Html::encode($order->user?->username ?? '—') ?></dd></div>
			<div><dt>Filme</dt><dd><?= Html::encode($movie) ?></dd></div>
			<div><dt>Bilhetes</dt><dd><?= count($order->tickets) ?></dd></div>
			<div><dt>Total</dt><dd><?= number_format((float) $order->total, 2, ',', '') ?> €</dd></div>
		</dl>
		<div class="inline-actions">
			<?php if ($firstTicket?->session): ?>
				<?= Button::widget([
					'label' => 'Ver bilhetes da sessão',
					'tag' => 'a',
					'url' => ['/tickets/index', 'session' => $firstTicket->session_id],
					'variant' => 'secondary',
				]) ?>
			<?php endif ?>
		</div>
	</section>
	<p class="back-link"><?= Html::a('← Voltar às encomendas', ['/orders/index'], ['class' => 'text-link']) ?></p>
</main>
