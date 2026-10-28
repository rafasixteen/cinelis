<?php

declare(strict_types=1);

define('YII_ENABLE_ERROR_HANDLER', false);

require dirname(__DIR__).'/vendor/autoload.php';
require dirname(__DIR__).'/vendor/yiisoft/yii2/Yii.php';

use common\assets\UiAsset;
use common\widgets\Logo;
use common\widgets\Toast;

$root = dirname(__DIR__);
$assetPath = $root.'/tmp/ui-smoke-assets-'.getmypid();

if (!is_dir($assetPath)) {
	mkdir($assetPath, 0775, true);
}
Yii::setAlias('@root', $root);
Yii::setAlias('@common', $root.'/common');
$app = new yii\web\Application([
	'id' => 'ui-smoke',
	'basePath' => $root,
	'runtimePath' => $root.'/tmp/ui-smoke-runtime',
	'components' => [
		'request' => [
			'cookieValidationKey' => 'test-only',
			'scriptFile' => __FILE__,
			'scriptUrl' => '/index.php',
		],
		'assetManager' => [
			'basePath' => $assetPath,
			'baseUrl' => '/assets',
		],
	],
]);
$check = static function (bool $condition, string $message): void {
	if (!$condition) {
		throw new RuntimeException($message);
	}
};
$logo = Logo::widget();
$check(substr_count($logo, '<img') === 2, 'Automatic logo must include both theme variants.');
$check(str_contains($logo, 'cinelis-logo-light.svg') && str_contains($logo, 'cinelis-logo-dark.svg'), 'Logo must publish official SVG assets.');
$dark = Logo::widget(
	[
		'variant' => 'dark',
	],
);
$check(substr_count($dark, '<img') === 1 && str_contains($dark, 'cinelis-logo-dark.svg'), 'Permanent dark sidebar must use only the dark logo.');

try {
	Logo::widget(
		[
			'variant' => '../invalid',
		],
	);

	throw new RuntimeException('Invalid logo variant should be rejected.');
} catch (yii\base\InvalidConfigException) {
}
$toast = Toast::widget();
$check(str_contains($toast, 'role="status"') && str_contains($toast, 'aria-live="polite"'), 'Toast must announce without moving focus.');
$check(str_contains($toast, 'hidden') && str_contains($toast, 'data-toast-message'), 'Toast must start hidden with an update target.');
$check(str_contains($toast, 'data-toast-close') && str_contains($toast, 'aria-label='), 'Toast must have an accessible dismiss control.');
$asset = UiAsset::register($app->view);
$check(is_file($asset->basePath.'/js/ui.js'), 'Shared JS must be published.');
$check(is_file($asset->basePath.'/fonts/inter-latin-400-normal.woff2'), 'Inter font must be locally available.');
$check(!is_file($asset->basePath.'/UiAsset.php'), 'Only public UI resources may be published.');
yii\helpers\FileHelper::removeDirectory($assetPath);
echo "UI smoke test passed: logo variants, official assets, accessible toast and shared asset publication.\n";
