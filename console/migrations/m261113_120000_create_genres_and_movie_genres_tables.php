<?php

use yii\db\Migration;

class m261113_120000_create_genres_and_movie_genres_tables extends Migration
{
	public function safeUp()
	{
		$this->createTable('genres', [
			'id' => 'UUID PRIMARY KEY DEFAULT uuidv7()',
			'name' => 'TEXT NOT NULL',
		]);

		$this->createIndex('uq_genres_name', 'genres', 'name', true);

		$this->createTable('movie_genres', [
			'movie_id' => 'UUID NOT NULL REFERENCES movies(id) ON DELETE CASCADE',
			'genre_id' => 'UUID NOT NULL REFERENCES genres(id) ON DELETE CASCADE',
			'PRIMARY KEY ([[movie_id]], [[genre_id]])',
		]);

		$this->createIndex('idx_movie_genres_genre_id', 'movie_genres', 'genre_id');

		$genres = [
			'Action',
			'Adventure',
			'Animation',
			'Comedy',
			'Crime',
			'Documentary',
			'Drama',
			'Family',
			'Fantasy',
			'History',
			'Horror',
			'Music',
			'Mystery',
			'Romance',
			'Science Fiction',
			'TV Movie',
			'Thriller',
			'War',
			'Western',
		];

		foreach ($genres as $genre) {
			$this->insert('genres', ['name' => $genre]);
		}
	}

	public function safeDown()
	{
		$this->dropTable('movie_genres');
		$this->dropTable('genres');
	}
}
