<?php

use yii\helpers\Html;
?>

<aside class="sidebar" aria-label="Primary navigation">
	<div class="sidebar-brand">
		<?= Html::a(
			'<span class="brand-mark">C</span><span>Cinelis <small>Backoffice</small></span>',
			['/'],
			['class' => 'brand'],
		) ?>
	</div>

	<nav class="sidebar-nav">
		<p class="nav-label">Workspace</p>

		<?= Html::a(
			'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 13h6V4H4v9Zm0 7h6v-4H4v4Zm10 0h6v-9h-6v9Zm0-16v4h6V4h-6Z"/></svg><span>Overview</span>',
			['/home/index'],
			[
				'class' => 'nav-link active',
				'aria-current' => 'page',
				'aria-label' => 'Overview',
			],
		) ?>

		<?= Html::tag(
			'span',
			'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 6 2-3h3L7 6h3l2-3h3l-2 3h3l2-3h1a2 2 0 0 1 2 2v15a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 1-2Zm1 5v9h14v-9H5Z"/></svg><span>Movies</span>',
			['class' => 'nav-link disabled', 'aria-label' => 'Movies', 'aria-disabled' => 'true'],
		) ?>

		<?= Html::tag(
			'span',
			'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2h2v2h6V2h2v2h1a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h1V2Zm12 8H5v9a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-9Z"/></svg><span>Sessions</span>',
			['class' => 'nav-link disabled', 'aria-label' => 'Sessions', 'aria-disabled' => 'true'],
		) ?>

		<?= Html::tag(
			'span',
			'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h14a2 2 0 0 1 2 2v5H3V5a2 2 0 0 1 2-2Zm-2 9h18v7a2 2 0 0 1-2 2h-1v-4H6v4H5a2 2 0 0 1-2-2v-7Zm5 5h8v4H8v-4Z"/></svg><span>Rooms &amp; seats</span>',
			['class' => 'nav-link disabled', 'aria-label' => 'Rooms and seats', 'aria-disabled' => 'true'],
		) ?>

		<?= Html::tag(
			'span',
			'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 2h14a2 2 0 0 1 2 2v18l-3-2-3 2-3-2-3 2-3-2-3 2V4a2 2 0 0 1 2-2Zm2 5v2h10V7H7Zm0 4v2h10v-2H7Zm0 4v2h6v-2H7Z"/></svg><span>Orders</span>',
			['class' => 'nav-link disabled', 'aria-label' => 'Orders', 'aria-disabled' => 'true'],
		) ?>

		<p class="nav-label management-label">Management</p>

		<?= Html::tag(
			'span',
			'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10ZM3 22a9 9 0 0 1 18 0H3Z"/></svg><span>Staff</span>',
			['class' => 'nav-link disabled', 'aria-label' => 'Staff', 'aria-disabled' => 'true'],
		) ?>

		<?= Html::tag(
			'span',
			'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.3 2h3.4l.5 2.2c.5.2 1 .5 1.4.8l2.1-.7 1.7 2.9-1.6 1.5c.1.5.1 1.1 0 1.6l1.6 1.5-1.7 2.9-2.1-.7c-.4.3-.9.6-1.4.8l-.5 2.2h-3.4l-.5-2.2c-.5-.2-1-.5-1.4-.8l-2.1.7-1.7-2.9 1.6-1.5a8 8 0 0 1 0-1.6L4.6 7.2l1.7-2.9 2.1.7c.4-.3.9-.6 1.4-.8L10.3 2Zm1.7 5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5Z"/></svg><span>Settings</span>',
			['class' => 'nav-link disabled', 'aria-label' => 'Settings', 'aria-disabled' => 'true'],
		) ?>
	</nav>

	<div class="sidebar-footer">
		<div class="venue-mark">CL</div>
		<div>
			<strong>Cinelis Lisboa</strong>
			<span>Venue 01</span>
		</div>
	</div>
</aside>