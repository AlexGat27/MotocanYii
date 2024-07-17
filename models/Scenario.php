<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "scenario".
 *
 * @property int $id
 * @property string $name
 * @property resource|null $data
 * @property int $user_id
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $jsonData
 * @property int $model_id
 *
 * @property Models $model
 * @property User $user
 */
class Scenario extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'scenario';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'user_id', 'model_id'], 'required'],
            [['data'], 'string'],
            [['user_id', 'model_id'], 'integer'],
            [['created_at', 'updated_at', 'jsonData'], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['model_id'], 'exist', 'skipOnError' => true, 'targetClass' => Models::class, 'targetAttribute' => ['model_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'data' => 'Data',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'jsonData' => 'Json Data',
            'model_id' => 'Model ID',
        ];
    }

    /**
     * Gets query for [[Model]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getModel()
    {
        return $this->hasOne(Models::class, ['id' => 'model_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
