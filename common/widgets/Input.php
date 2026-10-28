<?php

namespace common\widgets;

use yii\helpers\Html;

class Input extends PrimitiveWidget
{
	public string $name = '';
	public mixed $value = null;
	public string $label = '';
	public string $type = 'text';
	public array $options = [];
	public array $containerOptions = [];
	public array $labelOptions = [];

	public function run(): string
	{
		$inputOptions = $this->withClass($this->options, 'ui-input');
		$inputOptions['id'] ??= Html::getInputIdByName($this->name);
		$input = Html::input(
			$this->type,
			$this->name,
			$this->value,
			$inputOptions,
		);

		if ($this->label === '') {
			return $input;
		}

		$labelOptions = $this->labelOptions;
		$labelOptions['for'] ??= $inputOptions['id'];
		$label = Html::label(
			Html::encode($this->label),
			$labelOptions['for'],
			$labelOptions,
		);

		return Html::tag(
			'div',
			$label.$input,
			$this->withClass($this->containerOptions, 'ui-field'),
		);
	}
}
