<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "movies".
 *
 * @property string $id
 * @property string $title
 * @property string|null $synopsis
 * @property int $duration_minutes
 * @property string|null $release_date
 * @property string|null $age_rating
 * @property string|null $poster_url
 * @property string|null $trailer_url
 *
 * @property Genre[] $genres
 * @property MovieGenre[] $movieGenres
 * @property Session[] $sessions
 */
class Movie extends \yii\db\ActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'movies';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['synopsis', 'release_date', 'age_rating', 'poster_url', 'trailer_url'], 'default', 'value' => null],
			[['id', 'title', 'duration_minutes'], 'required'],
			[['id', 'title', 'synopsis', 'age_rating', 'poster_url', 'trailer_url'], 'string'],
			[['duration_minutes'], 'default', 'value' => null],
			[['duration_minutes'], 'integer'],
			[['release_date'], 'safe'],
			[['id'], 'unique'],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'title' => 'Title',
			'synopsis' => 'Synopsis',
			'duration_minutes' => 'Duration Minutes',
			'release_date' => 'Release Date',
			'age_rating' => 'Age Rating',
			'poster_url' => 'Poster Url',
			'trailer_url' => 'Trailer Url',
		];
	}

	/**
	 * Gets query for [[Genre]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getGenres()
	{
		return $this->hasMany(Genre::class, ['id' => 'genre_id'])->viaTable('movie_genres', ['movie_id' => 'id']);
	}

	/**
	 * Gets query for [[MovieGenre]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getMovieGenres()
	{
		return $this->hasMany(MovieGenre::class, ['movie_id' => 'id']);
	}

	/**
	 * Gets query for [[Session]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getSessions()
	{
		return $this->hasMany(Session::class, ['movie_id' => 'id']);
	}
}
