<?php

use yii\db\Migration;

/**
 * Class m240701_080644_add_authkey_passwordhash
 */
class m240701_080644_add_authkey_passwordhash extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'auth_key', $this->string(32)->notNull()->unique());
        $this->addColumn('{{%user}}', 'password_hash', $this->string()->notNull());
        $this->dropColumn('{{%user}}', 'password'); // Удаляем старое поле password

        $this->createIndex('{{%idx-user-auth_key}}', '{{%user}}', 'auth_key'); // Индекс на колонку "auth_key" для ускорения поиска по ключу аутентификации
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('{{%idx-user-auth_key}}', '{{%user}}');
        $this->addColumn('{{%user}}', 'password', $this->string(255)->notNull());
        $this->dropColumn('{{%user}}', 'auth_key');
        $this->dropColumn('{{%user}}', 'password_hash');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240701_080644_add_authkey_passwordhash cannot be reverted.\n";

        return false;
    }
    */
}
