<?php

namespace app\commands;

use app\models\User;
use Yii;
use yii\console\Controller;

class RootUserController extends Controller
{
    public function actionInit()
    {
        $user = User::findByUsername('root');
        $auth = Yii::$app->authManager;
        if ($user){
            $user->delete();
            $auth->revokeAll($user);
        }

        $user = new User();
        $user->id = 1;
        $user->username = "root";
        $user->setPassword("shurikgat2704");
        $user->save();

        $auth = Yii::$app->authManager;
        $auth->assign($auth->getRole('admin'), $user->id);
    }
}