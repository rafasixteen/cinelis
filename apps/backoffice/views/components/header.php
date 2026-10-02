<?php

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
			'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 22a2.5 2.5 0 0 0 2.4-2h-4.8a2.5 2.5 0 0 0 2.4 2Zm7-6-2-2v-4a5 5 0 0 0-4-4.9V3h-2v2.1A5 5 0 0 0 7 10v4l-2 2v2h14v-2Z"/></svg><span class="notification-dot"></span><span class="sr-only">Notifications</span>',
			['class' => 'icon-button disabled', 'aria-label' => 'Notifications', 'aria-disabled' => 'true'],
		) ?>

		<div class="profile">
			<div class="avatar" aria-hidden="true">SM</div>
			<div class="profile-copy">
				<strong>Admin</strong>
				<span>Venue manager</span>
			</div>
			<svg class="chevron" viewBox="0 0 24 24" aria-hidden="true">
				<path d="m7 10 5 5 5-5H7Z" />
			</svg>
		</div>
	</div>
</header>