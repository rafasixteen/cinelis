<?php

declare(strict_types=1);

namespace backoffice\controllers;

use common\models\Movie;
use common\models\Room;
use common\models\SeatHold;
use common\models\Session;
use common\models\Ticket;
use common\rbac\RbacDefinitions;
use DateTimeImmutable;
use DateTimeZone;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class SessionsController extends Controller
{
	private const TIMEZONE = 'Europe/Lisbon';

	public function behaviors(): array
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow' => true,
						'roles' => [RbacDefinitions::PERMISSION_SESSIONS_MANAGE],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'create' => ['GET', 'POST'],
					'update' => ['GET', 'POST'],
					'cancel' => ['POST'],
				],
			],
		];
	}

	public function actionIndex(): string
	{
		$date = trim((string) Yii::$app->request->get('date', ''));
		$roomId = trim((string) Yii::$app->request->get('room', ''));
		$query = Session::find()->with(['movie', 'room']);

		if ($date !== '') {
			$day = DateTimeImmutable::createFromFormat('!Y-m-d', $date, new DateTimeZone(self::TIMEZONE));
			$errors = DateTimeImmutable::getLastErrors();

			if ($day !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
				$query
					->andWhere(['>=', 'starts_at', $day->format('Y-m-d H:i:s')])
					->andWhere(['<', 'starts_at', $day->modify('+1 day')->format('Y-m-d H:i:s')]);
			} else {
				$date = '';
			}
		}

		if ($roomId !== '') {
			if ($this->isUuid($roomId)) {
				$query->andWhere(['room_id' => $roomId]);
			} else {
				$query->andWhere('1=0');
			}
		}

		return $this->render('index', [
			'provider' => new ActiveDataProvider([
				'query' => $query->orderBy(['starts_at' => SORT_ASC]),
				'pagination' => [
					'pageSize' => 8,
					'pageSizeLimit' => false,
				],
			]),
			'date' => $date,
			'room' => $roomId,
			'rooms' => Room::find()->orderBy(['name' => SORT_ASC])->all(),
		]);
	}

	public function actionCreate(): string|Response
	{
		$model = new Session();

		if (Yii::$app->request->isPost) {
			$this->loadPostedSession($model);
			$this->validateUuidReferences($model);

			if ($model->validate() && $model->save()) {
				return $this->redirect(['update', 'id' => $model->id]);
			}
		}

		return $this->render('form', [
			'model' => $model,
			'movies' => $this->movies(),
			'rooms' => $this->rooms(),
			'isNew' => true,
		]);
	}

	public function actionUpdate(string $id): string|Response
	{
		$model = $this->findSession($id);
		$originalMovieId = (string) $model->movie_id;
		$originalRoomId = (string) $model->room_id;
		$originalStartsAt = (string) $model->starts_at;

		if (Yii::$app->request->isPost) {
			$this->loadPostedSession($model);
			$this->validateUuidReferences($model);
			$scheduleChanged = (
				(string) $model->movie_id !== $originalMovieId
				|| (string) $model->room_id !== $originalRoomId
				|| (string) $model->starts_at !== $originalStartsAt
			);

			if ($scheduleChanged && !$model->hasErrors()) {
				$this->protectBookedSession($model, $originalMovieId, $originalRoomId, $originalStartsAt);
			}

			if (!$model->hasErrors() && $model->save()) {
				return $this->redirect(['update', 'id' => $model->id]);
			}
		}

		return $this->render('form', [
			'model' => $model,
			'movies' => $this->movies(),
			'rooms' => $this->rooms(),
			'isNew' => false,
		]);
	}

	public function actionCancel(string $id): Response
	{
		$model = $this->findSession($id);
		$model->status = Session::STATUS_CANCELLED;
		$model->save(false, ['status']);

		return $this->redirect(['index']);
	}

	private function loadPostedSession(Session $model): void
	{
		if ($model->load(Yii::$app->request->post())) {
			$model->starts_at = $this->normalizeDateTime((string) $model->starts_at);
		}
	}

	private function normalizeDateTime(string $value): string
	{
		$value = trim($value);
		foreach (['Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i:s', 'Y-m-d H:i'] as $format) {
			$date = DateTimeImmutable::createFromFormat($format, $value, new DateTimeZone(self::TIMEZONE));
			$errors = DateTimeImmutable::getLastErrors();

			if ($date !== false && ($errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
				return $date->format('Y-m-d H:i:s');
			}
		}

		return $value;
	}

	private function protectBookedSession(Session $model, string $movieId, string $roomId, string $startsAt): void
	{
		$hasTickets = Ticket::find()->where(['session_id' => $model->id])->exists();
		$hasActiveHolds = SeatHold::find()
			->where(['session_id' => $model->id])
			->andWhere(['>', 'expires_at', new \yii\db\Expression('CURRENT_TIMESTAMP')])
			->exists();

		if ($hasTickets || $hasActiveHolds) {
			$model->movie_id = $movieId;
			$model->room_id = $roomId;
			$model->starts_at = $startsAt;
			$model->addError(
				'starts_at',
				'Não é possível alterar o filme, a sala ou o horário de uma sessão com '
				.($hasTickets ? 'bilhetes existentes' : 'reservas ativas').'.',
			);
		}
	}

	/**
	 * @return list<Movie>
	 */
	private function movies(): array
	{
		return Movie::find()->orderBy(['title' => SORT_ASC])->all();
	}

	/**
	 * @return list<Room>
	 */
	private function rooms(): array
	{
		return Room::find()->with('seats')->orderBy(['name' => SORT_ASC])->all();
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

	private function validateUuidReferences(Session $model): void
	{
		foreach (['movie_id', 'room_id'] as $attribute) {
			if (!$this->isUuid((string) $model->{$attribute})) {
				$model->addError($attribute, 'Selecione uma entidade válida.');
			}
		}
	}

	private function isUuid(string $value): bool
	{
		return preg_match(
			'/\A[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\z/i',
			$value,
		) === 1;
	}
}
