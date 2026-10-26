<?php

namespace common\widgets;

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
}
