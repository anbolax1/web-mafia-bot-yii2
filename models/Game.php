<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "game".
 *
 * @property int $id
 * @property int $host_id id ведущего из таблицы user
 * @property string $guild_id id сервера дискорда, на котором проводилась игра
 * @property string $status статус игры
 * @property string|null $win_role кто победил
 * @property string|null $start_time
 * @property string|null $end_time
 *
 * @property GameMember[] $gameMembers
 * @property GameSetting[] $gameSettings
 * @property User $host
 */
class Game extends \yii\db\ActiveRecord
{
    const GAME_IN_PROCESS = 'game_in_process';
    const GAME_FINISHED = 'game_finished';
    const GAME_CANCELED = 'game_canceled';


    const ROLE_MAF = 'maf';
    const ROLE_DON = 'don';
    const ROLE_SHERIFF = 'sheriff';
    const ROLE_MIR = 'mir';

    const REASON_VOTED = 'voted';
    const REASON_KILLED = 'killed';
    const REASON_FOULED = 'fouled';
    const REASON_TECH = 'tech_reason';

    const KILLED_FIRST = 'killed_first';

    const THE_BEST_MOVE = 'the_best_move';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'game';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['host_id', 'guild_id', 'status'], 'required'],
            [['host_id'], 'integer'],
            [['guild_id', 'status', 'win_role', 'start_time', 'end_time'], 'string', 'max' => 255],
            [['host_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['host_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'host_id' => 'Host ID',
            'guild_id' => 'Guild ID',
            'status' => 'Status',
            'win_role' => 'Win Role',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
        ];
    }

    public static function getGameActionDescription($reason): string
    {
        $reasonDescriptions = [
            self::REASON_VOTED => 'Заголосован',
            self::REASON_KILLED => 'Убит',
            self::REASON_FOULED => 'Поднят по фолам',
            self::REASON_TECH => 'Поднят по тех. причинам',
            self::KILLED_FIRST => 'Первый убиенный',
            self::THE_BEST_MOVE => 'Лучший ход',
        ];
        return $reasonDescriptions[$reason];
    }

    /**
     * Gets query for [[GameMembers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGameMembers()
    {
        return $this->hasMany(GameMember::class, ['game_id' => 'id']);
    }

    /**
     * Gets query for [[GameSettings]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGameSettings()
    {
        return $this->hasOne(GameSetting::class, ['game_id' => 'id']);
    }

    /**
     * Gets query for [[Host]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHost()
    {
        return $this->hasOne(User::class, ['id' => 'host_id']);
    }

    /**
     * Gets query for [[GameHistoriy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getGameHistory()
    {
        return $this->hasMany(GameHistory::class, ['game_id' => 'id']);
    }
}
