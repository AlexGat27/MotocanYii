<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "models".
 *
 * @property int $id
 * @property int $brand_id
 * @property string $name
 * @property string $data
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Brands $brand
 * @property Scenario[] $scenarios
 */
class Model extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'models';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['brand_id', 'name', 'data'], 'required'],
            [['brand_id'], 'integer'],
            [['data', 'created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['brand_id'], 'exist', 'skipOnError' => true, 'targetClass' => Brands::class, 'targetAttribute' => ['brand_id' => 'id']],
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
            'name' => 'Name',
            'data' => 'Data',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Brand]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(Brands::class, ['id' => 'brand_id']);
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
