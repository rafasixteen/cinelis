<?php

use common\widgets\Icon;
use yii\helpers\Html;

?>

<header class="topbar">
	<div>
		<p class="topbar-context">Cinelis Lisboa</p>
		<p class="topbar-title">Backoffice</p>
	</div>

	<div class="topbar-actions">
		<?= Html::tag(
			'span',
			Icon::widget([
				'name' => 'bell',
				'options' => ['aria-hidden' => 'true'],
			])
			. '<span class="notification-dot"></span>'
			. '<span class="sr-only">Notifications</span>',
			[
				'class' => 'icon-button disabled',
				'aria-label' => 'Notifications',
				'aria-disabled' => 'true',
			],
		) ?>

		<div class="profile">
			<div class="avatar" aria-hidden="true">SM</div>
			<div class="profile-copy">
				<strong>Admin</strong>
				<span>Venue manager</span>
			</div>
			<?= Icon::widget([
				'name' => 'chevron-down',
				'options' => ['class' => 'chevron', 'aria-hidden' => 'true'],
			]) ?>
		</div>
	</div>
</header>
