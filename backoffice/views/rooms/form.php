<?php

/** @var yii\web\View $this */
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => 'Adicionar sala',
		'section' => 'Salas',
		'description' => 'Defina a identificação e a capacidade da sala.',
	]) ?>
	<form method="post" data-demo-form novalidate>
		<div class="panel form-grid">
			<?= $this->render('../components/field', [
				'name' => 'name',
				'label' => 'Nome da sala',
				'options' => ['placeholder' => 'Sala 4'],
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'status',
				'label' => 'Estado',
				'items' => [
					'Ativa' => 'Ativa',
					'Inativa' => 'Inativa',
				],
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'rows',
				'label' => 'Número de filas',
				'type' => 'number',
				'value' => 6,
				'options' => [
					'min' => 1,
					'max' => 26,
				],
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'columns',
				'label' => 'Lugares por fila',
				'type' => 'number',
				'value' => 10,
				'options' => [
					'min' => 1,
					'max' => 30,
				],
			]) ?>
		</div>
		<?= $this->render('../components/form-actions', [
			'cancel' => ['/rooms/index'],
			'label' => 'Criar sala',
		]) ?>
	</form>
</main>
