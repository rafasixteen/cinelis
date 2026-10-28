<?php

namespace common\assets;

use yii\web\AssetBundle;

final class BrandAsset extends AssetBundle
{
	public $sourcePath = '@root/assets/images';
	public $publishOptions = [
		'only' => ['*.svg'],
	];
}
