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
 * @property string $genres
 *
 * @property Sessions[] $sessions
 */
class Movies extends \yii\db\ActiveRecord
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
            [['id', 'title', 'synopsis', 'age_rating', 'poster_url', 'trailer_url', 'genres'], 'string'],
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
            'genres' => 'Genres',
        ];
    }

    /**
     * Gets query for [[Sessions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSessions()
    {
        return $this->hasMany(Sessions::class, ['movie_id' => 'id']);
    }

}
