<?php

namespace app\controllers;

use app\models\Brands;
use app\models\Models;
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

    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $brandModels = Models::find()
            ->with(['brand'])
            ->asArray()
            ->all();

        $data = [];
        foreach ($brandModels as $brandModel) {
            $data[] = [
                'brand_id' => $brandModel['brand_id'],
                'model_id' => $brandModel['id'],
                'brand_name' => $brandModel['brand']['name'],
                'model_name' => $brandModel['name'],
                'attributes' => json_decode($brandModel['data'], true),
            ];
        }

        return $data;
    }

    public function actionView($id)
    {
        $model = Models::findOne($id);
        if ($model) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested model does not exist.');
        }
    }

    public function actionCreate()
    {
        $model = new Models();
        if (Models::findOne(['name' => Yii::$app->request->post('name')])) {
            Yii::$app->response->statusCode = 400;
            return ['status' => 'error', 'message' => 'Model already exists.'];
        }
        if ($model->load(Yii::$app->request->post(), '')) {
            // Проверка валидности данных
            if ($model->validate()) {
                // Сохранение модели
                if ($model->save()) {
                    return ['status' => 'success', 'model' => $model];
                } else {
                    // Ошибка при сохранении модели
                    Yii::$app->response->statusCode = 500;
                    return ['status' => 'error', 'message' => 'Failed to save the model.'];
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

    public function actionUpdate($id)
    {
        $model = Models::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException('The requested model does not exist.');
        }

        $postData = Yii::$app->request->getBodyParams();
        if (isset($postData['data'])) {
            $model->data = json_encode($postData['data']);
        }
        if (isset($postData['model_name'])) {
            $model->name = $postData['model_name'];
        }
        if (isset($postData['brand_id'])) {
            $model->brand_id = $postData['brand_id'];
        }

        if ($model->save()) {
            return [
                'status' => 'success',
                'brand_id' => $model->brand_id,
                'brand_name' => Brands::findOne($model->brand_id)->name,
                'model_id' => $id,
                'model_name' => $model->name,
                'data' => json_decode($model->data, true),
            ];
        } else {
            return ['status' => 'error', 'errors' => $model->errors];
        }
    }


    public function actionDelete($id)
    {
        $brandModel = Models::findOne($id);
        if ($brandModel) {
            $brandModel->delete();
            return ['status' => 'success'];
        } else {
            throw new NotFoundHttpException('The requested model does not exist.');
        }
    }
}
