<?php

namespace common\widgets;

use common\assets\BrandAsset;
use yii\base\InvalidConfigException;
use yii\helpers\Html;

final class Logo extends PrimitiveWidget
{
	public string $variant = 'auto';
	public array $options = [];

	public function run(): string
	{
		if (!in_array($this->variant, ['auto', 'light', 'dark'], true)) {
			throw new InvalidConfigException('Logo variant must be auto, light, or dark.');
		}

		$asset = BrandAsset::register($this->view);
		$variants = $this->variant === 'auto' ? ['light', 'dark'] : [$this->variant];
		$images = '';

		foreach ($variants as $variant) {
			$images .= Html::img(
				$asset->baseUrl.'/cinelis-logo-'.$variant.'.svg',
				[
					'alt' => 'CineLis',
					'width' => 296,
					'height' => 72,
					'class' => 'ui-logo__'.$variant,
				],
			);
		}

		return Html::tag(
			'span',
			$images,
			$this->withClass($this->options, 'ui-logo ui-logo--'.$this->variant),
		);
	}
}
