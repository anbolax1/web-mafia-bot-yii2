<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "game_history".
 *
 * @property int $id
 * @property int $game_id id игры из таблицы game
 * @property string|null $member_discord_id discord_id участника
 * @property string $description имя участника
 * @property string $time время
 *
 * @property Game $game
 */
class GameHistory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'game_history';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['game_id', 'description', 'time'], 'required'],
            [['game_id'], 'integer'],
            [['member_discord_id', 'description', 'time'], 'string', 'max' => 255],
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
            'game_id' => 'Game ID',
            'member_discord_id' => 'Member Discord ID',
            'description' => 'Description',
            'time' => 'Time',
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
