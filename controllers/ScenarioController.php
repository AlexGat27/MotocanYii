<?php

namespace app\controllers;

use app\models\BrandModel;
use app\models\Model;
use app\models\Scenario;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ScenarioController extends Controller
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
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['scenarios'],
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

    /**
     * Displays a list of scenarios for the current user.
     * @return array
     */
    public function actionIndex()
    {
        $userId = Yii::$app->user->id;
        $scenarios = Scenario::find()
            ->select(['scenario.id', 'scenario.name', 'scenario.jsonData', 'models.brand_id', 'models.id as model_id', 'models.data as model_attributes'])
            ->joinWith('model') // Объединяем с моделью brandModel
            ->where(['scenario.user_id' => $userId]) // Условие по пользователю
            ->asArray() // Возвращаем результат в виде массива
            ->all(); // Получаем все записи

        foreach ($scenarios as &$scenario) {
            if (isset($scenario['model_attributes'])) {
                $scenario['model_attributes'] = json_decode($scenario['model_attributes'], true);
            }
            if (isset($scenario['jsonData'])) {
                $scenario['jsonData'] = json_decode($scenario['jsonData'], true);
            }
        }
        return $scenarios;
    }

    /**
     * Creates a new scenario.
     * @return array
     */
    public function actionCreate()
    {
        $model = new Scenario();
        $model->name = Yii::$app->request->post('name');
        $brandId = Yii::$app->request->post('brand_id');
        $modelId = Yii::$app->request->post('model_id');
        $brandModel = Model::findOne(['brand_id' => $brandId, 'id' => $modelId]);

        if ($brandModel !== null) {
            $model->model_id = $brandModel->id;
            $model->user_id = Yii::$app->user->id;

            if ($model->save()) {
                $scenarioData = [
                    'id' => $model->id,
                    'name' => $model->name,
                    'brand_id' => $brandId,
                    'model_id' => $modelId,
                    'model_attributes' => $brandModel->data
                ];
                return $scenarioData;
            } else {
                return ['status' => 'error', 'errors' => $model->errors];
            }
        } else {
            return ['status' => 'error', 'message' => 'BrandModel not found with the given brand_id and model_id'];
        }
    }

    /**
     * Deletes an existing scenario.
     * @param int $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $userId = Yii::$app->user->id;
        $scenario = Scenario::findOne(['id' => $id, 'user_id' => $userId]);

        if ($scenario) {
            $scenario->delete();
            return ['status' => 'success'];
        }
        throw new NotFoundHttpException('Scenario not found.');
    }

    /**
     * Updates an existing scenario.
     * @param int $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $scenario = Scenario::findOne($id);
        if (!$scenario) {
            throw new NotFoundHttpException('Scenario not found.');
        }

        $postData = Yii::$app->request->getBodyParams();

        if (isset($postData['jsonData'])) {
            $scenario->jsonData = $postData['jsonData'];
            $scenario->data = Yii::$app->arduinoConverter->processJsonData($postData['jsonData']);
        }
        if (isset($postData['model_id'])) {
            $brandModel = Model::findOne($postData['model_id']);
            if ($brandModel) {
                $scenario->model_id = $brandModel->id;
            } else {
                return ['status' => 'error', 'message' => 'BrandModel not found with the given brand_id and model_id'];
            }
        }
        if (isset($postData['name'])) {
            $scenario->name = $postData['name'];
        }

        if ($scenario->save()) {
            $brandModel = Model::findOne($scenario->model_id);
            $scenarioData = [
                'id' => $scenario->id,
                'name' => $scenario->name,
                'jsonData' => json_decode($scenario->jsonData, true),
                'brand_id' => $brandModel->brand_id,
                'model_id' => $brandModel->id,
                'model_attributes' => json_decode($brandModel->data, true)
            ];
            return $scenarioData;
        } else {
            return ['status' => 'error', 'errors' => $scenario->errors];
        }
    }
}
