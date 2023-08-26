<?php

namespace app\models;

use Yii;
use yii\base\BaseObject;
use yii\data\ActiveDataProvider;

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
            'id' => 'Номер',
            'hostName' => 'Ведущий',
            'guildName' => 'Сервер',
            'gameStatus' => 'Статус игры',
            'winRole' => 'Победитель',
            'startTime' => 'Начало игры',
            'endTime' => 'Окончание игры',
        ];
    }

    public function getGames($data = []): ActiveDataProvider
    {
        $query = self::find()->where(['<>', 'status', Game::GAME_CANCELED]);

        $dataProvider = new ActiveDataProvider([
               'query' => $query,
               'sort' => $this->sortField()
           ]);

        $this->load($data);

        return $dataProvider;
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

    public static function getRoleInRus($role)
    {
        $rolesInRus = [
            self::ROLE_MIR => 'Мирный житель',
            self::ROLE_SHERIFF => 'Комиссар',
            self::ROLE_DON => 'Дон мафии',
            self::ROLE_MAF => 'Мафия'
        ];
        return $rolesInRus[$role];
    }

    public static function getRoleTask($role)
    {
        $tasks = [
            self::ROLE_MIR => 'Попытаться найти мафию и выгнать её на дневном голосовании. Удачи!',
            self::ROLE_SHERIFF => 'Искать ночью мафию, написав в лс ведущему *Я ком чек <номер игрока>*. Удачи!',
            self::ROLE_DON => 'Убивать мирных жителей вместе со своей мафией и искать ночью комиссара, написав в лс ведущему *Я дон чек <номер игрока>*. Удачи!',
            self::ROLE_MAF => 'Убивать мирных жителей и не выдать себя. Удачи!'
        ];
        return $tasks[$role];
    }

    public static function getEmbedColor($role)
    {
        $colors = [
            self::ROLE_MIR => 15542585,
            self::ROLE_SHERIFF => 16761125,
            self::ROLE_DON => 328965,
            self::ROLE_MAF => 328965
        ];
        return $colors[$role];
    }

    public static function isMemberWin($gameWinRole, $memberRole)
    {
        if(($gameWinRole == 'maf' && in_array($memberRole, ['maf', 'don'])) || ($gameWinRole == 'mir' && in_array($memberRole, ['mir', 'com']))){
            return true;
        } else {
            return false;
        }
    }

    public static function getGameResult($win_role)
    {
        if($win_role == 'maf') {
            return 'Мафии';
        } else {
            return 'Мирных жителей';
        }
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

    public function getHostName()
    {
        return User::find()->where(['id' => $this->host_id])->one()->username;
    }

    public function getGuildName()
    {
        return Guild::find()->where(['discord_id' => $this->guild_id])->one()->name;
    }

    public function getGameStatus()
    {
        $statuses = [
            self::GAME_IN_PROCESS => 'Игра в процессе',
            self::GAME_FINISHED => 'Игра завершена',
        ];
        return $statuses[$this->status];
    }

    public function getWinRole()
    {
        if(empty($this->win_role)){
            return '';
        } else {
            return $this->win_role == 'mir' ? 'Мирные жители' : 'Мафия';
        }
    }

    public function getStartTime()
    {
        return gmdate("d.m.Y H:i:s", $this->start_time);
    }

    public function getEndTime()
    {
        if(empty($this->end_time)){
            return '';
        }
        return gmdate("d.m.Y H:i:s", $this->end_time);
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

    /**
     * Gets query for [[MemberRatingHistory]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMemberRatingHistory()
    {
        return $this->hasMany(MemberRatingHistory::class, ['game_id' => 'id']);
    }

    public static function sortField($params = null): array
    {
        return [
            'defaultOrder' => [
                'id' => SORT_DESC
            ],
            'attributes' => [
                'id' => [
                    'asc' => ['id' => SORT_ASC],
                    'desc' => ['id' => SORT_DESC],
                    'default' => SORT_ASC,
                ],
                'host_id' => [
                    'asc' => ['external_id' => SORT_ASC],
                    'desc' => ['external_id' => SORT_DESC],
                    'default' => SORT_ASC,
                ],
                'guild_id' => [
                    'asc' => ['pay_amount' => SORT_ASC],
                    'desc' => ['pay_amount' => SORT_DESC],
                    'default' => SORT_ASC,
                ],
                'status' => [
                    'asc' => ['payroll_info.marketplace' => SORT_ASC],
                    'desc' => ['payroll_info.marketplace' => SORT_DESC],
                    'default' => SORT_ASC,
                ],
                'win_role' => [
                    'asc' => ['curator_info.fio' => SORT_ASC],
                    'desc' => ['curator_info.fio' => SORT_DESC],
                    'default' => SORT_ASC,
                ],
                'start_time' => [
                    'asc' => ['employees_count' => SORT_ASC],
                    'desc' => ['employees_count' => SORT_DESC],
                    'default' => SORT_ASC,
                ],
            ],
        ];
    }
}
