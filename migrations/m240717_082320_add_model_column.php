<?php

use yii\db\Migration;

/**
 * Class m240717_082320_add_model_column
 */
class m240717_082320_add_model_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Добавляем поле model_id
        $this->addColumn('{{%scenario}}', 'model_id', $this->integer());

        // Создаем индекс для model_id
        $this->createIndex(
            'idx-scenario-model_id',
            '{{%scenario}}',
            'model_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Удаляем внешний ключ

        // Удаляем индекс
        $this->dropIndex('idx-scenario-model_id', '{{%scenario}}');

        // Удаляем поле model_id
        $this->dropColumn('{{%scenario}}', 'model_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240717_082320_add_model_column cannot be reverted.\n";

        return false;
    }
    */
}
