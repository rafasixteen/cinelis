<?php

use common\widgets\Icon;
use yii\helpers\Html;
use yii\widgets\Menu;

$navLabel = static fn(string $icon, string $label): string =>
	Icon::widget([
		'name' => $icon,
		'options' => ['aria-hidden' => 'true'],
	])
	. '<span>'
	. $label
	. '</span>';
$disabledTemplate = '<span class="nav-link disabled" aria-disabled="true">{label}</span>';
$menuOptions = [
	'options' => ['class' => 'sidebar-menu'],
	'itemOptions' => ['class' => 'nav-item'],
	'linkTemplate' => '<a class="nav-link" href="{url}">{label}</a>',
	'encodeLabels' => false,
];

$workspaceItems = [
	[
		'label' => $navLabel('layout-dashboard', 'Overview'),
		'url' => ['/home/index'],
	],
	[
		'label' => $navLabel('clapperboard', 'Movies'),
		'template' => $disabledTemplate,
	],
	[
		'label' => $navLabel('calendar-days', 'Sessions'),
		'template' => $disabledTemplate,
	],
	[
		'label' => $navLabel('armchair', 'Rooms &amp; seats'),
		'template' => $disabledTemplate,
	],
	[
		'label' => $navLabel('receipt-text', 'Orders'),
		'template' => $disabledTemplate,
	],
];

$managementItems = [
	[
		'label' => $navLabel('user-round', 'Staff'),
		'template' => $disabledTemplate,
	],
	[
		'label' => $navLabel('settings', 'Settings'),
		'template' => $disabledTemplate,
	],
];
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

		<?= Menu::widget([
			'items' => $workspaceItems,
			...$menuOptions,
		]) ?>

		<p class="nav-label management-label">Management</p>

		<?= Menu::widget([
			'items' => $managementItems,
			...$menuOptions,
		]) ?>
	</nav>

	<div class="sidebar-footer">
		<div class="venue-mark">CL</div>
		<div>
			<strong>Cinelis Lisboa</strong>
			<span>Venue 01</span>
		</div>
	</div>
</aside>
