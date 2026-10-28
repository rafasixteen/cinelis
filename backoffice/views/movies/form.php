<?php

/** @var yii\web\View $this */
/** @var array $record */
/** @var bool $isNew */
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => $isNew ? 'Adicionar filme' : 'Editar filme',
		'section' => 'Filmes',
		'description' => $isNew ? 'Acrescente uma nova história ao catálogo.' : 'Catálogo / '.$record['title'],
	]) ?>
	<form method="post" class="edit-form" data-demo-form novalidate>
		<div class="panel form-grid form-grid--movie">
			<?= $this->render('../components/field', [
				'name' => 'title',
				'label' => 'Título',
				'value' => $record['title'],
				'options' => ['autocomplete' => 'off'],
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'duration',
				'label' => 'Duração (min)',
				'type' => 'number',
				'value' => $record['duration'],
				'options' => ['min' => 1],
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'rating',
				'label' => 'Classificação',
				'value' => $record['rating'],
				'items' => [
					'M/6' => 'M/6',
					'M/12' => 'M/12',
					'M/16' => 'M/16',
					'M/18' => 'M/18',
				],
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'synopsis',
				'label' => 'Sinopse',
				'type' => 'textarea',
				'value' => $record['synopsis'],
				'class' => 'field--full',
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'genre',
				'label' => 'Género',
				'value' => $record['genre'],
				'items' => [
					'Drama' => 'Drama',
					'Romance' => 'Romance',
					'Ficção científica' => 'Ficção científica',
					'Aventura' => 'Aventura',
					'Suspense' => 'Suspense',
				],
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'premiere',
				'label' => 'Estreia',
				'type' => 'date',
				'value' => $record['premiere'],
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'status',
				'label' => 'Estado',
				'value' => $record['status'],
				'items' => [
					'Em cartaz' => 'Em cartaz',
					'Em breve' => 'Em breve',
				],
			]) ?>
			<div class="form-grid field--full">
				<?= $this->render('../components/field', [
					'name' => 'poster',
					'label' => 'Poster URL',
					'type' => 'url',
					'options' => ['placeholder' => 'https://…'],
				]) ?>

				<?= $this->render('../components/field', [
					'name' => 'trailer',
					'label' => 'Trailer URL',
					'type' => 'url',
					'options' => ['placeholder' => 'https://…'],
				]) ?>
			</div>
		</div>
		<?= $this->render('../components/form-actions', [
			'cancel' => ['/movies/index'],
			'label' => 'Guardar filme',
		]) ?>
	</form>
</main>
