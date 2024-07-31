<?php

namespace app\controllers;

use app\models\CanComands;
use app\models\CanCommands;
use app\models\Models;
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
            ->with(['model'])
            ->where(['scenario.user_id' => $userId])
            ->asArray()
            ->all(); // Получаем все записи
        $data = [];
        foreach ($scenarios as $scenario) {
            $data[] = $this->generateSuccessResponse($scenario, $scenario['model']);
        }
        return $data;
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
        $brandModel = Models::findOne(['brand_id' => $brandId, 'id' => $modelId]);

        if ($brandModel !== null) {
            $model->model_id = $brandModel->id;
            $model->user_id = Yii::$app->user->id;

            if ($model->save()) {
                return $this->generateSuccessResponse($model, $brandModel);
            } else {
                return ['status' => 'error', 'errors' => $model->errors];
            }
        } else {
            return $this->generateErrorResponse('BrandModel not found with the given model_id');
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
            $brandModel = Models::findOne($postData['model_id']);
            if ($brandModel) {
                $scenario->model_id = $brandModel->id;
            } else {
                return $this->generateErrorResponse('BrandModel not found with the given model_id');
            }
        }
        if (isset($postData['name'])) {
            $scenario->name = $postData['name'];
        }

        if ($scenario->save()) {
            $brandModel = Models::findOne($scenario->model_id);
            return $this->generateSuccessResponse($scenario, $brandModel);
        } else {
            return ['status' => 'error', 'errors' => $scenario->errors];
        }
    }
    public function actionDownload($id)
    {
        $scenario = Scenario::findOne($id);
        if (!$scenario) {
            throw new NotFoundHttpException('Scenario not found.');
        }
        if (!$scenario->data) {
            return $this->generateErrorResponse('Scenario data not saving.');
        }
        return ['status' => 'success', 'hexData' => $scenario->data];
    }
    private function generateSuccessResponse($model, $brandModel)
    {
        $canComandNames = CanCommands::find()
            ->select('name')
            ->where(['model_id' => $brandModel['id']])
            ->column();
        return [
            'id' => $model['id'],
            'name' => $model['name'],
            'jsonData' => is_string($model['jsonData']) ? json_decode($model['jsonData']) : $model['jsonData'],
            'user_id' => $model['user_id'],
            'model' => [
                'id' => $brandModel['id'],
                'name' => $brandModel['name'],
                'canCommands' => $canComandNames,
            ]

        ];
    }
    private function generateErrorResponse($message){
        return ['status' => 'error', 'message' => $message];
    }
}
