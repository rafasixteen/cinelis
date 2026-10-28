<?php

namespace backoffice\assets;

use yii\web\AssetBundle;

final class BehaviorAsset extends AssetBundle
{
	public $sourcePath = '@backoffice/assets/js';
	public $js = [
		'operations.js',
	];
}
