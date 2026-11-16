<?php

use common\models\Movie;
use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $provider */
/** @var string $q */
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => 'Filmes',
		'description' => 'Faça a gestão do catálogo do cinema.',
		'action' => [
			'label' => 'Adicionar filme',
			'url' => ['/movies/create'],
		],
	]) ?>

	<?= Html::beginForm(['/movies/index'], 'get', ['class' => 'filters']) ?>
	<?= $this->render('../components/field', [
		'name' => 'q',
		'label' => 'Pesquisar',
		'value' => $q,
		'options' => ['placeholder' => 'Título do filme…'],
	]) ?>
	<?= Button::widget([
		'label' => 'Filtrar',
		'variant' => 'secondary',
		'options' => ['type' => 'submit'],
	]) ?>
	<?= Html::a('Limpar', ['/movies/index'], ['class' => 'text-link']) ?>
	<?= Html::endForm() ?>

	<?= $this->render('../components/table', [
		'provider' => $provider,
		'caption' => 'Catálogo de filmes',
		'columns' => [
			['label' => 'Título', 'value' => static fn (Movie $record): string => $record->title],
			[
				'label' => 'Duração',
				'value' => static fn (Movie $record): string => $record->duration_minutes.' min',
			],
			[
				'label' => 'Estreia',
				'value' => static fn (Movie $record): string => $record->release_date
					? date('d/m/Y', strtotime($record->release_date))
					: '—',
			],
			[
				'label' => 'Géneros',
				'value' => static fn (Movie $record): string => implode(', ', array_map(
					static fn (common\models\Genre $genre): string => $genre->name,
					$record->genres,
				)),
			],
			[
				'label' => 'Ações',
				'raw' => true,
				'headerOptions' => ['class' => 'table-actions-header'],
				'contentOptions' => ['class' => 'table-actions-cell'],
				'value' => static fn (Movie $record): string => '<div class="row-actions">'.Button::widget([
					'label' => 'Editar',
					'tag' => 'a',
					'url' => ['/movies/update', 'id' => $record->id],
					'variant' => 'secondary',
					'options' => ['aria-label' => 'Editar '.$record->title],
				]).'</div>',
			],
		],
	]) ?>
</main>
