<?php

namespace app\controllers;

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
            'only' => ['create', 'update', 'delete', 'index'], // Действия, для которых требуется аутентификация
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
        $userId = Yii::$app->user->id;
        $scenarios = Scenario::find()
            ->select(['scenario.id', 'scenario.name', 'scenario.jsonData', 'model.attributes as model_attributes',
                'model.name as model_name', 'model.id as model_id']) // Выбираем поля из таблицы scenario и связанной модели model
            ->joinWith('model') // Объединяем с моделью model
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

    public function actionCreate()
    {
        $model = new Scenario();
        $model->name = Yii::$app->request->post('name');
        $modelName = Yii::$app->request->post('model_name');
        $modelModel = Model::findOne(['name' => $modelName]);

        if ($modelModel !== null) {
            $model->model_id = $modelModel->id;
            $model->user_id = Yii::$app->user->id;

            if ($model->save()) {
                // Добавляем model_name в массив данных, который будет возвращен как JSON
                $scenarioData = [
                    'id' => $model->id,
                    'name' => $model->name,
                    'jsonData' => $model->jsonData,
                    'model_name' => $modelName,
                    'model_attributes' => $modelModel->attributes,
                    'model_id' => $modelModel->id
                ];
                return $scenarioData;
            } else {
                return $model->errors;
            }
        } else {
            return ['error' => 'Модель не найдена по указанному имени'];
        }
    }

    public function actionDelete($id)
    {
        $userId = Yii::$app->user->id;
        $scenario = Scenario::findOne(['id' => $id, 'user_id' => $userId]);
        if ($scenario) {
            $scenario->delete();
            return ['status' => 'success'];
        }
        return ['status' => 'error', 'message' => 'Scenario not found'];
    }

    public function actionUpdate($id)
    {
        $scenario = Scenario::findOne($id);
        if (!$scenario) {
            throw new NotFoundHttpException('Scenario not found.');
        }

        // Загружаем данные для обновления из запроса
        $postData = Yii::$app->request->getBodyParams();
        $modelModel = Model::findOne(['name' => $postData['model_name']]);

        if (isset($postData['json_data'])){
            $scenario->jsonData = $postData['json_data'];
            $scenario->data = Yii::$app->arduinoConverter->processJsonData($postData['json_data']);
        }
        if (isset($postData['model_name'])){
            $scenario->model_id = $modelModel->id;
        }
        if (isset($postData['name'])){
            $scenario->name = $postData['name'];
        }

        if ($scenario->save()) {
            $scenarioData = [
                'id' => $scenario->id,
                'name' => $scenario->name,
                'jsonData' => $scenario->jsonData,
                'model_name' => $modelModel->name,
                'model_attributes' => $modelModel->attributes,
                'model_id' => $modelModel->id
            ];
            return $scenarioData;
        } else {
            return $scenario->errors;
        }
    }
}