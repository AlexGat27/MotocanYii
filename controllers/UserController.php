<?php

namespace app\controllers;

use app\models\LoginForm;
use app\models\RegisterForm;
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
            'cors' => [
                // restrict access to
                'Origin' => ['http://localhost:5173', 'http://localhost:8080'],
                // Allow only POST and PUT methods
                'Access-Control-Request-Method' => ['POST', 'GET', 'OPTIONS'],
                // Allow only headers 'X-Wsse'
                'Access-Control-Request-Headers' => ['*'],
                // Allow credentials (cookies, authorization headers, etc.) to be exposed to the browser
                'Access-Control-Allow-Credentials' => true,
                // Allow OPTIONS caching
                'Access-Control-Max-Age' => 3600,
                // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                'Access-Control-Expose-Headers' => [],
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
            ];
        }

        return [
            'success' => false,
            'errors' => $model->errors,
        ];
    }

    public function actionLogout(){
        Yii::$app->user->logout();
    }
}