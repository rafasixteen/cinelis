<?php

namespace backoffice\controllers;

use backoffice\data\Operations;

final class TicketsController extends MockController
{
	public function actionIndex(int $session = 3): string
	{
		$record = Operations::find('sessions', $session);
		$q = $this->query('q');
		$used = $this->query('used');
		$tickets = array_filter(
			Operations::tickets($record),
			static fn (array $ticket): bool => (
				($q === '' || mb_stripos($ticket['customer'].' '.$ticket['code'].' '.$ticket['seat'], $q) !== false)
				&& ($used === '' || (string) (int) $ticket['used'] === $used)
			),
		);

		return $this->render(
			'index',
			[
				'session' => $record,
				'provider' => $this->provider($tickets, 5),
				'q' => $q,
				'used' => $used,
			],
		);
	}
}
