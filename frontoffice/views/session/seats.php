<?php

use common\widgets\Button;
use frontoffice\data\Catalog;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $session */
/** @var array $selectedSeats */

$this->title = 'Escolha os seus lugares';
?>

<header class="page-heading">
	<h1>Escolha os seus lugares</h1>
	<p><?= Html::encode($session['movie']['title']) ?>
· <?= Catalog::date($session['date']) ?>
· <?= $session['time'] ?>
· <?= $session['room'] ?>
	</p>
</header>
<?= $this->render(
	'../components/steps',
	[
		'step' => 1,
	],
) ?>
<div
	class="seat-layout"
	data-seat-picker
	data-price="<?= $session['price'] ?>
"
	data-checkout-url="<?= Url::to(
		[
			'/checkout/index',
			'session' => $session['id'],
		],
	) ?>
"
>
	<section class="seat-stage" aria-label="Escolha de lugares">
		<div class="screen" aria-hidden="true">ECRÃ</div>
		<div class="seat-scroll" tabindex="0" aria-label="Mapa de lugares, desloque horizontalmente se necessário">
			<div class="seat-map">
				<?php foreach (range('A', 'E') as $row): ?>
					<div class="seat-row">
						<span class="row-label" aria-hidden="true"><?= $row ?>
						</span>

						<?php foreach (range(1, 10) as $number): ?>

							<?php
							$seat = $row.$number;
							$occupied = in_array($seat, Catalog::occupiedSeats(), true);
							$selected = in_array($seat, $selectedSeats, true);
							?>
							<button
								type="button"
								class="seat <?= $occupied ? 'is-occupied' : '' ?>
"
								data-seat="<?= $seat ?>
"
								aria-label="Fila <?= $row ?>
, lugar <?= $number ?><?= $occupied ? ', ocupado' : '' ?>
"
								aria-pressed="<?= $selected ? 'true' : 'false' ?>
"
								<?= $occupied ? 'disabled' : '' ?>
>
								<?= $occupied ? '×' : ($selected ? '✓' : $number) ?>
							</button>

						<?php endforeach ?>
					</div>

				<?php endforeach ?>
			</div>
		</div>
		<div class="seat-legend">
			<span>
				<i></i>
				Livre
			</span>
			<span>
				<i class="is-occupied">×</i>
				Ocupado
			</span>
			<span>
				<i class="is-selected">✓</i>
				Selecionado
			</span>
		</div>
		<div class="seat-zoom">
			<span class="muted">Amplie para escolher.</span>
			<div>
				<button type="button" class="icon-button" data-seat-zoom="out" aria-label="Reduzir mapa">−</button>
				<button type="button" class="icon-button" data-seat-zoom="in" aria-label="Ampliar mapa">+</button>
			</div>
		</div>
	</section>
	<aside class="panel selection">
		<h2>A sua seleção</h2>
		<p data-seat-summary aria-live="polite">
			<?php if ($selectedSeats): ?>
Lugares <?= Html::encode(implode(', ', $selectedSeats)) ?>
				<br>
				<?= count($selectedSeats) ?>

				<?= count($selectedSeats) === 1 ? 'bilhete' : 'bilhetes' ?>
× <?= Catalog::money($session['price']) ?>

			<?php else: ?>
Selecione pelo menos um lugar.
			<?php endif ?>
		</p>
		<div class="total">
			<span>Total</span>
			<strong data-seat-total><?= Catalog::money(count($selectedSeats) * $session['price']) ?>
			</strong>
		</div>
		<a
			class="ui-button ui-button--primary"
			data-seat-continue
			aria-disabled="<?= $selectedSeats ? 'false' : 'true' ?>
"
			href="<?= Url::to(
				[
					'/checkout/index',
					'session' => $session['id'],
					'seats' => implode(',', $selectedSeats),
				],
			) ?>
"
		>
			Continuar para pagamento
		</a>
		<p class="note">Pré-visualização. Os lugares não são reservados.</p>
	</aside>
</div>
