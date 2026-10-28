<?php

namespace common\widgets;

use yii\helpers\Html;

final class Badge extends PrimitiveWidget
{
	public string $label = '';
	public string $variant = 'default';
	public bool $encodeLabel = true;
	public array $options = [];

	public function run(): string
	{
		$label = $this->encodeLabel ? Html::encode($this->label) : $this->label;

		return Html::tag(
			'span',
			$label,
			$this->withClass($this->options, 'ui-badge ui-badge--'.$this->variant),
		);
	}
}
