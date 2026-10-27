<?php

namespace common\widgets;

use yii\base\Widget;

abstract class PrimitiveWidget extends Widget
{
	protected function withClass(array $options, string $class): array
	{
		$options['class'] = trim($class . ' ' . ($options['class'] ?? ''));

		return $options;
	}
}
