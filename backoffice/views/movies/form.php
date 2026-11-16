<?php

use common\models\Genre;
use common\models\Movie;
use common\widgets\Combobox;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var Movie $model */
/** @var list<Genre> $genres */
/** @var list<string> $genreIds */
/** @var bool $isNew */

$this->title = ($isNew ? 'Adicionar filme' : 'Editar filme').' · CineLis Backoffice';
$this->params['section'] = 'Filmes';
$genreItems = array_combine(
	array_map(static fn (Genre $genre): string => (string) $genre->id, $genres),
	array_map(static fn (Genre $genre): string => $genre->name, $genres),
);
?>

<main id="main-content" class="page movie-editor-page">
	<?= $this->render('../components/page-heading', [
		'title' => $isNew ? 'Adicionar filme' : 'Editar filme',
		'section' => 'Filmes',
		'description' => $isNew ? 'Acrescente uma nova história ao catálogo.' : $model->title,
	]) ?>

	<?php $form = ActiveForm::begin([
		'options' => ['class' => 'movie-editor-form', 'novalidate' => true],
		'fieldConfig' => [
			'options' => ['class' => 'field'],
			'inputOptions' => ['class' => 'input'],
		],
	]) ?>
		<section class="panel movie-editor">
			<header class="movie-editor__header">
				<div>
					<p class="movie-editor__eyebrow">Catálogo</p>
					<h2><?= $isNew ? 'Detalhes do filme' : 'Editar detalhes' ?></h2>
				</div>
				<span class="movie-editor__mode"><?= $isNew ? 'Novo registo' : 'Em edição' ?></span>
			</header>

			<fieldset class="movie-editor__section">
				<legend>Informação principal</legend>
				<div class="movie-editor__grid movie-editor__grid--primary">
					<?= $form->field($model, 'title')->textInput([
						'autocomplete' => 'off',
						'placeholder' => 'Título do filme',
					]) ?>
					<?= $form->field($model, 'synopsis')->textarea([
						'rows' => 5,
						'class' => 'input',
						'placeholder' => 'Escreva uma sinopse curta…',
					]) ?>
				</div>
			</fieldset>

			<fieldset class="movie-editor__section">
				<legend>Classificação</legend>
				<div class="movie-editor__grid movie-editor__grid--metadata">
					<?= $form->field($model, 'duration_minutes')->input('number', [
						'min' => 1,
						'placeholder' => '120',
					]) ?>
					<?= $form->field($model, 'release_date')->input('date', [
						'data-localized-date' => 'true',
						'lang' => 'pt-PT',
					]) ?>
					<?= $form->field($model, 'age_rating')->dropDownList([
						'M/6' => 'M/6',
						'M/12' => 'M/12',
						'M/16' => 'M/16',
						'M/18' => 'M/18',
					], ['prompt' => 'Selecionar classificação']) ?>
				</div>
				<div class="field movie-editor__genres">
					<?= Html::label('Géneros', 'genreIds', ['class' => 'control-label']) ?>
					<?= Combobox::widget([
						'name' => 'genreIds',
						'value' => $genreIds,
						'items' => $genreItems,
						'options' => [
							'id' => 'genreIds',
							'multiple' => true,
							'size' => 6,
							'aria-label' => 'Géneros',
						],
					]) ?>
					<small>Selecione um ou mais géneros.</small>
				</div>
			</fieldset>

			<fieldset class="movie-editor__section">
				<legend>Media</legend>
				<div class="movie-editor__grid movie-editor__grid--media">
					<?= $form->field($model, 'poster_url')->input('url', ['placeholder' => 'https://…']) ?>
					<?= $form->field($model, 'trailer_url')->input('url', ['placeholder' => 'https://…']) ?>
				</div>
			</fieldset>

			<div class="form-actions movie-editor__actions">
				<?= Html::a('Cancelar', ['/movies/index'], ['class' => 'ui-button ui-button--secondary']) ?>
				<?= Html::submitButton($isNew ? 'Criar filme' : 'Guardar alterações', ['class' => 'ui-button ui-button--primary']) ?>
			</div>
		</section>
	<?php ActiveForm::end() ?>
</main>
