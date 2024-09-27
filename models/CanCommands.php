<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "canCommands".
 *
 * @property int $id
 * @property string $name
 * @property string $command_id
 * @property int $model_id
 * @property int $byte_1
 * @property int $byte_2
 * @property int $byte_3
 * @property int $byte_4
 * @property int $byte_5
 * @property int $byte_6
 * @property int $byte_7
 * @property int $byte_8
 *
 * @property Models $model
 */
class CanCommands extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'canCommands';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'command_id', 'model_id', 'byte_1', 'byte_2', 'byte_3', 'byte_4', 'byte_5', 'byte_6', 'byte_7', 'byte_8'], 'required'],
            [['model_id', 'byte_1', 'byte_2', 'byte_3', 'byte_4', 'byte_5', 'byte_6', 'byte_7', 'byte_8'], 'integer'],
            [['name', 'command_id'], 'string', 'max' => 255],
            [['model_id'], 'exist', 'skipOnError' => true, 'targetClass' => Models::class, 'targetAttribute' => ['model_id' => 'id']],
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
            'command_id' => 'Command ID',
            'model_id' => 'Model ID',
            'byte_1' => 'Byte 1',
            'byte_2' => 'Byte 2',
            'byte_3' => 'Byte 3',
            'byte_4' => 'Byte 4',
            'byte_5' => 'Byte 5',
            'byte_6' => 'Byte 6',
            'byte_7' => 'Byte 7',
            'byte_8' => 'Byte 8',
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
}
