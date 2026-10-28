<?php

use common\widgets\Button;
use frontoffice\data\Catalog;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $sessions */
/** @var string $movie */
/** @var string $date */

$this->title = 'Sessões';
?>

<header class="page-heading">
	<h1>Sessões</h1>
	<p>Encontre o melhor momento para ir ao cinema.</p>
</header>
<form class="filter-form" method="get" action="<?= Url::to(
	['/session/index'],
) ?>
">
	<div class="ui-field">
		<label for="session-movie">Filme</label>
		<select class="ui-select" id="session-movie" name="movie">
			<option value="">Todos os filmes</option>

			<?php foreach (Catalog::movies() as $film): ?>
				<option value="<?= Html::encode($film['id']) ?>
" <?= $movie === $film['id'] ? 'selected' : '' ?>
>
					<?= Html::encode($film['title']) ?>
				</option>

			<?php endforeach ?>
		</select>
	</div>
	<div class="ui-field">
		<label for="session-date">Data</label>
		<select class="ui-select" id="session-date" name="date">
			<?php foreach (Catalog::days() as $day => $label): ?>
				<option value="<?= Html::encode($day) ?>
" <?= $date === $day ? 'selected' : '' ?>
>
					<?= Html::encode($label) ?>
				</option>

			<?php endforeach ?>
		</select>
	</div>
	<button class="ui-button ui-button--secondary" type="submit">Filtrar</button>
</form>
<div class="session-list">
	<?php foreach ($sessions as $session): ?>
		<article class="panel">
			<div>
				<h2>
					<?= Html::a(
						Html::encode($session['movie']['title']),
						[
							'/movie/view',
							'id' => $session['movie']['id'],
						],
					) ?>
				</h2>
				<p class="muted">
					<?= Catalog::date($session['date']) ?>
· <?= $session['room'] ?>
· <?= Catalog::money($session['price']) ?>
				</p>
			</div>
			<strong><?= $session['time'] ?>
			</strong>
			<?= Button::widget([
				'tag' => 'a',
				'label' => 'Escolher lugares',
				'variant' => 'secondary',
				'url' => [
					'/session/seats',
					'id' => $session['id'],
				],
				'options' => [
					'aria-label' => 'Escolher lugares para '.$session['movie']['title'].' às '.$session['time'],
				],
			]) ?>
		</article>

	<?php endforeach ?>
</div>

<?php if (!$sessions): ?>
	<div class="empty">
		<h2>Sem sessões para esta pesquisa.</h2>
		<?= Html::a(
			'Ver todas as sessões',
			['/session/index'],
			[
				'class' => 'ui-button ui-button--secondary',
			],
		) ?>
	</div>

<?php endif ?>
