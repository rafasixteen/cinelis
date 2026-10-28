<?php

namespace common\widgets;

use yii\helpers\Html;

final class Card extends PrimitiveWidget
{
	public string $title = '';
	public string $subtitle = '';
	public string $tag = 'section';
	public array $options = [];

	public function init(): void
	{
		parent::init();

		echo Html::beginTag($this->tag, $this->withClass($this->options, 'ui-card'));

		if ($this->title !== '' || $this->subtitle !== '') {
			echo Html::beginTag('header', ['class' => 'ui-card__header']);

			if ($this->title !== '') {
				echo Html::tag(
					'h2',
					Html::encode($this->title),
					[
						'class' => 'ui-card__title',
					],
				);
			}

			if ($this->subtitle !== '') {
				echo Html::tag(
					'p',
					Html::encode($this->subtitle),
					[
						'class' => 'ui-card__subtitle',
					],
				);
			}

			echo Html::endTag('header');
		}
	}

	public function run(): string
	{
		return Html::endTag($this->tag);
	}
}
