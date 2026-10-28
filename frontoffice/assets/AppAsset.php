<?php

namespace frontoffice\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
	public $depends = [InteractionAsset::class];

	public $basePath = '@webroot';
	public $baseUrl = '@web';

	public $css = [
		'css/globals.css',
	];
}
