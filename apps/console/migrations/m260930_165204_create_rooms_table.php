<?php

use yii\db\Migration;

class m260930_165204_create_rooms_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('rooms', [
            'id' => 'uuid PRIMARY KEY DEFAULT uuidv7()',
            'name' => 'varchar(100) NOT NULL',
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('rooms');
    }
}
