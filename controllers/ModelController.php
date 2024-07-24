<?php

namespace app\controllers;

use app\models\Brands;
use app\models\Models;
use PhpParser\Node\Scalar\String_;
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
                'attributes' => is_string($brandModel['data']) ? json_decode($brandModel['data']) : $brandModel['data']
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
                    return $this->generateSuccessResponse($model);
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
        $model = Models::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('The requested model does not exist.');
        }
        if ($model->load(Yii::$app->request->post(), '')) {
            // Проверка валидности данных
            if ($model->validate()) {
                // Сохранение модели
                if ($model->save()) {
                    return $this->generateSuccessResponse($model);
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
        $brandModel = Models::findOne($id);
        if ($brandModel) {
            $brandModel->delete();
            return ['status' => 'success'];
        } else {
            throw new NotFoundHttpException('The requested model does not exist.');
        }
    }

    private function generateSuccessResponse($model)
    {
        return ['status' => 'success', 'model' => [
            'id' => $model->id,
            'brand_id' => $model->brand_id,
            'name' => $model->name,
            'data' => is_string($model->data) ? json_decode($model->data) : $model->data
        ]];
    }
    private function generateErrorResponse($message){
        return ['status' => 'error', 'message' => $message];
    }
}
