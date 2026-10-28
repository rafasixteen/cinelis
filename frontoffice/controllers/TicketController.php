<?php

namespace frontoffice\controllers;

use frontoffice\data\Catalog;

final class TicketController extends PreviewController
{
	public function actionIndex(string $period = 'upcoming'): string
	{
		$period = $period === 'past' ? 'past' : 'upcoming';

		return $this->render(
			'index',
			[
				'tickets' => Catalog::tickets($period),
				'period' => $period,
			],
		);
	}

	public function actionView(string $id, string $period = 'upcoming'): string
	{
		$ticket = Catalog::ticket($id);

		if ($period === 'past') {
			foreach (Catalog::tickets('past') as $pastTicket) {
				if ($pastTicket['id'] === $id) {
					$ticket = $pastTicket;

					break;
				}
			}
		}

		return $this->render(
			'view',
			[
				'ticket' => $ticket,
				'confirmation' => false,
			],
		);
	}
}
