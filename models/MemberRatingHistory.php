<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "member_rating_history".
 *
 * @property int $id
 * @property string $discord_id discord_id участника
 * @property int $game_id id игры
 * @property string $type тип рейтинга
 * @property string|null $guild_id id сервера, если рейтинг серверный
 * @property string $change_rating изменение рейтинга
 *
 * @property Game $game
 */
class MemberRatingHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'member_rating_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['discord_id', 'game_id', 'type', 'change_rating'], 'required'],
            [['game_id'], 'integer'],
            [['discord_id', 'type', 'guild_id', 'change_rating'], 'string', 'max' => 255],
            [['game_id'], 'exist', 'skipOnError' => true, 'targetClass' => Game::class, 'targetAttribute' => ['game_id' => 'id']],
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
            'game_id' => 'Game ID',
            'type' => 'Type',
            'guild_id' => 'Guild ID',
            'change_rating' => 'Change Rating',
        ];
    }

    /**
     * Gets query for [[Game]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGame()
    {
        return $this->hasOne(Game::class, ['id' => 'game_id']);
    }
}
