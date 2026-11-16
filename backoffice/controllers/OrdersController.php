<?php

declare(strict_types=1);

namespace backoffice\controllers;

use common\models\Order;
use common\rbac\RbacInstaller;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

final class OrdersController extends Controller
{
	public function behaviors(): array
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow' => true,
						'roles' => [RbacInstaller::PERMISSION_ORDERS_VIEW],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'index' => ['GET'],
					'view' => ['GET'],
				],
			],
		];
	}

	public function actionIndex(): string
	{
		$q = trim((string) Yii::$app->request->get('q', ''));
		$query = Order::find()
			->alias('order')
			->joinWith(['user', 'tickets.session.movie'])
			->distinct();

		if ($q !== '') {
			$query->andWhere([
				'or',
				['like', new Expression('CAST("order"."id" AS TEXT)'), $q],
				['like', 'user.username', $q],
				['like', 'user.email', $q],
				['like', 'movies.title', $q],
			]);
		}

		return $this->render('index', [
			'provider' => new ActiveDataProvider([
				'query' => $query->orderBy(['order.created_at' => SORT_DESC]),
				'pagination' => [
					'pageSize' => 8,
					'pageSizeLimit' => false,
				],
			]),
			'q' => $q,
		]);
	}

	public function actionView(string $id): string
	{
		return $this->render('view', [
			'order' => $this->findOrder($id),
		]);
	}

	private function findOrder(string $id): Order
	{
		if (!$this->isUuid($id)) {
			throw new NotFoundHttpException('Encomenda não encontrada.');
		}

		$model = Order::find()
			->with(['user', 'tickets.session.movie', 'tickets.session.room', 'tickets.seat'])
			->where(['id' => $id])
			->one();

		if (!$model instanceof Order) {
			throw new NotFoundHttpException('Encomenda não encontrada.');
		}

		return $model;
	}

	private function isUuid(string $value): bool
	{
		return preg_match(
			'/\A[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\z/i',
			$value,
		) === 1;
	}
}
