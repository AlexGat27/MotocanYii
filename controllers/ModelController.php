<?php

namespace app\controllers;

use app\models\Model;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ModelController extends Controller
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
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['create', 'update', 'delete', 'index', 'view'], // Действия, для которых требуется аутентификация
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'], // '@' обозначает авторизованных пользователей
                ],
            ],
        ];

        return $behaviors;
    }
    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Получаем список всех моделей
        $models = Model::find()->all();

        // Формируем массив данных для JSON
        $data = [];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->id,
                'name' => $model->name,
                'attributes' => $model->attributes, // Декодируем JSON в массив
            ];
        }

        return $data;
    }
    public function actionView($id){
        $model = Model::findOne($id);
        if ($model){
            return $model->attributes;
        }else{
            throw new NotFoundHttpException('The requested model does not exist.');
        }
    }
}