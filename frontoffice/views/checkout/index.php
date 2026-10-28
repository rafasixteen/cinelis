<?php

use frontoffice\data\Catalog;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $session */
/** @var array $seats */

$this->title = 'Resumo da compra';
$movie = $session['movie'];
$total = count($seats) * $session['price'];
?>

<header class="page-heading">
	<h1>Resumo da compra</h1>
	<p>2 de 3 · Confirme os detalhes e escolha como pagar.</p>
</header>
<div class="checkout-grid">
	<section class="panel order-summary">
		<div class="order-movie">
			<div class="order-poster">
		<?= $this->render(
			'../components/poster',
			compact('movie'),
		) ?>
			</div>
			<div>
				<h2><?= Html::encode($movie['title']) ?>
				</h2>
				<p class="muted">
					<?= Catalog::date($session['date']) ?>
· <?= $session['time'] ?>
					<br>
					<?= $session['room'] ?>
· Lugares <?= Html::encode(implode(', ', $seats)) ?: 'por selecionar' ?>
					<br>
					<?= count($seats) ?>
bilhetes × <?= Catalog::money($session['price']) ?>
				</p>
			</div>
		</div>
		<div class="total">
			<span>Total</span>
			<strong><?= Catalog::money($total) ?>
			</strong>
		</div>
		<?= Html::a(
			'Alterar lugares',
			[
				'/session/seats',
				'id' => $session['id'],
				'seats' => implode(',', $seats),
			],
			['class' => 'text-link'],
		) ?>
	</section>
	<section class="panel payment">
		<h2>Pagamento</h2>
		<form data-demo-form novalidate method="post" action="<?= Url::to(
			['/checkout/index'],
		) ?>
">
			<div class="ui-field">
				<label for="payment-method">Método de pagamento</label>
				<select class="ui-select" id="payment-method" data-payment-method>
					<option value="mbway">MB WAY</option>
					<option value="card">Cartão bancário</option>
				</select>
			</div>
			<div class="ui-field" data-payment-phone>
				<label for="payment-phone">Número de telemóvel</label>
				<input class="ui-input" id="payment-phone" type="tel" autocomplete="tel" placeholder="912 345 678">
			</div>
			<div class="ui-field" data-payment-card hidden>
				<label for="payment-card">Número do cartão (demonstração)</label>
				<input class="ui-input" id="payment-card" inputmode="numeric" autocomplete="off" placeholder="0000 0000 0000 0000">
			</div>
			<p class="note">Demonstração: não é efetuado qualquer pagamento.</p>
			<button class="ui-button ui-button--primary" type="submit" data-demo-action <?= !$seats ? 'disabled' : '' ?>
>
				Pagar <?= Catalog::money($total) ?>
			</button>
		</form>
	</section>
</div>
<div class="preview-link">
		<?= Html::a(
			'Ver exemplo de confirmação e bilhete',
			['/checkout/confirmation'],
			[
				'class' => 'text-link',
			],
		) ?>
	<span class="note">Pré-visualização independente da compra.</span>
</div>
