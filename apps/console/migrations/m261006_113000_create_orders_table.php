<?php

use yii\db\Migration;

class m261006_113000_create_orders_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('orders', [
            'id' => 'UUID PRIMARY KEY DEFAULT uuidv7()',
            'user_id' => 'UUID NOT NULL REFERENCES users(id)',
            'total' => 'NUMERIC(10,2) NOT NULL',
            'created_at' => 'TIMESTAMP NOT NULL',
        ]);

        $this->createIndex('idx_orders_user_id', 'orders', 'user_id');
        $this->createIndex('idx_orders_created_at', 'orders', 'created_at');
    }

    public function safeDown()
    {
        $this->dropTable('orders');
    }
}
