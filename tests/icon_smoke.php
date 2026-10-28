<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/vendor/yiisoft/yii2/Yii.php';

use common\widgets\Button;
use common\widgets\Icon;

$composerIconPath = dirname(__DIR__)
	.'/vendor/npm-asset/lucide-static/icons/calendar-days.svg';

if (!is_file($composerIconPath)) {
	throw new RuntimeException('Lucide icons must be installed through Composer.');
}

$icon = Icon::widget([
	'name' => 'calendar-days',
	'options' => ['class' => 'metric-icon__svg'],
]);

if (!str_contains($icon, '<svg')) {
	throw new RuntimeException('The icon widget must render an SVG element.');
}

if (!str_contains($icon, 'metric-icon__svg')) {
	throw new RuntimeException('The icon widget must preserve custom classes.');
}

if (!str_contains($icon, 'aria-hidden="true"')) {
	throw new RuntimeException('Decorative icons must be hidden from assistive technology.');
}

$button = Button::widget([
	'label' => 'Add session',
	'icon' => 'plus',
]);

if (!str_contains($button, 'Add session')) {
	throw new RuntimeException('Buttons must preserve their labels when using an icon.');
}

if (!str_contains($button, 'ui-icon')) {
	throw new RuntimeException('Buttons must render their icons through the icon primitive.');
}

if (!str_contains($button, 'ui-button__label')) {
	throw new RuntimeException('Buttons must wrap their labels for reliable alignment.');
}

echo "Icon smoke test passed.\n";
