<?php

namespace app\controllers;

use app\models\Model;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class ModelController extends Controller
{
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
        return $model->attributes;
    }
}