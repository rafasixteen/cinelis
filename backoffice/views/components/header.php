<?php

use common\widgets\Icon;
use yii\helpers\Html;

/** @var yii\web\View $this */
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
		<details class="profile-menu">
			<summary class="profile" aria-label="Menu de Ana Silva">
				<span class="avatar" aria-hidden="true">AS</span>
				<span class="profile-copy">
					<strong>Ana Silva</strong>
					<span>Admin</span>
				</span>
				<?= Icon::widget([
					'name' => 'chevron-down',
					'options' => ['class' => 'chevron'],
				]) ?>
			</summary>
			<div class="profile-dropdown">
				<strong>Ana Silva</strong>
				<small>ana@cinelis.pt</small>
				<?= Html::a(
					'O meu perfil',
					[
						'/users/edit',
						'id' => 1,
					],
				) ?>

				<?= Html::a(
					'Login interno',
					['/auth/login'],
				) ?>
				<button type="button" data-demo-action>Terminar sessão</button>
			</div>
		</details>
	</div>
</header>
