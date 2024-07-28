<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%canComand}}`.
 */
class m240727_033728_create_canCommands_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%canCommands}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'command_id' => $this->string()->notNull()->unique(),
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
        $this->addForeignKey('fk-canCommands-model_id', 'canCommands', 'model_id', 'models', 'id', 'CASCADE');
        $this->createIndex('idx-canCommands-command_id', 'canCommands', 'command_id');
        $this->createIndex('idx-canCommands-model_id', 'canCommands', 'model_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        $this->dropForeignKey('fk-canCommands-model_id', 'canCommands');
        $this->dropIndex('idx-canCommands-model_id', 'canCommands');
        $this->dropIndex('idx-canCommands-command_id', 'canCommands');
        $this->dropTable('{{%canCommands}}');
    }
}
