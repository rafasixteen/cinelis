<?php

declare(strict_types=1);

namespace backoffice\controllers;

use common\models\Genre;
use common\models\Movie;
use common\models\MovieGenre;
use common\rbac\RbacDefinitions;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

final class MoviesController extends Controller
{
	public function behaviors(): array
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow' => true,
						'roles' => [RbacDefinitions::PERMISSION_MOVIES_MANAGE],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::class,
				'actions' => [
					'create' => ['GET', 'POST'],
					'update' => ['GET', 'POST'],
				],
			],
		];
	}

	public function actionIndex(): string
	{
		$query = Movie::find();
		$search = trim((string) Yii::$app->request->get('q', ''));

		if ($search !== '') {
			$query->andWhere(['like', 'title', $search]);
		}

		return $this->render('index', [
			'provider' => new ActiveDataProvider([
				'query' => $query->orderBy([
					'release_date' => SORT_DESC,
					'title' => SORT_ASC,
				]),
				'pagination' => [
					'pageSize' => 8,
					'pageSizeLimit' => false,
				],
			]),
			'q' => $search,
		]);
	}

	public function actionCreate(): string|Response
	{
		$model = new Movie();
		$genreIds = [];

		if (Yii::$app->request->isPost) {
			$genreIds = $this->postedGenreIds();

			if ($model->load(Yii::$app->request->post()) && $this->saveMovie($model, $genreIds)) {
				return $this->redirect(['update', 'id' => $model->id]);
			}
		}

		return $this->render('form', [
			'model' => $model,
			'genres' => $this->genres(),
			'genreIds' => $genreIds,
			'isNew' => true,
		]);
	}

	public function actionUpdate(string $id): string|Response
	{
		$model = $this->findMovie($id);
		$genreIds = array_map(
			static fn (Genre $genre): string => (string) $genre->id,
			$model->genres,
		);

		if (Yii::$app->request->isPost) {
			$genreIds = $this->postedGenreIds($genreIds);

			if ($model->load(Yii::$app->request->post()) && $this->saveMovie($model, $genreIds)) {
				return $this->redirect(['update', 'id' => $model->id]);
			}
		}

		return $this->render('form', [
			'model' => $model,
			'genres' => $this->genres(),
			'genreIds' => $genreIds,
			'isNew' => false,
		]);
	}

	private function saveMovie(Movie $model, array $genreIds): bool
	{
		$genreIds = array_values(array_unique(array_filter($genreIds, static fn (string $id): bool => $id !== '')));

		if (array_filter($genreIds, fn (string $id): bool => !$this->isUuid($id)) !== []) {
			$model->addError('title', 'Selecione apenas géneros válidos.');

			return false;
		}

		$validGenreCount = Genre::find()->where(['id' => $genreIds])->count();

		if ($validGenreCount !== count($genreIds)) {
			$model->addError('title', 'Selecione apenas géneros válidos.');

			return false;
		}

		try {
			return Yii::$app->db->transaction(static function () use ($model, $genreIds): bool {
				if (!$model->save()) {
					return false;
				}

				MovieGenre::deleteAll(['movie_id' => $model->id]);
				foreach ($genreIds as $genreId) {
					$relation = new MovieGenre([
						'movie_id' => $model->id,
						'genre_id' => $genreId,
					]);

					if (!$relation->save()) {
						throw new \DomainException(
							implode('; ', $relation->getErrorSummary(true)),
						);
					}
				}

				return true;
			});
		} catch (\DomainException $error) {
			$model->addError('title', $error->getMessage());

			return false;
		}
	}

	/**
	 * @return list<string>
	 */
	private function postedGenreIds(array $fallback = []): array
	{
		$post = Yii::$app->request->post();
		$value = array_key_exists('genreIds', $post) ? $post['genreIds'] : $fallback;

		if (!is_array($value)) {
			return $fallback;
		}

		return array_values(array_map('strval', $value));
	}

	/**
	 * @return list<Genre>
	 */
	private function genres(): array
	{
		return Genre::find()->orderBy(['name' => SORT_ASC])->all();
	}

	private function findMovie(string $id): Movie
	{
		if (!$this->isUuid($id)) {
			throw new NotFoundHttpException('Filme não encontrado.');
		}

		$model = Movie::findOne($id);

		if (!$model instanceof Movie) {
			throw new NotFoundHttpException('Filme não encontrado.');
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
