<?php

namespace frontoffice\controllers;

final class SiteController extends PreviewController
{
	public function actions(): array
	{
		return [
			'error' => [
				'class' => 'yii\\web\\ErrorAction',
			],
		];
	}

	public function actionContact(): string
	{
		return $this->render(
			'information',
			[
				'page' => 'contact',
			],
		);
	}

	public function actionHelp(): string
	{
		return $this->render(
			'information',
			[
				'page' => 'help',
			],
		);
	}

	public function actionPrivacy(): string
	{
		return $this->render(
			'information',
			[
				'page' => 'privacy',
			],
		);
	}
}
