<?php

use yii\db\Migration;

class m260930_165204_create_rooms_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('rooms', [
            'id' => 'UUID PRIMARY KEY DEFAULT uuidv7()',
            'name' => 'TEXT NOT NULL',
            'layout' => 'JSONB',
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('rooms');
    }
}
