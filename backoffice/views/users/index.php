<?php

use common\rbac\RbacInstaller;
use common\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $provider */
/** @var string $q */
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => 'Utilizadores internos',
		'section' => 'Utilizadores',
		'description' => 'Acessos à gestão do cinema.',
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
			[
				'label' => 'Nome',
				'value' => static fn (common\models\User $record): string => $record->username,
			],
			[
				'label' => 'Email',
				'value' => static fn (common\models\User $record): string => $record->email,
			],
			[
				'label' => 'Roles',
				'raw' => true,
				'value' => static fn (common\models\User $record): string => Html::encode(
					RbacInstaller::getRoleLabel(
						RbacInstaller::getUserRole(Yii::$app->authManager, $record->getId()),
					),
				),
			],
			[
				'label' => 'Estado',
				'value' => static fn (common\models\User $record): string => (
					$record->status === common\models\User::STATUS_DELETED ? 'Eliminado' : 'Ativo'
				),
			],
			[
				'label' => 'Ações',
				'raw' => true,
				'headerOptions' => ['class' => 'table-actions-header'],
				'contentOptions' => ['class' => 'table-actions-cell'],
				'value' => static function (common\models\User $record): string {
					$actions = '<div class="row-actions">'.Button::widget([
						'label' => 'Ver',
						'tag' => 'a',
						'url' => ['/users/view', 'id' => $record->id],
						'variant' => 'secondary',
					]).Button::widget([
						'label' => 'Editar',
						'tag' => 'a',
						'url' => ['/users/update', 'id' => $record->id],
						'variant' => 'secondary',
					]);

					$action = $record->status === common\models\User::STATUS_DELETED ? 'restore' : 'delete';
					$label = $record->status === common\models\User::STATUS_DELETED ? 'Restaurar' : 'Eliminar';
					$actions .= Html::beginForm(
						['/users/'.$action, 'id' => $record->id],
						'post',
						['class' => 'row-action-form'],
					).Button::widget([
						'label' => $label,
						'variant' => $record->status === common\models\User::STATUS_DELETED ? 'success' : 'danger',
						'options' => ['type' => 'submit'],
					]).Html::endForm();

					return $actions.'</div>';
				},
			],
		],
	]) ?>
</main>
