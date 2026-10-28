<?php

namespace common\widgets;

use yii\helpers\Html;

final class Toast extends PrimitiveWidget
{
	public function run(): string
	{
		$message = Html::tag(
			'div',
			Icon::widget(
				[
					'name' => 'info',
				],
			)
			.Html::tag(
				'span',
				'',
				[
					'data-toast-message' => true,
				],
			),
			[
				'role' => 'status',
				'aria-live' => 'polite',
				'aria-atomic' => 'true',
				'class' => 'ui-toast__message',
			],
		);
		$close = Html::button(
			Icon::widget(
				[
					'name' => 'x',
				],
			),
			[
				'type' => 'button',
				'class' => 'ui-toast__close',
				'data-toast-close' => true,
				'aria-label' => 'Fechar notificação',
			],
		);

		return Html::tag(
			'aside',
			$message.$close,
			[
				'class' => 'ui-toast',
				'data-ui-toast' => true,
				'hidden' => true,
			],
		);
	}
}
