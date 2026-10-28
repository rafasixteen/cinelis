<?php

use common\widgets\Button;
use frontoffice\data\Catalog;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $movies */
/** @var string $q */
/** @var string $genre */

$this->title = 'Filmes em exibição';
?>

<section class="catalog">
	<header class="page-heading">
		<h1>
			<span class="desktop-title">O seu lugar no cinema.</span>
			<span class="mobile-title">O seu próximo grande filme.</span>
		</h1>
		<p>Descubra os filmes em exibição e escolha a sua próxima sessão.</p>
	</header>
	<form class="filter-form" method="get" action="<?= Url::to(
		['/movie/index'],
	) ?>
" role="search">
		<div class="ui-field search-field">
			<label for="movie-search">Pesquisar filmes</label>
			<input class="ui-input" id="movie-search" type="search" name="q" value="<?= Html::encode($q) ?>
" placeholder="Pesquisar por título ou género…">
		</div>
		<div class="ui-field">
			<label for="movie-genre">Género</label>
			<select class="ui-select" id="movie-genre" name="genre">
				<option value="">Todos os géneros</option>

					<?php foreach (array_unique(array_column(Catalog::movies(), 'genre')) as $option): ?>
						<option <?= $genre === $option ? 'selected' : '' ?>
>
						<?= Html::encode($option) ?>
						</option>

					<?php endforeach ?>
				</select>
		</div>
		<button class="ui-button ui-button--secondary" type="submit">Filtrar</button>
	</form>
	<div class="section-heading">
		<h2>Em exibição</h2>
		<span class="muted"><?= count($movies) ?>

				<?= count($movies) === 1 ? 'filme' : 'filmes' ?>
			</span>
	</div>

		<?php if ($movies): ?>
			<div class="movie-grid">
			<?php foreach ($movies as $movie): ?>

					<?= $this->render(
						'../components/movie-card',
						compact('movie'),
					) ?>

				<?php endforeach ?>
			</div>

		<?php else: ?>
			<div class="empty">
			<h2>Não encontrámos filmes.</h2>
			<p>Experimente outro título ou género.</p>
				<?= Button::widget([
					'tag' => 'a',
					'url' => ['/movie/index'],
					'label' => 'Ver todos os filmes',
					'variant' => 'secondary',
				]) ?>
			</div>

		<?php endif ?>
		<section class="coming-soon">
		<h2>Em breve</h2>
		<div class="panel">
			<div>
				<h3>Novas histórias, o mesmo cinema.</h3>
				<p class="muted">A próxima programação chega a 5 de novembro.</p>
			</div>
			<span class="ui-badge">Em breve</span>
		</div>
	</section>
	</section>
