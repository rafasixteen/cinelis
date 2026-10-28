<?php

namespace backoffice\controllers;

use backoffice\data\Operations;

final class UsersController extends MockController
{
	public function actionIndex(): string
	{
		$q = $this->query('q');
		$records = array_filter(
			Operations::users(),
			static fn (array $user): bool => (
				$q === '' || mb_stripos($user['name'].' '.$user['email'], $q) !== false
			),
		);

		return $this->render(
			'index',
			[
				'provider' => $this->provider($records),
				'q' => $q,
			],
		);
	}

	public function actionCreate(): string
	{
		return $this->render(
			'form',
			[
				'record' => [
					'name' => '',
					'email' => '',
					'role' => 'Funcionário',
					'status' => 'Ativo',
				],
				'isNew' => true,
			],
		);
	}

	public function actionEdit(int $id): string
	{
		return $this->render(
			'form',
			[
				'record' => Operations::find('users', $id),
				'isNew' => false,
			],
		);
	}
}
