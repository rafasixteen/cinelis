<?php

namespace common\widgets;

use yii\base\InvalidConfigException;
use yii\helpers\Html;

final class Button extends PrimitiveWidget
{
	public string $label = '';
	public string $icon = '';
	public string $variant = 'primary';
	public string $tag = 'button';
	public string|array|null $url = null;
	public bool $encodeLabel = true;
	public array $options = [];

	public function run(): string
	{
		if (!in_array($this->tag, ['a', 'button'], true)) {
			throw new InvalidConfigException('Button tag must be either "a" or "button".');
		}

		$labelContent = $this->encodeLabel
			? Html::encode($this->label)
			: $this->label;
		$label = Html::tag(
			'span',
			$labelContent,
			[
				'class' => 'ui-button__label',
			],
		);

		if ($this->icon !== '') {
			$label = Icon::widget([
				'name' => $this->icon,
				'options' => ['aria-hidden' => 'true'],
			]).$label;
		}

		$options = $this->withClass($this->options, 'ui-button ui-button--'.$this->variant);

		return $this->tag === 'a'
			? Html::a(
				$label,
				$this->url ?? '#',
				$options,
			)
			: Html::button(
				$label,
				$options,
			);
	}
}
