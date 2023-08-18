<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "member_rating".
 *
 * @property int $id
 * @property string $discord_id discord_id участника
 * @property string $type тип рейтинга
 * @property string|null $guild_id id сервера, если рейтинг серверный
 * @property string $rating рейтинг
 */
class MemberRating extends \yii\db\ActiveRecord
{
    const RATING_GENERAL = 'general';
    const RATING_GUILD = 'guild';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'member_rating';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['discord_id', 'type', 'rating'], 'required'],
            [['discord_id', 'type', 'guild_id', 'rating'], 'string', 'max' => 255],
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
            'type' => 'Type',
            'guild_id' => 'Guild ID',
            'rating' => 'Rating',
        ];
    }
}
