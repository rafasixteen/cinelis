<?php

use common\widgets\Badge;
use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ArrayDataProvider $provider */
/** @var string $q */
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => 'Utilizadores internos',
		'section' => 'Utilizadores',
		'description' => 'Acessos à gestão do cinema · Área de administração demonstrativa',
		'action' => [
			'label' => 'Adicionar utilizador',
			'url' => ['/users/create'],
		],
	]) ?>

	<?= Html::beginForm(
		['/users/index'],
		'get',
		[
			'class' => 'filters',
		],
	) ?>

	<?= $this->render('../components/field', [
		'name' => 'q',
		'label' => 'Pesquisar utilizador',
		'value' => $q,
		'options' => ['placeholder' => 'Nome ou email…'],
	]) ?>

	<?= Button::widget([
		'label' => 'Pesquisar',
		'variant' => 'secondary',
		'options' => ['type' => 'submit'],
	]) ?>

	<?= Html::a(
		'Limpar',
		['/users/index'],
		[
			'class' => 'text-link',
		],
	) ?>

	<?= Html::endForm() ?>

	<?= $this->render('../components/table', [
		'provider' => $provider,
		'caption' => 'Utilizadores internos',
		'columns' => [
			['label' => 'Nome', 'value' => 'name'],
			['label' => 'Email', 'value' => 'email'],
			[
				'label' => 'Perfil',
				'raw' => true,
				'value' => static fn (array $record): string => Badge::widget([
					'label' => $record['role'],
				]),
			],
			[
				'label' => 'Ações',
				'raw' => true,
				'value' => static fn (array $record): string => Html::a(
					'Editar',
					[
						'/users/edit',
						'id' => $record['id'],
					],
					[
						'class' => 'text-link',
						'aria-label' => 'Editar '.$record['name'],
					],
				),
			],
		],
	]) ?>
</main>
