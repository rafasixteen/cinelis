<?php

namespace backoffice\controllers;

use backoffice\data\Operations;

final class OrdersController extends MockController
{
	public function actionIndex(): string
	{
		$q = $this->query('q');
		$status = $this->query('status');
		$records = array_filter(
			Operations::orders(),
			static fn (array $order): bool => (
				($q === '' || mb_stripos($order['customer'].' '.$order['id'].' '.$order['movie'], $q) !== false)
				&& ($status === '' || $status === $order['status'])
			),
		);

		return $this->render(
			'index',
			[
				'provider' => $this->provider($records),
				'q' => $q,
				'status' => $status,
			],
		);
	}

	public function actionView(int $id): string
	{
		return $this->render(
			'view',
			[
				'order' => Operations::find('orders', $id),
			],
		);
	}
}
