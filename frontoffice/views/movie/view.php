<?php

use common\widgets\Button;
use frontoffice\data\Catalog;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $movie */
/** @var array $sessions */
/** @var string $date */

$this->title = $movie['title'];
?>

<article class="movie-detail">
	<div class="detail-poster">
		<?= $this->render(
			'../components/poster',
			compact('movie'),
		) ?>

		<?= Button::widget([
			'label' => 'Ver trailer',
			'icon' => 'play',
			'variant' => 'secondary',
			'options' => [
				'data-demo-action' => true,
			],
		]) ?>
	</div>
	<div class="detail-heading">
		<span class="ui-badge">EM EXIBIÇÃO</span>
		<h1><?= Html::encode($movie['title']) ?>
		</h1>
		<p class="muted">
			<?= $movie['rating'] ?>
			<span>·</span>
			<?= $movie['duration'] ?>
min
			<span>·</span>
			<?= Html::encode($movie['genre']) ?>
			<span>·</span>
			<?= Html::encode($movie['country']) ?>
		</p>
	</div>
	<div class="detail-content">
		<p class="synopsis"><?= Html::encode($movie['synopsis']) ?>
		</p>
		<section aria-labelledby="available-sessions">
			<h2 id="available-sessions">Sessões disponíveis</h2>
			<nav class="tabs" aria-label="Data da sessão">
				<?php foreach (Catalog::days() as $day => $label): ?>

					<?= Button::widget([
						'tag' => 'a',
						'label' => $label,
						'url' => [
							'/movie/view',
							'id' => $movie['id'],
							'date' => $day,
						],
						'variant' => $day === $date ? 'primary' : 'secondary',
						'options' => [
							'aria-current' => $day === $date ? 'date' : null,
						],
					]) ?>

				<?php endforeach ?>
			</nav>
			<div class="session-grid">
				<?php foreach ($sessions as $session): ?>
					<article class="panel session-card">
						<h3><?= $session['time'] ?>
						</h3>
						<p class="muted"><?= $session['room'] ?>
· <?= Catalog::money($session['price']) ?>
						</p>
						<?= Button::widget([
							'tag' => 'a',
							'label' => 'Escolher lugares',
							'variant' => 'secondary',
							'url' => [
								'/session/seats',
								'id' => $session['id'],
							],
							'options' => [
								'aria-label' => 'Escolher lugares para '.$session['time'],
							],
						]) ?>
					</article>

				<?php endforeach ?>
			</div>
		</section>
	</div>
</article>
