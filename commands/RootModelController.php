<?php

namespace app\commands;

use app\models\Brands;
use app\models\Models;
use Yii;
use yii\console\Controller;

class RootModelController extends Controller
{
    public function actionIndex(){
        $brandName = "Тестовая марка";
        $modelName = "Тестовая модель";

        $brand = new Brands();
        $brand->id = 1;
        $brand->name = $brandName;

        if ($brand->save()) {
            $model = new Models();
            $model->id = 1;
            $model->name = $modelName;
            $model->brand_id = $brand->id;
            if ($model->save()) {
                Yii::info("Успешное создание тестовой модели");
            }
            else{
                Yii::error($model->getErrors());
            }


        } else {
           Yii::error($brand->getErrors());
        }
    }
}