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
        $brand->name = $brandName;

        if ($brand->save()) {
            $model = new Models();
            $model->name = $modelName;
            $model->brand_id = $brand->id;
            $model->data = [
                "scenario" => "Тестовая модель",
                "conditionAttributes" => [
                    [
                        "condition" => "Сухой контакт",
                        "values" => [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15],
                        "countSignals" => [0,1,2,3,4,5,6,7,8,9,10,"Постоянно"],
                        "delayTypes" => ["более", "менее", "равно"]
                    ],
                    [
                        "condition" => "Фоторезистор",
                        "values" => ["День", "Ночь"],
                        "countSignals" => ["Постоянно"],
                        "delayTypes" => ["более", "менее", "равно"]
                    ]
                ],
                "actionAttributes" => [
                    "actions" => ["Включить", "Выключить", "Мигать", "Включить/Выключить"],
                    "interruptions" => [0,100,200,300,400,500,600,700,800,900,1000],
                    "workingPeriod" => [100,200,300,400,500,600,700,800,900,1000,"Нет"],
                    "powers" => [10, 20, 30, 40, 50, 60, 70, 80, 90, 100],
                ]
            ];
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