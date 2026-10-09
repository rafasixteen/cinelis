<?php

use yii\db\Migration;

class m261006_112000_create_seat_holds_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('seat_holds', [
            'session_id' => 'UUID NOT NULL REFERENCES sessions(id)',
            'seat_id' => 'UUID NOT NULL REFERENCES seats(id)',
            'user_id' => 'UUID NOT NULL REFERENCES users(id)',
            'expires_at' => 'TIMESTAMP NOT NULL',
            'PRIMARY KEY (session_id, seat_id)',
        ]);

        $this->createIndex('idx_seat_holds_user_id', 'seat_holds', 'user_id');
        $this->createIndex('idx_seat_holds_expires_at', 'seat_holds', 'expires_at');
    }

    public function safeDown()
    {
        $this->dropTable('seat_holds');
    }
}
