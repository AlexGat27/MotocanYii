<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%brand_model}}`.
 */
class m240714_043852_create_brand_model_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%brand_model}}', [
            'id' => $this->primaryKey(),  // Новый столбец id как первичный ключ
            'brand_id' => $this->integer()->notNull(),
            'model_id' => $this->integer()->notNull(),
            'data' => $this->json()->notNull(),
        ]);

        // creates index for column `brand_id`
        $this->createIndex(
            '{{%idx-brand_model-brand_id}}',
            '{{%brand_model}}',
            'brand_id'
        );

        // add foreign key for table `{{%brands}}`
        $this->addForeignKey(
            '{{%fk-brand_model-brand_id}}',
            '{{%brand_model}}',
            'brand_id',
            '{{%brands}}',
            'id',
            'CASCADE'
        );

        // creates index for column `model_id`
        $this->createIndex(
            '{{%idx-brand_model-model_id}}',
            '{{%brand_model}}',
            'model_id'
        );

        // add foreign key for table `{{%models}}`
        $this->addForeignKey(
            '{{%fk-brand_model-model_id}}',
            '{{%brand_model}}',
            'model_id',
            '{{%models}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%brands}}`
        $this->dropForeignKey(
            '{{%fk-brand_model-brand_id}}',
            '{{%brand_model}}'
        );

        // drops index for column `brand_id`
        $this->dropIndex(
            '{{%idx-brand_model-brand_id}}',
            '{{%brand_model}}'
        );

        // drops foreign key for table `{{%models}}`
        $this->dropForeignKey(
            '{{%fk-brand_model-model_id}}',
            '{{%brand_model}}'
        );

        // drops index for column `model_id`
        $this->dropIndex(
            '{{%idx-brand_model-model_id}}',
            '{{%brand_model}}'
        );

        $this->dropTable('{{%brand_model}}');
    }
}
