<?php

namespace frontoffice\assets;

use yii\web\AssetBundle;

final class InteractionAsset extends AssetBundle
{
	public $sourcePath = __DIR__.'/js';
	public $js = [
		'frontoffice.js',
	];
}
