<?php

namespace common\widgets;

use yii\base\InvalidConfigException;
use yii\helpers\Html;

final class Icon extends PrimitiveWidget
{
	public string $name = '';
	public string $label = '';
	public array $options = [];

	public function run(): string
	{
		if (!preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $this->name)) {
			throw new InvalidConfigException(
				'Icon name must contain lowercase letters, numbers, and hyphens only.',
			);
		}

		$iconPath = dirname(__DIR__, 2)
			.'/vendor/npm-asset/lucide-static/icons/'
			.$this->name
			.'.svg';
		$svg = file_get_contents($iconPath);

		if ($svg === false) {
			throw new InvalidConfigException(
				'Lucide icon "'.$this->name.'" could not be found.',
			);
		}

		$options = $this->withClass($this->options, 'ui-icon');

		if ($this->label !== '') {
			$options['role'] ??= 'img';
			$options['aria-label'] ??= $this->label;
		} elseif (
			!isset($options['aria-label'])
			&& !isset($options['aria-labelledby'])
		) {
			$options['aria-hidden'] ??= 'true';
		}

		return preg_replace_callback(
			'/<svg\b([^>]*)>/s',
			static function (array $matches) use ($options): string {
				$attributes = preg_replace(
					'/\s+class="[^"]*"/',
					'',
					$matches[1],
					1,
				);

				return '<svg'
					.$attributes
					.Html::renderTagAttributes($options)
					.'>';
			},
			$svg,
			1,
		) ?? $svg;
	}
}
