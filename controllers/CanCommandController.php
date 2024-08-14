<?php

namespace app\controllers;

use app\models\CanCommands;
use app\models\Models;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class CanCommandController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator'] = [
            'class' => \yii\filters\ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['create', 'update', 'delete'],
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['models'],
                ],
                [
                    'allow' => false,
                    'roles' => ['banned'],
                    'denyCallback' => function ($rule, $action) {
                        Yii::$app->response->statusCode = 403;
                        return ['status' => 'error', 'message' => 'You are banned.'];
                    },
                ]
            ],
        ];

        return $behaviors;
    }
    public function actionCreate()
    {
        $model = new CanCommands();
        if (CanCommands::findOne(['name' => Yii::$app->request->post('name')])) {
            Yii::$app->response->statusCode = 400;
            return ['status' => 'error', 'message' => 'Model already exists.'];
        }
        if ($model->load(Yii::$app->request->post(), '')) {
            // Проверка валидности данных
            if ($model->validate()) {
                // Сохранение модели
                if ($model->save()) {
                    return $model;
                } else {
                    // Ошибка при сохранении модели
                    Yii::$app->response->statusCode = 500;
                    return $this->generateErrorResponse('Failed to save the model.');
                }
            } else {
                // Ошибка при валидации модели
                Yii::$app->response->statusCode = 422;
                return ['status' => 'error', 'message' => 'Validation failed.', 'errors' => $model->errors];
            }
        } else {
            // Ошибка при загрузке данных из POST-запроса
            Yii::$app->response->statusCode = 400;
            return $this->generateErrorResponse('Failed to load data.');
        }

    }

    public function actionUpdate($id)
    {
        $model = CanCommands::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('The requested model does not exist.');
        }
        if ($model->load(Yii::$app->request->post(), '')) {
            // Проверка валидности данных
            if ($model->validate()) {
                // Сохранение модели
                if ($model->save()) {
                    return $model;
                } else {
                    // Ошибка при сохранении модели
                    Yii::$app->response->statusCode = 500;
                    return $this->generateErrorResponse('Failed to update the model.');
                }
            } else {
                // Ошибка при валидации модели
                Yii::$app->response->statusCode = 422;
                return ['status' => 'error', 'message' => 'Validation failed.', 'errors' => $model->errors];
            }
        } else {
            // Ошибка при загрузке данных из POST-запроса
            Yii::$app->response->statusCode = 400;
            return ['status' => 'error', 'message' => 'Failed to load data.'];
        }
    }


    public function actionDelete($id)
    {
        $model = CanCommands::findOne($id);
        if ($model) {
            $model->delete();
            return ['status' => 'success'];
        } else {
            throw new NotFoundHttpException('The requested model does not exist.');
        }
    }

    private function generateErrorResponse($message){
        return ['status' => 'error', 'message' => $message];
    }
}