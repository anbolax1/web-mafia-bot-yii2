<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "game_setting".
 *
 * @property int $id
 * @property int $game_id id игры из таблицы game
 * @property string $settings настройки игры
 *
 * @property Game $game
 */
class GameSetting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'game_setting';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['game_id', 'settings'], 'required'],
            [['game_id'], 'integer'],
            [['settings'], 'string'],
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
            'settings' => 'Settings',
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
