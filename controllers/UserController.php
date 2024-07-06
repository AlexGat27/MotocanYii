<?php

namespace app\controllers;

use app\models\LoginForm;
use app\models\RegisterForm;
use app\models\User;
use Yii;
use yii\web\Response;
use yii\web\Controller;

class UserController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // Убедитесь, что поведение ContentNegotiator установлено для правильного формата ответа
        $behaviors['contentNegotiator'] = [
            'class' => \yii\filters\ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::class,
            'cors' => [                   // restrict access to domains:
                'Origin' => [
                    'http://localhost:8080', 'http://localhost:5173'

                ],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Max-Age' => 3600 * 5,


            ],
        ];

        return $behaviors;
    }
    public function actionRegister()
    {
        $model = new RegisterForm();
        $request = Yii::$app->request->getBodyParams();
        $model->username = $request["username"];
        $model->password = $request["password"];
        $model->reCaptcha = $request["reCaptcha"];
        return $model->register();
    }

    public function actionLogin()
    {
        $model = new LoginForm();
        $model->username = Yii::$app->request->post('username');
        $model->password = Yii::$app->request->post('password');
        $model->reCaptcha = Yii::$app->request->post('reCaptcha');
        if ($model->login()) {
            return [
                'success' => true,
                'message' => 'Login successful.',
                'user' => Yii::$app->user->identity,
            ];
        }

        return [
            Yii::$app->response->statusCode = 401,
            'success' => false,
            'errors' => $model->errors,
        ];
    }

    public function actionLogout(){
        Yii::$app->user->logout();
    }

    public function actionCheckAuth()
    {
        if (Yii::$app->user->isGuest) {
            return ['status' => 'unauthorized'];
        }

        return [
            'status' => 'authorized',
            'user' => Yii::$app->user->identity,
        ];
    }
}