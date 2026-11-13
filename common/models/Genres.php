<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "genres".
 *
 * @property string $id
 * @property string $name
 *
 * @property MovieGenres[] $movieGenres
 * @property Movies[] $movies
 */
class Genres extends \yii\db\ActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'genres';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['id', 'name'], 'required'],
			[['id', 'name'], 'string'],
			[['name'], 'unique'],
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
			'name' => 'Name',
		];
	}

	/**
	 * Gets query for [[MovieGenres]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getMovieGenres()
	{
		return $this->hasMany(MovieGenres::class, ['genre_id' => 'id']);
	}

	/**
	 * Gets query for [[Movies]].
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getMovies()
	{
		return $this->hasMany(Movies::class, ['id' => 'movie_id'])->viaTable('movie_genres', ['genre_id' => 'id']);
	}
}
