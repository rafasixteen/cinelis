<?php

namespace common\assets;

use yii\web\AssetBundle;
use yii\web\View;

final class UiAsset extends AssetBundle
{
	public $sourcePath = '@common/assets';
	public $publishOptions = [
		'only' => [
			'css/layouts/fonts.css',
			'fonts/*',
			'js/ui.js',
		],
	];
	public $css = [
		'css/layouts/fonts.css',
	];
	public $js = [
		'js/ui.js',
	];
	public $jsOptions = [
		'defer' => true,
	];

	public function registerAssetFiles($view): void
	{
		parent::registerAssetFiles($view);
		$view->registerJs(<<<'JS'
(() => {
	try {
		const theme = localStorage.getItem('cinelis-theme');
		if (theme === 'light' || theme === 'dark') {
			document.documentElement.dataset.theme = theme;
		} else {
			document.documentElement.removeAttribute('data-theme');
		}
	} catch (_) {
		document.documentElement.removeAttribute('data-theme');
	}
})();
JS, View::POS_HEAD, 'cinelis-theme-bootstrap');
	}
}
