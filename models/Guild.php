<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "guild".
 *
 * @property int $id
 * @property string $discord_id
 * @property string|null $name
 * @property string|null $voice_channels
 */
class Guild extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'guild';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['discord_id'], 'required'],
            [['voice_channels'], 'string'],
            [['discord_id', 'name'], 'string', 'max' => 255],
            [['discord_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'discord_id' => 'Discord ID',
            'name' => 'Name',
            'voice_channels' => 'Voice Channels',
        ];
    }

    /**
     * Gets query for [[VoiceChannels]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVoiceChannels(): \yii\db\ActiveQuery
    {
        return $this->hasMany(VoiceChannel::class, ['guild_id' => 'id']);
    }
}
