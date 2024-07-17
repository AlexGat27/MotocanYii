<?php

use yii\db\Migration;

/**
 * Class m240717_082335_init_rbac
 */
class m240717_082335_init_rbac extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // Создание ролей
        $admin = $auth->createRole('admin');
        $auth->add($admin);

        $user = $auth->createRole('user');
        $auth->add($user);

        $banned = $auth->createRole('banned');
        $auth->add($banned);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $auth->removeAll(); // Удаляет все данные, связанные с RBAC
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240717_082335_init_rbac cannot be reverted.\n";

        return false;
    }
    */
}
