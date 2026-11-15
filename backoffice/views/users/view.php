<?php

use common\widgets\Badge;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\User $model */
/** @var string $role */

$this->title = 'Utilizador · '.$model->username;
$this->params['section'] = 'Utilizadores';
?>

<main id="main-content" class="page">
	<?= $this->render('../components/page-heading', [
		'title' => $model->username,
		'section' => 'Utilizadores',
		'description' => $model->email,
		'action' => [
			'label' => 'Editar utilizador',
			'url' => ['/users/update', 'id' => $model->id],
		],
	]) ?>

	<section class="panel detail-grid">
		<div>
			<strong>Email</strong>
			<p><?= Html::encode($model->email) ?></p>
		</div>
		<div>
			<strong>Perfil</strong>
			<p><?= Badge::widget(['label' => common\rbac\RbacInstaller::getRoleLabel($role)]) ?></p>
		</div>
		<div>
			<strong>Estado</strong>
			<p><?= $model->status === common\models\User::STATUS_DELETED ? 'Eliminado' : 'Ativo' ?></p>
		</div>
	</section>

	<?php if ($model->status !== common\models\User::STATUS_DELETED): ?>
		<?= Html::beginForm(['/users/delete', 'id' => $model->id], 'post') ?>
			<?= Html::submitButton('Eliminar utilizador', ['class' => 'ui-button ui-button--danger']) ?>
		<?= Html::endForm() ?>
	<?php else: ?>
		<?= Html::beginForm(['/users/restore', 'id' => $model->id], 'post') ?>
			<?= Html::submitButton('Restaurar utilizador', ['class' => 'ui-button']) ?>
		<?= Html::endForm() ?>
	<?php endif ?>
</main>
