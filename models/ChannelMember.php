<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "channel_member".
 *
 * @property int $id
 * @property string $discord_id
 * @property string|null $name
 * @property string|null $avatar
 * @property string|null $self_video включена ли вебка участника канала
 * @property int $channel_id
 * @property string|null $flag
 *
 * @property VoiceChannel $channel
 */
class ChannelMember extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'channel_member';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['discord_id', 'channel_id'], 'required'],
            [['channel_id'], 'integer'],
            [['discord_id', 'name', 'avatar', 'self_video', 'flag'], 'string', 'max' => 255],
            [['channel_id'], 'exist', 'skipOnError' => true, 'targetClass' => VoiceChannel::class, 'targetAttribute' => ['channel_id' => 'id']],
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
            'avatar' => 'Avatar',
            'channel_id' => 'Channel ID',
            'flag' => 'Flag',
        ];
    }

    /**
     * Gets query for [[Channel]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChannel()
    {
        return $this->hasOne(VoiceChannel::class, ['id' => 'channel_id']);
    }
}
