<?php

namespace common\assets;

use yii\web\AssetBundle;
use yii\web\View;

final class UiAsset extends AssetBundle
{
	public $sourcePath = '@common/assets';

	public $publishOptions = [
		'forceCopy' => YII_ENV_DEV,
		'only' => [
			'css/layouts/fonts.css',
			'fonts/*',
			'js/theme.js',
			'js/ui.js',
		],
	];

	public $css = [
		'css/layouts/fonts.css',
	];

	public $js = [
		['js/theme.js', 'position' => View::POS_HEAD, 'defer' => false],
		['js/ui.js', 'appendTimestamp' => true],
	];

	public $jsOptions = [
		'defer' => true,
	];
}
