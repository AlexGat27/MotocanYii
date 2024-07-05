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
            ->select(['scenario.*', 'model.name as model_name', 'model.id as model_id']) // Выбираем поля из таблицы scenario и связанной модели model
            ->joinWith('model') // Объединяем с моделью model
            ->where(['scenario.user_id' => $userId]) // Условие по пользователю
            ->asArray() // Возвращаем результат в виде массива
            ->all(); // Получаем все записи
        return $this->asJson($scenarios);
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
                $scenarioData = $model->attributes;
                $scenarioData['model_name'] = $modelName;

                return $this->asJson($scenarioData);
            } else {
                return $this->asJson($model->errors);
            }
        } else {
            return $this->asJson(['error' => 'Модель не найдена по указанному имени']);
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

        // Обновляем атрибуты сценария
        $scenario->jsonData = $postData['json_data'] ? $postData['json_data'] : $scenario->jsonData;
        $scenario->data = $postData['data'] ? $postData['data'] : $scenario->data;

        if ($scenario->save()) {
            return $this->asJson($scenario);
        } else {
            return $this->asJson($scenario->errors);
        }
    }
}