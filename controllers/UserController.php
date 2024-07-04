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

        return $behaviors;
    }
    public function actionRegister()
    {
        $model = new RegisterForm();
        $request = Yii::$app->request->getBodyParams();
        $model->username = $request["username"];
        $model->password = $request["password"];
        $model->reCaptcha = $request["reCaptcha"];
        if ($model->register()) {
            return [
                'success' => true,
                'message' => 'Registration successful.',
            ];
        }
        return [
            'success' => true,
            'errors' => $model->errors,
        ];
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
        $session = Yii::$app->session;
        $sessionId = $session->getId();
        Yii::info("Current session ID: $sessionId");
        if (Yii::$app->user->isGuest) {
            return ['status' => 'unauthorized'];
        }

        return [
            'status' => 'authorized',
            'user' => Yii::$app->user->identity,
        ];
    }
}