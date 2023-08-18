<?php

namespace app\components\game;

use app\models\ChannelMember;
use app\models\DiscordUser as DiscordUserModel;
use app\models\GameMember;
use app\models\GameSetting;
use app\models\MemberRating;
use app\models\MemberRatingHistory;
use app\models\User;
use GuzzleHttp\Client;
use Yii;
use yii\base\BaseObject;

class Game
{
    public function createGame($settings, $gameMembers)
    {
        try {
            $transaction = Yii::$app->db->beginTransaction();

            $hostUser = Yii::$app->user->getIdentity();
            $game = new \app\models\Game([
                'host_id' => $hostUser->getId(),
                'guild_id' => $this->getGuildId($hostUser),
                'status' => \app\models\Game::GAME_IN_PROCESS,
                'start_time' => strval(time())
            ]);
            if(!$game->save()){
                throw new \Exception('Игра не сохранена в базу!');
            }

            $gameSettings = new GameSetting([
                'game_id' => $game->id,
                'settings' => json_encode($settings)
            ]);
            if(!$gameSettings->save()){
                throw new \Exception('Настройки игры не сохранены в базу!');
            }

            $roles = [];
            for ($i = 1; $i <= 2; $i++) {
                $roles[] = \app\models\Game::ROLE_MAF;
            }
            for ($i = 1; $i <= 6; $i++) {
                $roles[] = \app\models\Game::ROLE_MIR;
            }
            $roles[] = \app\models\Game::ROLE_SHERIFF;
            $roles[] = \app\models\Game::ROLE_DON;

            //перемешиваем роли 3 раза
            shuffle($roles);
            shuffle($roles);
            shuffle($roles);

            foreach ($gameMembers as $gameMemberSlot => &$gameMember) {
                if($gameMemberSlot + 1 < 10) {
                    $gameMember['slot'] = sprintf("0%s", $gameMemberSlot + 1);
                } else {
                    $gameMember['slot'] = $gameMemberSlot + 1;
                }
                $gameMember['role'] = $roles[$gameMemberSlot];
                $gameMemberModel = new GameMember([
                    'game_id' => $game->id,
                    'discord_id' => strval($gameMember['discord_id']),
                    'name' => $gameMember['name'],
                    'avatar' => $gameMember['avatar'],
                    'slot' => strval($gameMember['slot']),
                    'role' => $gameMember['role'],
                ]);
                if(!$gameMemberModel->save()){
                    throw new \Exception('Участник игры не сохранен в базу!');
                }
            }
            $transaction->commit();
//            return [$game, $gameMembers];
            return $game;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    public function getGameSettings($game)
    {
        try {
            $gameSettings = json_decode($game->getGameSettings()->one()->settings, true);
            return array_combine(array_column($gameSettings, 'id'), array_column($gameSettings, 'value'));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getGuildId($user)
    {
        try {
            $channelMember = ChannelMember::find()->where(['discord_id' => $user->discordId])->one();
            if(empty($channelMember)){
                throw new \Exception("Ведущий не найден в таблице участников канала");
            }
            $voiceChannel = $channelMember->getChannel()->one();
            if(empty($voiceChannel)){
                throw new \Exception("Голосовой канал не найден в таблице каналов");
            }
            $guild = $voiceChannel->getGuild()->one();
            if(empty($guild)){
                throw new \Exception("Сервер не найден в таблице серверов");
            }
            return $guild->discord_id;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function writeRating($game)
    {
        try {
            $gameMembers = $game->getGameMembers()->all();

            foreach ($gameMembers as $gameMember) {
                $writeGeneralRatingResult = $this->writeGeneralRating($gameMember);
                $memberRatingHistory = new MemberRatingHistory([
                    'discord_id' => strval($gameMember->discord_id),
                    'game_id' => $game->id,
                    'type' => MemberRating::RATING_GENERAL,
                    'change_rating' => strval($writeGeneralRatingResult)
                ]);
                $memberRatingHistory->save();
                $writeGuildRatingResult = $this->writeGuildRating($gameMember, $game->guild_id);
                $memberRatingHistory = new MemberRatingHistory([
                    'discord_id' => strval($gameMember->discord_id),
                    'game_id' => $game->id,
                    'type' => MemberRating::RATING_GUILD,
                    'guild_id' => $game->guild_id,
                    'change_rating' => strval($writeGuildRatingResult)
                ]);
                $memberRatingHistory->save();
            }
            return  true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


    public function writeGeneralRating($gameMember)
    {
        try {
            $currentRating = MemberRating::find()->where(['discord_id' => $gameMember->discord_id, 'type' => MemberRating::RATING_GENERAL])->one();
            if(empty($currentRating)){
                $currentRating = new MemberRating([
                    'discord_id' => strval($gameMember->discord_id),
                    'type' => MemberRating::RATING_GENERAL,
                    'rating' => '1000'
                ]);
                $currentRating->save();
            }
            $memberGames = GameMember::getMemberGames($gameMember);

            /* считаем изменения рейтинга по формуле:
            Победа: +5
            Поражение: -5
            Стрик: 3: +/- 1
            Стрик: 5: +/- 2
            Стрик: 7...: +/- 3\
            ЛХ: 1/3 +1
            ЛХ: 2/3 +2
            ЛХ: 3/3 +3
            Поднялся по фолам или тех. причине: -3 */
            $memberResult = json_decode($gameMember->result, true);
            $theBestMoveChange = $memberResult['the_best_move']['right_count'];

            $isMemberWin = $this->isMemberWin($gameMember, $memberGames[0]);
            if($isMemberWin) {
                $streak = $this->getWinStreak($gameMember, $memberGames);
            } else {
//                $streak = $this->getLoseStreak($gameMember, $memberGames);
                $streak = 0;
            }
            $streakCoeff = $this->getStreakCoeff($streak);

            $foulsCoeff = in_array($memberResult['delete_reason'], [\app\models\Game::REASON_FOULED, \app\models\Game::REASON_TECH]) ? 3 : 0;

            // формула посчёта баллов за игру
            if($isMemberWin) {
                $changeRating = 5 + $streakCoeff + $theBestMoveChange - $foulsCoeff;
            } else {
                $changeRating = -5 - $streakCoeff + $theBestMoveChange - $foulsCoeff;
            }

            $currentRating->updateAttributes([
                'rating' => strval(intval($currentRating->rating) + intval($changeRating))
            ]);

            return $changeRating;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function writeGuildRating($gameMember, $guildId)
    {
        try {
            $currentRating = MemberRating::find()->where(['discord_id' => $gameMember->discord_id, 'type' => MemberRating::RATING_GUILD, 'guild_id' => $guildId])->one();
            if(empty($currentRating)){
                $currentRating = new MemberRating([
                    'discord_id' => strval($gameMember->discord_id),
                    'type' => MemberRating::RATING_GUILD,
                    'guild_id' => strval($guildId),
                    'rating' => '1000'
                ]);
                $currentRating->save();
            }
            $memberGames = GameMember::getMemberGames($gameMember, $guildId);

            /* считаем изменения рейтинга по формуле:
            Победа: +5
            Поражение: -5
            Стрик: 3: +/- 1
            Стрик: 5: +/- 2
            Стрик: 7...: +/- 3\
            ЛХ: 1/3 +1
            ЛХ: 2/3 +2
            ЛХ: 3/3 +3
            Поднялся по фолам или тех. причине: -3 */
            $memberResult = json_decode($gameMember->result, true);
            $theBestMoveChange = $memberResult['the_best_move']['right_count'];

            $isMemberWin = $this->isMemberWin($gameMember, $memberGames[0]);
            if($isMemberWin) {
                $streak = $this->getWinStreak($gameMember, $memberGames);
            } else {
//                $streak = $this->getLoseStreak($gameMember, $memberGames);
                $streak = 0;
            }
            $streakCoeff = $this->getStreakCoeff($streak);

            $foulsCoeff = in_array($memberResult['delete_reason'], [\app\models\Game::REASON_FOULED, \app\models\Game::REASON_TECH]) ? 3 : 0;

            // формула посчёта баллов за игру
            if($isMemberWin) {
                $changeRating = 5 + $streakCoeff + $theBestMoveChange - $foulsCoeff;
            } else {
                $changeRating = -5 - $streakCoeff + $theBestMoveChange - $foulsCoeff;
            }

            $currentRating->updateAttributes([
                'rating' => strval(intval($currentRating->rating) + intval($changeRating))
            ]);

            return $changeRating;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getWinStreak($gameMember, $memberGames)
    {
        try {
            $memberRole = in_array($gameMember->role, [\app\models\Game::ROLE_SHERIFF, \app\models\Game::ROLE_MIR]) ? \app\models\Game::ROLE_MIR : \app\models\Game::ROLE_MAF;
            $winStreak = 0;
            foreach ($memberGames as $memberGame) {
                if($memberGame->win_role == $memberRole) {
                    $winStreak++;
                } else {
                    break;
                }
            }
            return $winStreak;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getLoseStreak($gameMember, $memberGames)
    {
        try {
            $memberRole = in_array($gameMember->role, [\app\models\Game::ROLE_SHERIFF, \app\models\Game::ROLE_MIR]) ? \app\models\Game::ROLE_MIR : \app\models\Game::ROLE_MAF;
            $lostStreak = 0;
            foreach ($memberGames as $memberGame) {
                if($memberGame->win_role != $memberRole) {
                    $lostStreak++;
                } else {
                    break;
                }
            }
            return $lostStreak;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function isMemberWin($gameMember, $game)
    {
        try {
            $memberRole = in_array($gameMember->role, [\app\models\Game::ROLE_SHERIFF, \app\models\Game::ROLE_MIR]) ? \app\models\Game::ROLE_MIR : \app\models\Game::ROLE_MAF;
            if($memberRole == $game->win_role) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getStreakCoeff($streak)
    {
        try {
            switch ($streak){
                case 0:
                case 1:
                case 2:
                    return 0;
                case 3:
                case 4:
                    return 1;
                default:
                    return 2;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}