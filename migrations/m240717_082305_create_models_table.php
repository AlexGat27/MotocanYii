<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%models}}`.
 */
class m240717_082305_create_models_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%models}}', [
            'id' => $this->primaryKey(),
            'brand_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
        $this->createIndex(
            '{{%idx-models-brand_id}}',
            '{{%models}}',
            'brand_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        // Удаление индекса
        $this->dropIndex(
            '{{%idx-models-brand_id}}',
            '{{%models}}'
        );
        $this->dropTable('{{%models}}');
    }
}
