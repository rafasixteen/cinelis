<?php

use common\rbac\RbacInstaller;
use common\widgets\Icon;
use yii\helpers\Html;

/** @var yii\web\View $this */

$identity = Yii::$app->user->identity;
$username = $identity?->username ?? '';
$roles = $identity === null ? [] : Yii::$app->authManager->getRolesByUser((string) $identity->getId());
$role = array_key_first($roles) ?? RbacInstaller::ROLE_VISITOR;
$roleLabels = [
	RbacInstaller::ROLE_VISITOR => 'Visitante',
	RbacInstaller::ROLE_CUSTOMER => 'Cliente',
	RbacInstaller::ROLE_EMPLOYEE => 'Funcionário',
	RbacInstaller::ROLE_ADMIN => 'Administrador',
];
$initials = $identity === null ? '' : mb_strtoupper(
	mb_substr($username, 0, 2),
);
?>

<header class="topbar">
	<div class="breadcrumb">
		<span>Operações</span>
		<span aria-hidden="true">/</span>
		<span><?= Html::encode($this->params['section'] ?? 'Dashboard') ?>
		</span>
	</div>
	<div class="topbar-actions">
		<button
			class="icon-button"
			type="button"
			data-theme-toggle
			aria-label="Alternar tema claro e escuro"
		>
			<?= Icon::widget(
				[
					'name' => 'sun-moon',
				],
			) ?>
		</button>
		<?php if ($identity !== null): ?>
		<details class="profile-menu">
			<summary class="profile" aria-label="Menu de <?= Html::encode($username) ?>">
				<span class="avatar" aria-hidden="true"><?= Html::encode($initials) ?></span>
				<span class="profile-copy">
					<strong><?= Html::encode($username) ?></strong>
					<span><?= Html::encode($roleLabels[$role] ?? $role) ?></span>
				</span>
				<?= Icon::widget([
					'name' => 'chevron-down',
					'options' => ['class' => 'chevron'],
				]) ?>
			</summary>
			<div class="profile-dropdown">
				<strong><?= Html::encode($username) ?></strong>
				<small><?= Html::encode($identity->email) ?></small>
				<?= Html::a(
					'O meu perfil',
					[
						'/users/update',
						'id' => $identity->id,
					],
				) ?>
				<?= Html::beginForm(['/auth/logout'], 'post') ?>
					<?= Html::submitButton('Terminar sessão') ?>
				<?= Html::endForm() ?>
			</div>
		</details>
		<?php else: ?>
			<?= Html::a('Iniciar sessão', ['/auth/login'], ['class' => 'text-link']) ?>
		<?php endif ?>
	</div>
</header>
