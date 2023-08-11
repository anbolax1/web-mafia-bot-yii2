<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "discord_user".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $discord_id
 * @property string $username
 * @property string $avatar
 * @property string|null $access_token
 * @property string|null $refresh_token
 * @property int $updated_at
 *
 * @property User $user
 */
class DiscordUser extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'discord_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'updated_at'], 'integer'],
            [['discord_id', 'username', 'avatar', 'updated_at'], 'required'],
            [['access_token', 'refresh_token'], 'string'],
            [['discord_id', 'avatar'], 'string', 'max' => 255],
            [['username'], 'string', 'max' => 32],
            [['discord_id'], 'unique'],
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
            'user_id' => 'User ID',
            'discord_id' => 'Discord ID',
            'username' => 'Username',
            'avatar' => 'Avatar',
        ];
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
