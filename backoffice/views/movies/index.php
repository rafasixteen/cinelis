<?php

use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ArrayDataProvider $provider */
/** @var string $q */
/** @var string $status */
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

	<?= Html::beginForm(
		['/movies/index'],
		'get',
		[
			'class' => 'filters',
		],
	) ?>

	<?= $this->render('../components/field', [
		'name' => 'q',
		'label' => 'Pesquisar',
		'value' => $q,
		'options' => ['placeholder' => 'Título do filme…'],
	]) ?>

	<?= $this->render('../components/field', [
		'name' => 'status',
		'label' => 'Estado',
		'value' => $status,
		'items' => [
			'' => 'Todos',
			'Em cartaz' => 'Em cartaz',
			'Em breve' => 'Em breve',
		],
	]) ?>

	<?= Button::widget([
		'label' => 'Filtrar',
		'variant' => 'secondary',
		'options' => ['type' => 'submit'],
	]) ?>

	<?= Html::a(
		'Limpar',
		['/movies/index'],
		[
			'class' => 'text-link',
		],
	) ?>

	<?= Html::endForm() ?>

	<?= $this->render('../components/table', [
		'provider' => $provider,
		'caption' => 'Catálogo de filmes',
		'columns' => [
			['label' => 'Título', 'value' => 'title'],
			[
				'label' => 'Duração',
				'value' => static fn (array $record): string => $record['duration'].' min',
			],
			[
				'label' => 'Estreia',
				'value' => static fn (array $record): string => date('d/m/Y', strtotime($record['premiere'])),
			],
			['label' => 'Estado', 'value' => 'status'],
			[
				'label' => 'Ações',
				'raw' => true,
				'value' => static fn (array $record): string => '<div class="row-actions">'
					.Html::a(
						'Editar',
						[
							'/movies/edit',
							'id' => $record['id'],
						],
						['aria-label' => 'Editar '.$record['title']],
					)
					.Html::button(
						'Remover',
						[
							'type' => 'button',
							'data-demo-action' => true,
							'class' => 'remove',
							'aria-label' => 'Remover '.$record['title'],
						],
					)
					.'</div>',
			],
		],
	]) ?>
</main>
