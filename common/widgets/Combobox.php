<?php

namespace common\widgets;

use yii\helpers\Html;

final class Combobox extends Select
{
	public function init(): void
	{
		$this->options = $this->withClass($this->options, 'ui-combobox');
		$this->options['role'] ??= 'combobox';
		$this->options['aria-autocomplete'] ??= 'none';
		$this->options['data-ui-combobox'] ??= 'true';

		parent::init();
	}

	public function run(): string
	{
		if (empty($this->options['multiple'])) {
			return parent::run();
		}

		$id = (string) ($this->options['id'] ?? Html::getInputIdByName($this->name));
		$values = array_map('strval', (array) $this->value);
		$customClass = str_replace('ui-combobox', '', (string) ($this->options['class'] ?? ''));
		$inputClass = trim('ui-combobox__input '.$customClass);
		$inputOptions = [
			'id' => $id,
			'class' => $inputClass,
			'type' => 'search',
			'autocomplete' => 'off',
			'role' => 'combobox',
			'aria-autocomplete' => 'list',
			'aria-expanded' => 'false',
			'aria-controls' => $id.'-list',
			'data-combobox-input' => 'true',
			'placeholder' => $this->options['placeholder'] ?? 'Pesquisar ou selecionar géneros…',
		];

		if (isset($this->options['aria-label'])) {
			$inputOptions['aria-label'] = $this->options['aria-label'];
		}

		$selected = [];
		$options = [];

		foreach ($this->items as $value => $label) {
			$value = (string) $value;
			$label = (string) $label;
			$isSelected = in_array($value, $values, true);

			if ($isSelected) {
				$selected[] = Html::tag('span', Html::encode($label), [
					'class' => 'ui-combobox__tag',
					'data-combobox-tag' => $value,
				]);
			}

			$checkbox = Html::checkbox($this->name.'[]', $isSelected, [
				'value' => $value,
				'class' => 'ui-combobox__checkbox',
				'data-combobox-checkbox' => 'true',
				'tabindex' => '-1',
			]);
			$options[] = Html::tag(
				'label',
				$checkbox.Html::tag('span', Html::encode($label)),
				[
					'class' => 'ui-combobox__option',
					'data-combobox-option' => 'true',
					'data-label' => mb_strtolower($label),
					'role' => 'option',
					'aria-selected' => $isSelected ? 'true' : 'false',
				],
			);
		}

		$selectedContent = implode('', $selected);
		$selectedContent = Html::tag('div', $selectedContent, [
			'class' => 'ui-combobox__selected',
			'data-combobox-selected' => 'true',
		]);

		$panel = Html::tag(
			'div',
			implode('', $options).Html::tag(
				'p',
				'Nenhum género encontrado.',
				[
					'class' => 'ui-combobox__empty',
					'data-combobox-empty' => 'true',
					'hidden' => true,
				],
			),
			[
				'id' => $id.'-list',
				'class' => 'ui-combobox__panel',
				'data-combobox-panel' => 'true',
				'hidden' => true,
			],
		);

		$control = Html::tag('div', $selectedContent.Html::input('search', $id.'-search', '', $inputOptions), [
			'class' => 'ui-combobox__control',
			'data-combobox-control' => 'true',
		]);
		$container = $control.$panel
			.Html::hiddenInput($this->name.'[]', '', ['data-combobox-empty-value' => 'true']);

		return Html::tag('div', $container, [
			'class' => 'ui-combobox ui-combobox--multiple',
			'data-ui-combobox' => 'true',
		]);
	}
}
