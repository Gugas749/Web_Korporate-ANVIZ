<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Devices".
 *
 * @property int $Id
 * @property string $IpAddress
 */
class Devices extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Devices';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Id', 'IpAddress'], 'required'],
            [['Id'], 'integer'],
            [['IpAddress'], 'string', 'max' => 255],
            [['Id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'IpAddress' => 'Ip Address',
        ];
    }

}
