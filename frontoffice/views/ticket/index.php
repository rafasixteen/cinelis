<?php

use common\widgets\Button;
use frontoffice\data\Catalog;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $tickets */
/** @var string $period */

$this->title = 'Os meus bilhetes';
?>

<div class="section-heading ticket-heading">
	<header class="page-heading">
		<h1>Os meus bilhetes</h1>
		<p>A sua próxima ida ao cinema começa aqui.</p>
	</header>
	<nav class="tabs" aria-label="Filtrar bilhetes">
		<?php foreach ([
			'upcoming' => 'Próximos',
			'past' => 'Anteriores',
		] as $value => $label): ?>

		<?= Button::widget([
			'tag' => 'a',
			'url' => [
				'/ticket/index',
				'period' => $value,
			],
			'label' => $label,
			'variant' => $period === $value ? 'primary' : 'secondary',
			'options' => ['aria-current' => $period === $value ? 'page' : null],
		]) ?>

		<?php endforeach ?>
	</nav>
</div>
<p class="note demo-note">Bilhetes de demonstração · sem validade para entrada.</p>
<div class="ticket-list">
	<?php foreach ($tickets as $ticket): ?>
		<article class="panel ticket-row">
			<div class="ticket-poster">
				<?= $this->render(
					'../components/poster',
					[
						'movie' => $ticket['movie'],
					],
				) ?>
			</div>
			<div class="ticket-info">
				<h2><?= Html::encode($ticket['movie']['title']) ?>
				</h2>
				<p class="muted">
					<?= Catalog::date($ticket['date']) ?>
· <?= $ticket['time'] ?>
· <?= $ticket['room'] ?>
· Lugar <?= $ticket['seat'] ?>
				</p>
			</div>
			<span class="ui-badge ui-badge--success">
				<?= $period === 'past' ? 'Sessão anterior' : 'Exemplo' ?>
			</span>
			<?= Button::widget([
				'tag' => 'a',
				'label' => 'Ver bilhete',
				'url' => [
					'/ticket/view',
					'id' => $ticket['id'],
					'period' => $period,
				],
				'variant' => 'secondary',
				'options' => [
					'aria-label' => 'Ver bilhete de '.$ticket['movie']['title'],
				],
			]) ?>
		</article>

	<?php endforeach ?>
</div>
