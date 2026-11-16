<?php

declare(strict_types=1);

namespace backoffice\controllers;

use common\models\Session;
use common\models\Ticket;
use common\rbac\RbacInstaller;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

final class TicketsController extends Controller
{
	public function behaviors(): array
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow' => true,
						'roles' => [RbacInstaller::PERMISSION_TICKETS_VIEW],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => ['index' => ['GET']],
			],
		];
	}

	public function actionIndex(?string $session = null): string
	{
		$sessionId = $session ?? (string) Yii::$app->request->get('session', '');
		$model = $sessionId !== '' ? $this->findSession($sessionId) : Session::find()->orderBy(['starts_at' => SORT_DESC])->one();
		$query = Ticket::find()->alias('ticket')->joinWith(['order.user', 'seat']);
		$q = trim((string) Yii::$app->request->get('q', ''));
		$used = (string) Yii::$app->request->get('used', '');

		if ($model instanceof Session) {
			$query->andWhere(['ticket.session_id' => $model->id]);
		} else {
			$query->andWhere('1=0');
		}

		if ($q !== '') {
			$query->andWhere([
				'or',
				['like', 'ticket.code', $q],
				['like', 'user.username', $q],
				['like', 'seats.row', $q],
				['like', new Expression('CONCAT("seats"."row", "seats"."number")'), $q],
			]);
		}

		if ($used === '1') {
			$query->andWhere(['not', ['ticket.used_at' => null]]);
		} elseif ($used === '0') {
			$query->andWhere(['ticket.used_at' => null]);
		}

		return $this->render('index', [
			'session' => $model,
			'sessions' => Session::find()
				->with(['movie', 'room'])
				->orderBy(['starts_at' => SORT_DESC])
				->all(),
			'provider' => new ActiveDataProvider([
				'query' => $query->orderBy(['ticket.created_at' => SORT_DESC]),
				'pagination' => [
					'pageSize' => 8,
					'pageSizeLimit' => false,
				],
			]),
			'q' => $q,
			'used' => $used,
		]);
	}

	private function findSession(string $id): Session
	{
		if (!$this->isUuid($id)) {
			throw new NotFoundHttpException('Sessão não encontrada.');
		}

		$model = Session::findOne($id);

		if (!$model instanceof Session) {
			throw new NotFoundHttpException('Sessão não encontrada.');
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
