<?php

use yii\db\Migration;

class m260930_171059_create_seats_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('seats', [
            'id' => 'uuid PRIMARY KEY DEFAULT uuidv7()',
            'room_id' => 'uuid NOT NULL REFERENCES rooms(id)',
            'row' => 'varchar(10) NOT NULL',
            'number' => 'integer NOT NULL',
        ]);

        $this->createIndex(
            'uq_seats_room_row_number',
            'seats',
            ['room_id', 'row', 'number'],
            true,
        );
    }

    public function safeDown()
    {
        $this->dropTable('seats');
    }
}
