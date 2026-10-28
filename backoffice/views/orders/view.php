<?php

use backoffice\data\Operations;
use common\widgets\Badge;
use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $order */
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => 'Encomenda #'.$order['id'],
		'section' => 'Encomendas',
		'description' => '27 de outubro de 2026 · Dados de demonstração',
	]) ?>
	<section class="panel">
		<div class="panel-heading">
			<h2>Resumo da encomenda</h2>
			<?= Badge::widget([
				'label' => $order['status'],
				'variant' => $order['status'] === 'Paga' ? 'success' : 'default',
			]) ?>
		</div>
		<dl class="details">
			<div>
				<dt>Cliente</dt>
				<dd><?= Html::encode($order['customer']) ?>
				</dd>
			</div>
			<div>
				<dt>Filme</dt>
				<dd><?= Html::encode($order['movie']) ?>
				</dd>
			</div>
			<div>
				<dt>Bilhetes</dt>
				<dd><?= $order['quantity'] ?>
				</dd>
			</div>
			<div>
				<dt>Total</dt>
				<dd><?= Operations::money($order['total']) ?>
				</dd>
			</div>
			<div>
				<dt>Pagamento</dt>
				<dd>Cartão · demonstração</dd>
			</div>
		</dl>
		<div class="inline-actions">
			<?= Button::widget([
				'label' => 'Ver bilhetes da sessão',
				'tag' => 'a',
				'url' => [
					'/tickets/index',
					'session' => $order['sessionId'],
				],
				'variant' => 'secondary',
			]) ?>

			<?= Button::widget([
				'label' => 'Reenviar bilhetes',
				'options' => ['data-demo-action' => true],
			]) ?>

			<?= Button::widget([
				'label' => 'Cancelar encomenda',
				'variant' => 'secondary',
				'options' => ['data-demo-action' => true],
			]) ?>
		</div>
	</section>
	<p class="back-link">
		<?= Html::a(
			'← Voltar às encomendas',
			['/orders/index'],
			[
				'class' => 'text-link',
			],
		) ?>
	</p>
</main>
