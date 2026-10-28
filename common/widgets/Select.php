<?php

namespace common\widgets;

use yii\helpers\Html;

class Select extends PrimitiveWidget
{
	public string $name = '';
	public array $items = [];
	public mixed $value = null;
	public string $label = '';
	public array $options = [];
	public array $containerOptions = [];
	public array $labelOptions = [];

	public function run(): string
	{
		$selectOptions = $this->withClass($this->options, 'ui-select');
		$selectOptions['id'] ??= Html::getInputIdByName($this->name);
		$select = Html::dropDownList(
			$this->name,
			$this->value,
			$this->items,
			$selectOptions,
		);

		if ($this->label === '') {
			return $select;
		}

		$labelOptions = $this->labelOptions;
		$labelOptions['for'] ??= $selectOptions['id'];
		$label = Html::label(
			Html::encode($this->label),
			$labelOptions['for'],
			$labelOptions,
		);

		return Html::tag(
			'div',
			$label.$select,
			$this->withClass($this->containerOptions, 'ui-field'),
		);
	}
}
