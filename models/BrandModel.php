<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "brand_model".
 *
 * @property int $id
 * @property int $brand_id
 * @property int $model_id
 * @property string $data
 *
 * @property Brand $brand
 * @property Model $model
 * @property Scenario[] $scenarios
 */
class BrandModel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'brand_model';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['brand_id', 'model_id', 'data'], 'required'],
            [['brand_id', 'model_id'], 'integer'],
            [['data'], 'safe'],
            [['brand_id'], 'exist', 'skipOnError' => true, 'targetClass' => Brand::class, 'targetAttribute' => ['brand_id' => 'id']],
            [['model_id'], 'exist', 'skipOnError' => true, 'targetClass' => Model::class, 'targetAttribute' => ['model_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'brand_id' => 'Brand ID',
            'model_id' => 'Model ID',
            'data' => 'Data',
        ];
    }

    /**
     * Gets query for [[Brand]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(Brand::class, ['id' => 'brand_id']);
    }

    /**
     * Gets query for [[Model]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getModel()
    {
        return $this->hasOne(Model::class, ['id' => 'model_id']);
    }

    /**
     * Gets query for [[Scenarios]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getScenarios()
    {
        return $this->hasMany(Scenario::class, ['model_id' => 'id']);
    }
}
