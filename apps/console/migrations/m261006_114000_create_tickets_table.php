<?php

use yii\db\Migration;

class m261006_114000_create_tickets_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('tickets', [
            'id' => 'UUID PRIMARY KEY DEFAULT uuidv7()',
            'order_id' => 'UUID NOT NULL REFERENCES orders(id)',
            'session_id' => 'UUID NOT NULL REFERENCES sessions(id)',
            'seat_id' => 'UUID NOT NULL REFERENCES seats(id)',
            'price' => 'NUMERIC(8,2) NOT NULL',
            'code' => 'TEXT NOT NULL',
            'created_at' => 'TIMESTAMP NOT NULL',
            'used_at' => 'TIMESTAMP',
        ]);

        $this->createIndex('uq_tickets_code', 'tickets', 'code', true);
        $this->createIndex(
            'uq_tickets_session_seat',
            'tickets',
            ['session_id', 'seat_id'],
            true,
        );
        $this->createIndex('idx_tickets_order_id', 'tickets', 'order_id');
        $this->createIndex('idx_tickets_session_id', 'tickets', 'session_id');
    }

    public function safeDown()
    {
        $this->dropTable('tickets');
    }
}
