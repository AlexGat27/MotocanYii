<?php

use yii\db\Migration;

/**
 * Class m240705_064218_add_model_column
 */
class m240705_064218_add_model_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Добавляем поле model_id
        $this->addColumn('{{%scenario}}', 'model_id', $this->integer()->notNull());

        // Создаем внешний ключ
        $this->addForeignKey(
            'fk-scenario-model_id',
            '{{%scenario}}',
            'model_id',
            '{{%model}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Удаляем внешний ключ
        $this->dropForeignKey('fk-scenario-model_id', '{{%scenario}}');

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
        echo "m240705_064218_add_model_column cannot be reverted.\n";

        return false;
    }
    */
}
