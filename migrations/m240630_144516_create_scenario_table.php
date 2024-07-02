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
            'data' => $this->binary()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->notNull(),
        ]);

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
        $this->dropTable('{{%scenario}}');
    }
}
