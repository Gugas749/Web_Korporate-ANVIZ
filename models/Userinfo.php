<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "Userinfo".
 *
 * @property string $Userid
 * @property string|null $UserCode
 * @property string|null $Name
 * @property string|null $Sex
 * @property string|null $Pwd
 * @property int $Deptid
 * @property string|null $Nation
 * @property string|null $Birthday
 * @property string|null $EmployDate
 * @property string|null $Telephone
 * @property string|null $Duty
 * @property string|null $NativePlace
 * @property string|null $IDCard
 * @property string|null $Address
 * @property string|null $Mobile
 * @property string|null $Educated
 * @property string|null $Polity
 * @property string|null $Specialty
 * @property bool|null $IsAtt
 * @property bool|null $Isovertime
 * @property bool|null $Isrest
 * @property string|null $Remark
 * @property int|null $MgFlag
 * @property string|null $CardNum
 * @property resource|null $Picture
 * @property int|null $UserFlag
 * @property int|null $Groupid
 * @property int|null $ClassFlag
 * @property resource|null $OtherInfo
 * @property int|null $admingroupid
 * @property string|null $accessfrom
 * @property string|null $accessto
 *
 * @property User $userAuth
 */
class Userinfo extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'Userinfo';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['UserCode', 'Name', 'Sex', 'Pwd', 'Nation', 'Birthday', 'EmployDate', 'Telephone', 'Duty', 'NativePlace', 'IDCard', 'Address', 'Mobile', 'Educated', 'Polity', 'Specialty', 'Remark', 'CardNum', 'Picture', 'OtherInfo', 'accessfrom', 'accessto'], 'default', 'value' => null],
            [['Groupid'], 'default', 'value' => 1],
            [['admingroupid'], 'default', 'value' => 0],
            [['Userid'], 'required'],
            [['Deptid', 'MgFlag', 'UserFlag', 'Groupid', 'ClassFlag', 'admingroupid'], 'integer'],
            [['Birthday', 'EmployDate', 'accessfrom', 'accessto'], 'safe'],
            [['IsAtt', 'Isovertime', 'Isrest'], 'boolean'],
            [['Picture', 'OtherInfo'], 'string'],
            [['Userid', 'UserCode'], 'string', 'max' => 20],
            [['Name', 'Pwd', 'Nation', 'Telephone', 'Duty', 'NativePlace', 'IDCard', 'Mobile', 'Educated', 'Polity', 'Specialty'], 'string', 'max' => 50],
            [['Sex', 'CardNum'], 'string', 'max' => 10],
            [['Address'], 'string', 'max' => 150],
            [['Remark'], 'string', 'max' => 250],
            [['UserCode'], 'unique'],
            [['Userid'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Userid' => 'Userid',
            'UserCode' => 'User Code',
            'Name' => 'Name',
            'Sex' => 'Sex',
            'Pwd' => 'Pwd',
            'Deptid' => 'Deptid',
            'Nation' => 'Nation',
            'Birthday' => 'Birthday',
            'EmployDate' => 'Employ Date',
            'Telephone' => 'Telephone',
            'Duty' => 'Duty',
            'NativePlace' => 'Native Place',
            'IDCard' => 'Id Card',
            'Address' => 'Address',
            'Mobile' => 'Mobile',
            'Educated' => 'Educated',
            'Polity' => 'Polity',
            'Specialty' => 'Specialty',
            'IsAtt' => 'Is Att',
            'Isovertime' => 'Isovertime',
            'Isrest' => 'Isrest',
            'Remark' => 'Remark',
            'MgFlag' => 'Mg Flag',
            'CardNum' => 'Card Num',
            'Picture' => 'Picture',
            'UserFlag' => 'User Flag',
            'Groupid' => 'Groupid',
            'ClassFlag' => 'Class Flag',
            'OtherInfo' => 'Other Info',
            'admingroupid' => 'Admingroupid',
            'accessfrom' => 'Accessfrom',
            'accessto' => 'Accessto',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAuth()
    {
        return $this->hasOne(User::class, ['UserId' => 'Userid']);
    }
}
