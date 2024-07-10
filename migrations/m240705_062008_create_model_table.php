<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%model}}`.
 */
class m240705_062008_create_model_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%model}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'attributes' => $this->json()->notNull(), // JSON поле для хранения атрибутов модели
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP')->notNull(),
        ]);
        $this->createIndex('{{%idx-model-name}}', '{{%model}}', 'name', true); // Индекс на колонку "name" для ускорения поиска по имени
        $this->createIndex('{{%idx-model-created_at}}', '{{%model}}', 'created_at'); // Индекс на колонку "created_at" для ускорения выборок по дате создания
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('{{%idx-model-name}}', '{{%model}}');
        $this->dropIndex('{{%idx-model-created_at}}', '{{%model}}');
        $this->dropTable('{{%model}}');
    }
}
