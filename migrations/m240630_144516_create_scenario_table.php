<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%scenario}}`.
 */
class m240630_144516_create_scenario_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%scenario}}', [
            'id' => $this->primaryKey()->notNull(),
            'name' => $this->string(100)->notNull(),
            'data' => $this->binary()->null(),
            'jsonData' => $this->json(),
            'user_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->notNull(),
        ]);
        $this->createIndex('{{%idx-scenario-name}}', '{{%scenario}}', 'name'); // Индекс на колонку "name" для ускорения поиска по имени сценария
        $this->createIndex('{{%idx-scenario-user_id}}', '{{%scenario}}', 'user_id'); // Индекс на колонку "user_id" для ускорения поиска по ID пользователя
        $this->createIndex('{{%idx-scenario-created_at}}', '{{%scenario}}', 'created_at'); // Индекс на колонку "created_at" для ускорения выборок по дате создания


        $this->addForeignKey(
            '{{%fk-scenario-user_id}}', // это имя внешнего ключа
            '{{%scenario}}', // таблица, к которой применяется внешний ключ
            'user_id', // столбец в этой таблице
            '{{%user}}', // таблица, на которую ссылается внешний ключ
            'id', // столбец в этой таблице, на который ссылается внешний ключ
            'CASCADE' // действие при удалении строки,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%fk-scenario-user_id}}', '{{%scenario}}');
        $this->dropIndex('{{%idx-scenario-name}}', '{{%scenario}}');
        $this->dropIndex('{{%idx-scenario-user_id}}', '{{%scenario}}');
        $this->dropIndex('{{%idx-scenario-created_at}}', '{{%scenario}}');
        $this->dropTable('{{%scenario}}');
    }
}
