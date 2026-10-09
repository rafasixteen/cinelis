<?php

use yii\db\Migration;

class m261006_111000_create_sessions_table extends Migration
{
    public function safeUp()
    {
        $this->execute("CREATE TYPE session_status AS ENUM ('scheduled', 'cancelled')");

        $this->createTable('sessions', [
            'id' => 'UUID PRIMARY KEY DEFAULT uuidv7()',
            'movie_id' => 'UUID NOT NULL REFERENCES movies(id)',
            'room_id' => 'UUID NOT NULL REFERENCES rooms(id)',
            'starts_at' => 'TIMESTAMP NOT NULL',
            'price' => 'NUMERIC(8,2) NOT NULL',
            'status' => "session_status NOT NULL DEFAULT 'scheduled'",
        ]);

        $this->createIndex('idx_sessions_movie_id', 'sessions', 'movie_id');
        $this->createIndex('idx_sessions_room_id', 'sessions', 'room_id');
        $this->createIndex('idx_sessions_starts_at', 'sessions', 'starts_at');
    }

    public function safeDown()
    {
        $this->dropTable('sessions');
        $this->execute('DROP TYPE session_status');
    }
}
