<?php

namespace frontoffice\controllers;

use frontoffice\data\Catalog;

final class CheckoutController extends PreviewController
{
	public function actionIndex(string $session = '', string $seats = 'A2,A3'): string
	{
		return $this->render(
			'index',
			[
				'session' => Catalog::session($session ?: Catalog::defaultSession()),
				'seats' => Catalog::seats($seats),
			],
		);
	}

	public function actionConfirmation(): string
	{
		return $this->render(
			'/ticket/view',
			[
				'ticket' => Catalog::ticket('CIN-DEMO-1'),
				'confirmation' => true,
			],
		);
	}
}
