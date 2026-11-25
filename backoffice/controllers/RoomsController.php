<?php

declare(strict_types=1);

namespace backoffice\controllers;

use common\models\Room;
use common\rbac\RbacDefinitions;
use common\services\RoomLayoutSynchronizer;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class RoomsController extends Controller
{
	public function behaviors(): array
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow' => true,
						'roles' => [RbacDefinitions::PERMISSION_ROOMS_MANAGE],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'create' => ['GET', 'POST'],
					'layout' => ['GET', 'POST'],
				],
			],
		];
	}

	public function actionIndex(): string
	{
		return $this->render('index', [
			'provider' => new ActiveDataProvider([
				'query' => Room::find()->with('seats')->orderBy(['name' => SORT_ASC]),
				'pagination' => [
					'pageSize' => 8,
					'pageSizeLimit' => false,
				],
			]),
		]);
	}

	public function actionCreate(): string|Response
	{
		$model = new Room();
		$layout = $this->defaultLayout();

		if (Yii::$app->request->isPost) {
			try {
				$layout = $this->postedLayout($layout);
			} catch (\DomainException $error) {
				$model->addError('layout', $error->getMessage());
			}

			if (!$model->hasErrors()
				&& $model->load(Yii::$app->request->post())
				&& $this->saveRoom($model, $layout)
			) {
				return $this->redirect(['layout', 'id' => $model->id]);
			}
		}

		return $this->render('form', [
			'model' => $model,
			'layout' => $layout,
		]);
	}

	public function actionLayout(string $id): string|Response
	{
		$room = $this->findRoom($id);
		$layout = $this->roomLayout($room);

		if (Yii::$app->request->isPost) {
			try {
				$layout = $this->postedLayout($layout);
				RoomLayoutSynchronizer::sync($room, $layout);

				return $this->redirect(['layout', 'id' => $room->id]);
			} catch (\DomainException $error) {
				$room->addError('layout', $error->getMessage());
			}
		}

		return $this->render('layout', [
			'room' => $room,
			'layout' => $layout,
		]);
	}

	private function saveRoom(Room $model, array $layout): bool
	{
		try {
			return Yii::$app->db->transaction(static function () use ($model, $layout): bool {
				if (!$model->save()) {
					return false;
				}

				RoomLayoutSynchronizer::sync($model, $layout);

				return true;
			});
		} catch (\DomainException $error) {
			$model->addError('layout', $error->getMessage());

			return false;
		}
	}

	/**
	 * Accepts either the JSON layout payload or the structured editor controls.
	 *
	 * @return array<string, mixed>
	 */
	private function postedLayout(array $fallback): array
	{
		$post = Yii::$app->request->post();

		if (array_key_exists('layout', $post)) {
			$raw = $post['layout'];

			if (!is_string($raw) || trim($raw) === '') {
				throw new \DomainException('O layout enviado não é válido.');
			}

			try {
				$decoded = Json::decode($raw);

				if (is_array($decoded)) {
					return $decoded;
				}
			} catch (\Throwable $error) {
				throw new \DomainException('O layout enviado não é JSON válido.', 0, $error);
			}

			throw new \DomainException('O layout enviado tem de ser um objeto JSON.');
		}

		$rows = max(1, (int) Yii::$app->request->post('grid_rows', $fallback['grid']['rows'] ?? 6));
		$columns = max(1, (int) Yii::$app->request->post('grid_columns', $fallback['grid']['columns'] ?? 10));
		$cellTypes = Yii::$app->request->post('cell_types', []);
		$cells = [];

		if (is_array($cellTypes)) {
			foreach ($cellTypes as $coordinate => $type) {
				$parts = explode(':', (string) $coordinate, 2);

				if (count($parts) !== 2 || !ctype_digit($parts[0]) || !ctype_digit($parts[1])) {
					continue;
				}
				$type = (string) $type;

				if (!in_array($type, ['seat', 'aisle'], true)) {
					continue;
				}
				$cells[] = [
					'row' => (int) $parts[0],
					'column' => (int) $parts[1],
					'type' => $type,
				];
			}
		}

		$layout = $fallback;
		$layout['grid'] = ['rows' => $rows, 'columns' => $columns];
		$layout['screen']['label'] = trim((string) Yii::$app->request->post(
			'screen_label',
			$layout['screen']['label'] ?? 'Ecrã',
		)) ?: 'Ecrã';
		$layout['entrances'][0]['label'] = trim((string) Yii::$app->request->post(
			'entrance_label',
			$layout['entrances'][0]['label'] ?? 'Entrada',
		)) ?: 'Entrada';
		$layout['exits'][0]['label'] = trim((string) Yii::$app->request->post(
			'exit_label',
			$layout['exits'][0]['label'] ?? 'Saída',
		)) ?: 'Saída';
		$layout['cells'] = $cells;

		return $layout;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function roomLayout(Room $room): array
	{
		if (is_array($room->layout)) {
			return $room->layout;
		}

		if (is_string($room->layout) && trim($room->layout) !== '') {
			$decoded = Json::decode($room->layout);

			if (is_array($decoded)) {
				return $decoded;
			}
		}

		return $this->defaultLayout();
	}

	/**
	 * @return array<string, mixed>
	 */
	private function defaultLayout(): array
	{
		$rows = 6;
		$columns = 10;
		$cells = [];
		for ($row = 1; $row <= $rows; $row++) {
			for ($column = 1; $column <= $columns; $column++) {
				$cells[] = ['row' => $row, 'column' => $column, 'type' => 'seat'];
			}
		}

		return [
			'version' => 1,
			'grid' => ['rows' => $rows, 'columns' => $columns],
			'screen' => ['label' => 'Ecrã', 'row' => 0, 'column' => 0, 'rowSpan' => 1, 'columnSpan' => $columns],
			'entrances' => [['id' => 'entrance-main', 'label' => 'Entrada', 'row' => $rows + 1, 'column' => 1]],
			'exits' => [['id' => 'exit-main', 'label' => 'Saída', 'row' => $rows + 1, 'column' => $columns]],
			'cells' => $cells,
		];
	}

	private function findRoom(string $id): Room
	{
		if (!$this->isUuid($id)) {
			throw new NotFoundHttpException('Sala não encontrada.');
		}

		$model = Room::findOne($id);

		if (!$model instanceof Room) {
			throw new NotFoundHttpException('Sala não encontrada.');
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
