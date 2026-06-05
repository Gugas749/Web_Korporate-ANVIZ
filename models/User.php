<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "UserAuth".
 *
 * @property string $UserId
 * @property string $Username
 * @property string $PasswordHash
 * @property string|null $AuthKey
 * @property string|null $AccessToken
 * @property int $AccessLevel
 *
 * @property Userinfo $user
 */
class User extends ActiveRecord implements IdentityInterface
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'UserAuth';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['AuthKey', 'AccessToken'], 'default', 'value' => null],
            [['AccessLevel'], 'default', 'value' => 0],
            [['UserId', 'Username', 'PasswordHash'], 'required'],
            [['AccessLevel'], 'integer'],
            [['UserId'], 'string', 'max' => 20],
            [['Username', 'PasswordHash', 'AuthKey', 'AccessToken'], 'string', 'max' => 255],
            [['UserId'], 'unique'],
            [['UserId'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['UserId' => 'Userid']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'UserId' => 'User ID',
            'Username' => 'Username',
            'PasswordHash' => 'Password Hash',
            'AuthKey' => 'Auth Key',
            'AccessToken' => 'Access Token',
            'AccessLevel' => 'Access Level',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['UserId' => 'UserId']);
    }




    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['AccessToken' => $token]);
    }

    public function getId()
    {
        return $this->UserId;
    }

    public function getAuthKey()
    {
        return $this->AuthKey;
    }

    public function validateAuthKey($authKey)
    {
        return $this->AuthKey === $authKey;
    }


    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['Username' => $username]);
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->PasswordHash);
    }
}
