<?php

use yii\db\Migration;

class m261006_101923_create_movies_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('movies', [
            'id' => 'UUID PRIMARY KEY DEFAULT uuidv7()',
            'title' => 'TEXT NOT NULL',
            'synopsis' => 'TEXT',
            'duration_minutes' => 'INTEGER NOT NULL',
            'release_date' => 'DATE',
            'age_rating' => 'TEXT',
            'poster_url' => 'TEXT',
            'trailer_url' => 'TEXT',
            'genres' => "TEXT[] NOT NULL DEFAULT '{}'::TEXT[]",
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('movies');
    }
}
