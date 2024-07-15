<?php

namespace app\controllers;

use app\models\Brand;
use app\models\BrandModel;
use app\models\Model;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class BrandModelController extends Controller
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
            'only' => ['create', 'update', 'delete', 'index', 'view'],
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $brandModels = BrandModel::find()
            ->with(['brand', 'model'])
            ->asArray()
            ->all();

        $data = [];
        foreach ($brandModels as $brandModel) {
            $data[] = [
                'brand_id' => $brandModel['brand_id'],
                'model_id' => $brandModel['model_id'],
                'brand_name' => $brandModel['brand']['name'],
                'model_name' => $brandModel['model']['name'],
                'attributes' => json_decode($brandModel['data'], true),
            ];
        }

        return $data;
    }

    public function actionView($brand_id, $model_id)
    {
        $brandModel = BrandModel::find()
            ->select(['brand_model.brand_id', 'brand_model.model_id', 'brand_model.data', 'brand.name as brand_name', 'model.name as model_name'])
            ->joinWith('brand')
            ->joinWith('model')
            ->where(['brand_model.brand_id' => $brand_id, 'brand_model.model_id' => $model_id])
            ->asArray()
            ->one();

        if ($brandModel) {
            return [
                'brand_id' => $brandModel->brand_id,
                'model_id' => $brandModel->model_id,
                'brand_name' => $brandModel->brand_name,
                'model_name' => $brandModel->model_name,
                'data' => json_decode($brandModel->data, true),
            ];
        } else {
            throw new NotFoundHttpException('The requested brand-model combination does not exist.');
        }
    }

    public function actionCreate()
    {
        $brandModel = new BrandModel();
        $brandModel->brand_id = Yii::$app->request->post('brand_id');
        $brandModel->model_id = Yii::$app->request->post('model_id');
        $brandModel->data = json_encode(Yii::$app->request->post('data'));

        if ($brandModel->save()) {
            // Загружаем имена бренда и модели
            $brand = Brand::findOne($brandModel->brand_id);
            $model = Model::findOne($brandModel->model_id);

            return [
                'status' => 'success',
                'brand_id' => $brandModel->brand_id,
                'brand_name' => $brand->name,
                'model_id' => $brandModel->model_id,
                'model_name' => $model->name,
                'data' => json_decode($brandModel->data, true),
            ];
        } else {
            return ['status' => 'error', 'errors' => $brandModel->errors];
        }
    }

    public function actionUpdate($brand_id, $model_id)
    {
        $brandModel = BrandModel::findOne(['brand_id' => $brand_id, 'model_id' => $model_id]);
        if (!$brandModel) {
            throw new NotFoundHttpException('The requested brand-model combination does not exist.');
        }

        $postData = Yii::$app->request->getBodyParams();
        if (isset($postData['data'])) {
            $brandModel->data = json_encode($postData['data']);
        }

        if ($brandModel->save()) {
            // Загружаем имена бренда и модели
            $brand = Brand::findOne($brandModel->brand_id);
            $model = Model::findOne($brandModel->model_id);

            return [
                'status' => 'success',
                'brand_id' => $brandModel->brand_id,
                'brand_name' => $brand->name,
                'model_id' => $brandModel->model_id,
                'model_name' => $model->name,
                'data' => json_decode($brandModel->data, true),
            ];
        } else {
            return ['status' => 'error', 'errors' => $brandModel->errors];
        }
    }


    public function actionDelete($brand_id, $model_id)
    {
        $brandModel = BrandModel::findOne(['brand_id' => $brand_id, 'model_id' => $model_id]);
        if ($brandModel) {
            $brandModel->delete();
            return ['status' => 'success'];
        } else {
            throw new NotFoundHttpException('The requested brand-model combination does not exist.');
        }
    }
}
