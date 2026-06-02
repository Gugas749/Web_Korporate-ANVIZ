<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "V_Record".
 *
 * @property int $Logid
 * @property string $Userid
 * @property string $CheckTime
 * @property int $CheckType
 * @property int|null $Sensorid
 * @property int|null $WorkType
 * @property int|null $AttFlag
 * @property bool|null $Checked
 * @property bool|null $Exported
 * @property bool|null $OpenDoorFlag
 * @property float|null $temperature
 * @property int|null $whynoopen
 * @property int|null $mask
 * @property string|null $Name
 * @property int|null $Deptid
 * @property string|null $UserCode
 * @property string|null $Duty
 * @property string|null $DeptName
 * @property int|null $Clientid
 * @property string|null $ClientName
 * @property int|null $deviceflag
 * @property string|null $devicememo
 * @property int|null $Statusid
 * @property string|null $StatusChar
 * @property string|null $StatusText
 */
class VRecord extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'V_Record';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['Sensorid', 'WorkType', 'AttFlag', 'Checked', 'Exported', 'OpenDoorFlag', 'temperature', 'whynoopen', 'mask', 'Name', 'Deptid', 'UserCode', 'Duty', 'DeptName', 'Clientid', 'ClientName', 'deviceflag', 'devicememo', 'Statusid', 'StatusChar', 'StatusText'], 'default', 'value' => null],
            [['Logid', 'Userid', 'CheckTime', 'CheckType'], 'required'],
            [['Logid', 'CheckType', 'Sensorid', 'WorkType', 'AttFlag', 'whynoopen', 'mask', 'Deptid', 'Clientid', 'deviceflag', 'Statusid'], 'integer'],
            [['CheckTime'], 'safe'],
            [['Checked', 'Exported', 'OpenDoorFlag'], 'boolean'],
            [['temperature'], 'number'],
            [['Userid', 'UserCode'], 'string', 'max' => 20],
            [['Name', 'Duty', 'DeptName', 'ClientName', 'StatusText'], 'string', 'max' => 50],
            [['devicememo'], 'string', 'max' => 16],
            [['StatusChar'], 'string', 'max' => 2],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'Logid' => 'Logid',
            'Userid' => 'Userid',
            'CheckTime' => 'Check Time',
            'CheckType' => 'Check Type',
            'Sensorid' => 'Sensorid',
            'WorkType' => 'Work Type',
            'AttFlag' => 'Att Flag',
            'Checked' => 'Checked',
            'Exported' => 'Exported',
            'OpenDoorFlag' => 'Open Door Flag',
            'temperature' => 'Temperature',
            'whynoopen' => 'Whynoopen',
            'mask' => 'Mask',
            'Name' => 'Name',
            'Deptid' => 'Deptid',
            'UserCode' => 'User Code',
            'Duty' => 'Duty',
            'DeptName' => 'Dept Name',
            'Clientid' => 'Clientid',
            'ClientName' => 'Client Name',
            'deviceflag' => 'Deviceflag',
            'devicememo' => 'Devicememo',
            'Statusid' => 'Statusid',
            'StatusChar' => 'Status Char',
            'StatusText' => 'Status Text',
        ];
    }

}
