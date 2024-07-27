<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%canComand}}`.
 */
class m240727_033728_create_canComands_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%canComands}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'comand_id' => $this->integer()->notNull()->unsigned()->unique(),
            'model_id' => $this->integer()->notNull(),
            'byte_1' => $this->integer()->notNull()->unsigned(),
            'byte_2' => $this->integer()->notNull()->unsigned(),
            'byte_3' => $this->integer()->notNull()->unsigned(),
            'byte_4' => $this->integer()->notNull()->unsigned(),
            'byte_5' => $this->integer()->notNull()->unsigned(),
            'byte_6' => $this->integer()->notNull()->unsigned(),
            'byte_7' => $this->integer()->notNull()->unsigned(),
            'byte_8' => $this->integer()->notNull()->unsigned(),
        ]);
        $this->addForeignKey('fk-canComands-model_id', 'canComands', 'model_id', 'models', 'id', 'CASCADE');
        $this->createIndex('idx-canComands-comand_id', 'canComands', 'comand_id');
        $this->createIndex('idx-canComands-model_id', 'canComands', 'model_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-canComands-model_id', 'canComands');
        $this->dropIndex('idx-canComands-model_id', 'canComands');
        $this->dropIndex('idx-canComands-comand_id', 'canComands');
        $this->dropTable('{{%canComand}}');
    }
}
