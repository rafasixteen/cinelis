<?php

use yii\db\Migration;

class m261006_110000_create_users_table extends Migration
{
    public function safeUp()
    {
        $this->execute("CREATE TYPE user_role AS ENUM ('customer', 'employee', 'admin')");

        $this->createTable('users', [
            'id' => 'UUID PRIMARY KEY DEFAULT uuidv7()',
            'name' => 'TEXT NOT NULL',
            'email' => 'TEXT NOT NULL',
            'password_hash' => 'TEXT NOT NULL',
            'role' => "user_role NOT NULL DEFAULT 'customer'",
            'created_at' => 'TIMESTAMP NOT NULL',
        ]);

        $this->createIndex(
            'uq_users_email',
            'users',
            'email',
            true,
        );
    }

    public function safeDown()
    {
        $this->dropTable('users');
        $this->execute('DROP TYPE user_role');
    }
}
