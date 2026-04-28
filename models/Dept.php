<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Dept".
 *
 * @property int $Deptid
 * @property string $DeptName
 * @property int $SupDeptid
 *
 *
 * @property Userinfo $userinfo
 */
class Dept extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Dept';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['SupDeptid'], 'default', 'value' => 0],
            [['DeptName'], 'required'],
            [['SupDeptid'], 'integer'],
            [['DeptName'], 'string', 'max' => 50],
            [['DeptName'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Deptid' => 'Deptid',
            'DeptName' => 'Dept Name',
            'SupDeptid' => 'Sup Deptid',
        ];
    }

    /**
     * Gets query for [[Userinfo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserinfo()
    {
        return $this->hasMany(Userinfo::class, ['Deptid' => 'Deptid']);
    }

}
