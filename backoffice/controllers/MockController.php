<?php

namespace backoffice\controllers;

use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

abstract class MockController extends Controller
{
	public function behaviors(): array
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'actions' => ['login'],
						'allow' => true,
						'roles' => ['?'],
					],
					[
						'actions' => ['logout'],
						'allow' => true,
						'roles' => ['@'],
					],
					[
						'allow' => true,
						'roles' => ['backoffice.access'],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'*' => ['GET', 'HEAD'],
					'login' => ['GET', 'POST'],
					'logout' => ['POST'],
					'create' => ['GET', 'POST'],
					'update' => ['GET', 'POST'],
					'delete' => ['POST'],
					'restore' => ['POST'],
				],
			],
		];
	}

	protected function query(string $name, string $default = ''): string
	{
		$value = Yii::$app->request->get($name, $default);

		return is_string($value) ? trim($value) : $default;
	}

	protected function provider(array $records, int $pageSize = 8): ArrayDataProvider
	{
		return new ArrayDataProvider(
			[
				'allModels' => array_values($records),
				'key' => 'id',
				'pagination' => [
					'pageSize' => $pageSize,
					'pageSizeLimit' => false,
				],
				'sort' => false,
			],
		);
	}
}
