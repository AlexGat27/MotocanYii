<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        // Удаление всех предыдущих данных
        $auth->removeAll();

        // Создание разрешений
        $usersPerm = $auth->createPermission('users');
        $usersPerm->description = 'Interaction with users';
        $auth->add($usersPerm);

        $scenariosPerm = $auth->createPermission('scenarios');
        $scenariosPerm->description = 'Interaction with manufactures';
        $auth->add($scenariosPerm);

        $rolesPerm = $auth->createPermission('roles');
        $rolesPerm->description = 'Interaction with roles';
        $auth->add($rolesPerm);

        $modelsPerm = $auth->createPermission('models');
        $modelsPerm->description = 'Interaction with models';
        $auth->add($modelsPerm);

        // Создание ролей
        $admin = $auth->createRole('admin');
        $auth->add($admin);
        $auth->addChild($admin, $usersPerm);
        $auth->addChild($admin, $scenariosPerm);
        $auth->addChild($admin, $rolesPerm);
        $auth->addChild($admin, $modelsPerm);

        $user = $auth->createRole('user');
        $auth->add($user);
        $auth->addChild($user, $scenariosPerm);

        $banned = $auth->createPermission('banned');
        $auth->add($banned);
    }
}
