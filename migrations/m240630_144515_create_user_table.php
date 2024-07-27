<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 */
class m240630_144515_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey()->notNull(),
            'username' => $this->string(100)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'auth_key' => $this->string(32)->notNull()->unique(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
        ]);
        $this->createIndex('{{%idx-user-username}}', '{{%user}}', 'username', true); // Индекс на колонку "username" для ускорения поиска по имени пользователя
        $this->createIndex('{{%idx-user-created_at}}', '{{%user}}', 'created_at'); // Индекс на колонку "created_at" для ускорения выборок по дате создания
        $this->createIndex('{{%idx-user-auth_key}}', '{{%user}}', 'auth_key');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('{{%idx-user-auth_key}}', '{{%user}}');
        $this->dropIndex('{{%idx-user-username}}', '{{%user}}');
        $this->dropIndex('{{%idx-user-created_at}}', '{{%user}}');
        $this->dropTable('{{%user}}');
    }
}
