<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "game_member".
 *
 * @property int $id
 * @property int $game_id id игры из таблицы game
 * @property string $discord_id discord_id участника
 * @property string $name имя участника
 * @property string $avatar аватар участника
 * @property string $slot слот участника
 * @property string $role слот участника
 *
 * @property Game $game
 */
class GameMember extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'game_member';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['game_id', 'discord_id', 'name', 'avatar', 'slot', 'role'], 'required'],
            [['game_id'], 'integer'],
            [['discord_id', 'name', 'avatar', 'slot', 'role'], 'string', 'max' => 255],
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
            'discord_id' => 'Discord ID',
            'name' => 'Name',
            'avatar' => 'Avatar',
            'slot' => 'Slot',
            'role' => 'Role',
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
