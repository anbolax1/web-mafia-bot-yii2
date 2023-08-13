<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "voice_channel".
 *
 * @property int $id
 * @property string $discord_id
 * @property int $guild_id
 * @property string|null $name
 *
 * @property ChannelMember[] $channelMembers
 * @property Guild $guild
 */
class VoiceChannel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'voice_channel';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['discord_id', 'guild_id'], 'required'],
            [['guild_id'], 'integer'],
            [['discord_id', 'name'], 'string', 'max' => 255],
            [['guild_id'], 'exist', 'skipOnError' => true, 'targetClass' => Guild::class, 'targetAttribute' => ['guild_id' => 'id']],
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
            'guild_id' => 'Guild ID',
            'name' => 'Name',
        ];
    }

    /**
     * Gets query for [[ChannelMembers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChannelMembers()
    {
        return $this->hasMany(ChannelMember::class, ['channel_id' => 'id']);
    }

    /**
     * Gets query for [[Guild]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGuild()
    {
        return $this->hasOne(Guild::class, ['id' => 'guild_id']);
    }
}
