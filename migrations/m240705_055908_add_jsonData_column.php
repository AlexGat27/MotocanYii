<?php

use yii\db\Migration;

/**
 * Class m240705_055908_add_jsonData_column
 */
class m240705_055908_add_jsonData_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%scenario}}', 'jsonData', $this->json());
        $this->alterColumn('{{%scenario}}', 'data', $this->binary()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%scenario}}', 'jsonData');
        $this->alterColumn('{{%scenario}}', 'data', $this->binary()->notNull());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240705_055908_add_jsonData_column cannot be reverted.\n";

        return false;
    }
    */
}
