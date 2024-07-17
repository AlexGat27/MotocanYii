<?php

namespace app\controllers;

use app\models\Brand;
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
                    'roles' => ['admin'],
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

        $brandModels = Model::find()
            ->with(['brand'])
            ->asArray()
            ->all();

        $data = [];
        foreach ($brandModels as $brandModel) {
            $data[] = [
                'brand_id' => $brandModel['brand_id'],
                'model_id' => $brandModel['model_id'],
                'brand_name' => $brandModel['brand']['name'],
                'model_name' => $brandModel['name'],
                'attributes' => json_decode($brandModel['data'], true),
            ];
        }

        return $data;
    }

    public function actionView($brand_id, $model_id)
    {
//        $brandModel = BrandModel::find()
//            ->select(['brand_model.brand_id', 'brand_model.model_id', 'brand_model.data', 'brand.name as brand_name', 'model.name as model_name'])
//            ->joinWith('brand')
//            ->joinWith('model')
//            ->where(['brand_model.brand_id' => $brand_id, 'brand_model.model_id' => $model_id])
//            ->asArray()
//            ->one();
//
//        if ($brandModel) {
//            return [
//                'brand_id' => $brandModel->brand_id,
//                'model_id' => $brandModel->model_id,
//                'brand_name' => $brandModel->brand_name,
//                'model_name' => $brandModel->model_name,
//                'data' => json_decode($brandModel->data, true),
//            ];
//        } else {
//            throw new NotFoundHttpException('The requested brand-model combination does not exist.');
//        }
    }

    public function actionCreate()
    {
        $brandName = Yii::$app->request->post('brand_name');
        $modelName = Yii::$app->request->post('model_name');
        $brand = Brand::findOne(['name' => $brandName]);

        if(!$brand){
            $brand = new Brand();
            $brand->name = $brandName;
        }

        if ($brand->save()) {
            // Загружаем имена бренда и модели
            $model = Model::findOne(['name' => $modelName, 'brand_id' => $brand->id]);

            if (!$model){
                $model = new Model();
                $model->name = $modelName;
                $model->brand_id = $brand->id;
                $model->data = json_encode(Yii::$app->request->post('data'));
            }else{
                return ['status' => 'error', 'message' => "Такая модель в БД уже есть"];
            }
            if ($model->save()) {
                return [
                    'status' => 'success',
                    'brand_id' => $model->brand_id,
                    'brand_name' => $brand->name,
                    'model_id' => $model->id,
                    'model_name' => $model->name,
                    'data' => json_decode($model->data, true),
                ];
            }
            else{
                return ['status' => 'error', 'errors' => $model->errors];
            }


        } else {
            return ['status' => 'error', 'errors' => $brand->errors];
        }
    }

    public function actionUpdate($id)
    {
        $model = Model::findOne($id);

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
                'brand_name' => Brand::findOne($model->brand_id)->name,
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
        $brandModel = Model::findOne($id);
        if ($brandModel) {
            $brandModel->delete();
            return ['status' => 'success'];
        } else {
            throw new NotFoundHttpException('The requested model does not exist.');
        }
    }
}
