<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $sessions */
/** @var array $orders */
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => 'Bom dia, Ana.',
		'section' => 'Dashboard',
		'description' => '27 de outubro de 2026 · O seu cinema, hoje.',
		'action' => [
			'label' => 'Nova sessão',
			'url' => ['/sessions/create'],
		],
	]) ?>
	<section class="metrics" aria-label="Resumo de hoje">
		<?php foreach ([
			['Vendas hoje', '420,00 €', '56 bilhetes vendidos'],
			['Bilhetes', '56', '5 sessões hoje'],
			['Ocupação média', '61%', 'Todas as salas'],
		] as [$label, $value, $detail]): ?>
		<article class="panel metric">
				<p><?= Html::encode($label) ?>
			</p>
				<strong><?= Html::encode($value) ?>
			</strong>
				<small><?= Html::encode($detail) ?>
			</small>
			</article>

		<?php endforeach ?>
	</section>
	<div class="dashboard-grid">
		<section class="panel">
			<div class="panel-heading">
				<h2>Ocupação por sessão</h2>
				<?= Html::a(
					'Ver sessões →',
					['/sessions/index'],
					[
						'class' => 'text-link',
					],
				) ?>
			</div>
			<div class="occupancies">
				<?php foreach ($sessions as $session): ?>

					<?= Html::beginTag('a', [
						'href' => yii\helpers\Url::to(
							[
								'/tickets/index',
								'session' => $session['id'],
							],
						),
						'class' => 'occupancy',
						'aria-label' => $session['movie'].', '.$session['time'].', '.$session['occupancy'].'% de ocupação',
					]) ?>
					<span><?= Html::encode($session['time'].' · '.$session['room']) ?>
					</span>
						<span class="progress">
							<span style="width: <?= (int) $session['occupancy'] ?>
%"></span>
						</span>
						<strong><?= Html::encode($session['occupancy']) ?>
%</strong>
					<?= Html::endTag('a') ?>

				<?php endforeach ?>
			</div>
		</section>
		<section class="panel">
			<div class="panel-heading">
				<h2>Vendas recentes</h2>
			</div>
			<ul class="recent">
				<?php foreach ($orders as $index => $order): ?>
					<li>
						<?= Html::a(
							Html::tag('strong', Html::encode($order['movie']))
								.Html::tag('span', '2 bilhetes · há '.[4, 12, 20][$index].' min'),
							[
								'/orders/view',
								'id' => $order['id'],
							],
						) ?>
					</li>

				<?php endforeach ?>
			</ul>
			<?= Html::a(
				'Ver encomendas →',
				['/orders/index'],
				[
					'class' => 'text-link',
				],
			) ?>
		</section>
	</div>
	<p class="demo-note">Pré-visualização com dados de demonstração.</p>
</main>
