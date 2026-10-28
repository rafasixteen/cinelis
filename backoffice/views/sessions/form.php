<?php

use backoffice\data\Operations;

/** @var yii\web\View $this */
/** @var array $record */
/** @var bool $isNew */
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => $isNew ? 'Criar sessão' : 'Editar sessão',
		'section' => 'Sessões',
		'description' => 'Defina o filme, a sala e o horário.',
	]) ?>
	<form method="post" data-demo-form novalidate>
		<div class="panel form-grid">
			<?= $this->render('../components/field', [
				'name' => 'movie',
				'label' => 'Filme',
				'value' => $record['movieId'],
				'items' => array_column(Operations::movies(), 'title', 'id'),
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'room',
				'label' => 'Sala',
				'value' => $record['roomId'],
				'items' => array_column(
					array_map(
						static fn (array $room): array => [
							'id' => $room['id'],
							'name' => $room['name'].' · '.$room['capacity'].' lugares',
						],
						Operations::rooms(),
					),
					'name',
					'id',
				),
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'datetime',
				'label' => 'Data / hora',
				'type' => 'datetime-local',
				'value' => $record['date'].'T'.$record['time'],
			]) ?>

			<?= $this->render('../components/field', [
				'name' => 'price',
				'label' => 'Preço por bilhete (€)',
				'type' => 'number',
				'value' => number_format($record['price'], 2, '.', ''),
				'options' => [
					'min' => 0,
					'step' => '0.50',
				],
			]) ?>
			<div class="notice field--full">
				<h2>Disponibilidade da sala</h2>
				<p>Pré-visualização do horário. A disponibilidade será validada quando a criação de sessões estiver implementada.</p>
			</div>
		</div>
		<?= $this->render('../components/form-actions', [
			'cancel' => ['/sessions/index'],
			'label' => $isNew ? 'Criar sessão' : 'Guardar sessão',
		]) ?>
	</form>
</main>
