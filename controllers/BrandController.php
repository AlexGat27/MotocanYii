<?php

namespace app\controllers;

use app\models\Brands;
use app\models\Models;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * BrandController implements the CRUD actions for Brand model.
 */
class BrandController extends Controller
{
    /**
     * {@inheritdoc}
     */
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
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['models'],
                ]
            ]
        ];

        return $behaviors;
    }

    /**
     * Lists all Brand models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Brands::find(),
        ]);

        return $dataProvider->getModels();
    }

    /**
     * Displays a single Brand model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }
    public function actionIndexModels($id){
        $models = Models::find()->where(['brand_id' => $id])->all();
        return $models;
    }

    /**
     * Creates a new Brand model.
     * If creation is successful, returns the created model.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Brands();

        if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
            Yii::$app->response->statusCode = 201;
            return $model;
        }

        Yii::$app->response->statusCode = 400;
        return [
            'status' => 'error',
            'errors' => $model->errors,
        ];
    }

    /**
     * Updates an existing Brand model.
     * If update is successful, returns the updated model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
            return $model;
        }

        Yii::$app->response->statusCode = 400;
        return [
            'status' => 'error',
            'errors' => $model->errors,
        ];
    }

    /**
     * Deletes an existing Brand model.
     * If deletion is successful, returns a success message.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->delete()) {
            return [
                'status' => 'success',
                'message' => 'Brand deleted successfully.',
            ];
        }

        Yii::$app->response->statusCode = 400;
        return [
            'status' => 'error',
            'message' => 'Failed to delete brand.',
        ];
    }

    /**
     * Finds the Brand model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Brands the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Brands::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested brand does not exist.');
    }
}
