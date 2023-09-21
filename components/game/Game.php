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
            date_default_timezone_set("Europe/Moscow");

            $transaction = Yii::$app->db->beginTransaction();

            $hostUser = Yii::$app->user->getIdentity();
            $hostDiscordId = $hostUser->discordId;
            $hostServerNick = ChannelMember::find()->where(['discord_id' => $hostDiscordId])->one()->name;

            $guildId = $this->getGuildId($hostUser);

            //создаём текстовый канал игры
            //TODO вынести категорию в настройки сервера
            $categoryId = 803807947532009473;

//            $channelId = Yii::$app->bot->createTextChannel($guildId, $categoryId, "игра {$hostServerNick}");

            $game = new \app\models\Game([
                'host_id' => $hostUser->getId(),
                'guild_id' => $guildId,
                'status' => \app\models\Game::GAME_IN_PROCESS,
                'start_time' => strval(time()),
//                'channel_id' => strval($channelId)
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

            $embedText = '';

            $gameDatetime = date('d.m.Y H:i:s', $game->start_time);

            $gameMemberIds = []; //массив discord_id участников игры
            foreach ($gameMembers as $gameMemberSlot => &$gameMember) {
                $gameMemberIds[] = $gameMember['discord_id'];
               /* if($gameMember['discord_id'] != '472399225460752400'){
                    continue;
                }*/
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

                $embedText .= "{$gameMember['slot']}. <@{$gameMember['discord_id']}>, роль: **" . \app\models\Game::getRoleInRus($gameMember['role']) . "**".PHP_EOL;

                if(!$gameMemberModel->save()){
                    throw new \Exception('Участник игры не сохранен в базу!');
                }

                try {
                    Yii::$app->bot->changeUserNick($game->guild_id, $gameMember['discord_id'], $gameMember['name'], $gameMember['slot']);
                } catch (\Exception $e) {
                    try {
                        Yii::$app->bot->sendMessage($gameMember['discord_id'], "Я не смог поменять тебе ник. Пожалуйста, поставь перед ником слот {$gameMember['slot']} и не забудь точку (пример - 01.)!");
                    } catch (\Exception $e) {}
                }

                try {
                    $memberEmber = [
                        'title' => sprintf("%s. Ваша роль - %s", $gameMember['slot'], \app\models\Game::getRoleInRus($gameMember['role'])),
                        'description' => sprintf("Ваша задача: %s", \app\models\Game::getRoleTask($gameMember['role'])),
                        'footer' => [
                            'text' => "Игра {$hostServerNick} от {$gameDatetime} (МСК)"
                        ],
                        'color' => \app\models\Game::getEmbedColor($gameMember['role'])
                    ];
                    Yii::$app->bot->sendEmbed($gameMember['discord_id'], $memberEmber);
                } catch (\Exception $e) {
                    Yii::$app->bot->sendMessage($hostDiscordId, sprintf("<@%s> не получил в ЛС свою роль. Напиши ему сам. Его роль - %s", $gameMember['discord_id'], \app\models\Game::getRoleInRus($gameMember['role'])));
                }
            }

            $hostChannel = ChannelMember::find()->where(['discord_id' => $hostDiscordId])->one()->channel_id;
            $channelMembers = ChannelMember::find()->where(['channel_id' => $hostChannel])->andWhere(['not in' , 'discord_id' , $gameMemberIds])->all();
            foreach ($channelMembers as $channelMember) {
                if($channelMember->discord_id == $hostDiscordId) {
                    try {
                        Yii::$app->bot->changeUserNick($game->guild_id, $hostDiscordId, $channelMember->name, '!Вед');
                    } catch (\Exception $e) {
                        try {
                            Yii::$app->bot->sendMessage($channelMember->discord_id, "Я не смог поменять тебе ник. Пожалуйста, поставь перед ником слот '!Вед.'");
                        } catch (\Exception $e) {}
                    }
                    continue;
                }
                try {
                    Yii::$app->bot->changeUserNick($game->guild_id, $channelMember->discord_id, $channelMember->name, 'Зр');
                } catch (\Exception $e) {
                    try {
                        Yii::$app->bot->sendMessage($channelMember->discord_id, "Я не смог поменять тебе ник. Пожалуйста, поставь перед ником слот 'Зр.'");
                    } catch (\Exception $e) {}
                }
            }

            // отправляем ведущему список игроков и ролей
            $embed = [
                'title' => 'Участники игры',
                'description' => $embedText,
                'footer' => [
                    'text' => "Игра {$hostServerNick} от {$gameDatetime} (МСК)"
                ],
                'color' => '15724534' // Цвет в десятичном формате hex (пр. ff0000 -> 16711680)
            ];

            Yii::$app->bot->sendEmbed($hostDiscordId, $embed);
            $transaction->commit();

            //создаём ветки для ролей

            /*$mafThreadId = Yii::$app->bot->createThread($channelId, 'мафия');
            $donThreadId = Yii::$app->bot->createThread($channelId, 'дон');
            $sheriffThreadId = Yii::$app->bot->createThread($channelId, 'комиссар');

            //приглашаем ведущего во все ветки
            $result = Yii::$app->bot->inviteUserToThread($mafThreadId, $hostDiscordId);
            $result = Yii::$app->bot->inviteUserToThread($donThreadId, $hostDiscordId);
            $result = Yii::$app->bot->inviteUserToThread($sheriffThreadId, $hostDiscordId);

            unset($gameMember);
            foreach ($gameMembers as $gameMember) {
                if($gameMember['role'] == \app\models\Game::ROLE_MAF) {
                    $result = Yii::$app->bot->inviteUserToThread($mafThreadId, $gameMember['discord_id']);
                }
                if($gameMember['role'] == \app\models\Game::ROLE_DON) {
                    $result = Yii::$app->bot->inviteUserToThread($mafThreadId, $gameMember['discord_id']);
                    $result = Yii::$app->bot->inviteUserToThread($donThreadId, $gameMember['discord_id']);
                }
                if($gameMember['role'] == \app\models\Game::ROLE_SHERIFF) {
                    $result = Yii::$app->bot->inviteUserToThread($sheriffThreadId, $gameMember['discord_id']);
                }
            }*/

//            return [$game, $gameMembers];
            return $game;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage(), 'create-game');
            throw new \Exception($e->getMessage());
        }
    }

    public function finishGame($game, $post)
    {
        try {
            date_default_timezone_set("Europe/Moscow");

            $hostUser = Yii::$app->user->getIdentity();
            $hostDiscordId = $hostUser->discordId;
//            $hostServerNick = ChannelMember::find()->where(['discord_id' => $hostDiscordId])->one()->name;
            $hostServerNick = $hostUser->username;
            $gameDatetime = date('d.m.Y H:i:s', $game->start_time);

            $gameStatus = $post['finishType'] == 'canceled' ? \app\models\Game::GAME_CANCELED : \app\models\Game::GAME_FINISHED;
            $winRole = $post['finishType'] == 'canceled' ? '' : $post['finishType'];

            $game->updateAttributes(['status' => $gameStatus,'end_time' => strval(time()), 'win_role' => $winRole]);

            $channelId = $game->channel_id;
            if(!empty($channelId)){
                Yii::$app->bot->deleteChannel($channelId);
            }

            $gameSettings = Yii::$app->Game->getGameSettings($game);

            if($gameSettings['isRating'] == 'true'){
                $result = Yii::$app->Game->writeRating($game);
                if(!$result){
                    throw new \Exception("Произошла ошибка при записи в БД рейтинга");
                }
            }

            $gameMembers = GameMember::find()->where(['game_id' => $game->id])->all();
            $hostEmbedText = '';
            /** @var $gameMember GameMember */
            foreach ($gameMembers as $gameMember) {

                $memberRating = MemberRating::find()->where(['discord_id' => $gameMember->discord_id, 'type' => MemberRating::RATING_GENERAL])->one();
                if(!empty($memberRating) && !empty($memberRating->rating)) {
                    $memberRating = $memberRating->rating;
                } else {
                    $memberRating = 0;
                }

                //TODO подправить
                if($gameSettings['isRating'] == 'true') {
//                    $memberRating = MemberRating::find()->where(['discord_id' => $gameMember->discord_id, 'type' => MemberRating::RATING_GENERAL])->one()->rating;
                    $memberRatingChange = MemberRatingHistory::find()->where(['game_id' => $game->id, 'discord_id' => $gameMember->discord_id])->one()->change_rating;
                    $memberRatingChange = intval($memberRatingChange) < 0 ? $memberRatingChange : '+'.$memberRatingChange;
                } else {
                    $memberRating = 0;
                    $memberRatingChange = 0;
                }

                $isMemberWin = \app\models\Game::isMemberWin($game->win_role, $gameMember->role);
                if($isMemberWin) {
                    $memberGames = GameMember::getMemberGames($gameMember);
                    $streak = $this->getWinStreak($gameMember, $memberGames);
                    $embedText = sprintf("Ты выиграл на роли **%s**.%sТвой рейтинг: **%s (%s)**.%sТвоя серия побед: **%s**.", \app\models\Game::getRoleInRus($gameMember->role), PHP_EOL, $memberRating, $memberRatingChange, PHP_EOL, $streak);
                    $hostEmbedText .= "{$gameMember->slot}. <@{$gameMember->discord_id}>, роль: **" . \app\models\Game::getRoleInRus($gameMember->role) ."**, рейтинг: **{$memberRating} ({$memberRatingChange})**, серия побед: **{$streak}**" . PHP_EOL;
                } else {
                    $embedText = sprintf("Ты проиграл на роли **%s**.%sТвой рейтинг: **%s (%s)**", \app\models\Game::getRoleInRus($gameMember->role), PHP_EOL, $memberRating, $memberRatingChange);
                    $hostEmbedText .= "{$gameMember->slot}. <@{$gameMember->discord_id}>, роль: **" . \app\models\Game::getRoleInRus($gameMember->role) ."**, рейтинг: **{$memberRating} ({$memberRatingChange})**" . PHP_EOL;

                }
                $memberEmbed = [
                    'title' => 'Победа ' . \app\models\Game::getGameResult($game->win_role),
                    'description' => $embedText,
                    'footer' => [
                        'text' => "Игра {$hostServerNick} от {$gameDatetime} (МСК)"
                    ],
                    'color' => '15724534' // Цвет в десятичном формате hex (пр. ff0000 -> 16711680)
                ];
                /*if($gameMember->discord_id != 162954416528293889) {
                    continue;
                }*/
                try {
                    Yii::$app->bot->changeUserNick($game->guild_id, $gameMember->discord_id, $gameMember->name, '');
                } catch (\Exception $e) {}

                try {
                    Yii::$app->bot->sendEmbed($gameMember->discord_id, $memberEmbed);
                } catch (\Exception $e) {
                    continue;
                }
            }

            // отправляем ведущему список игроков и ролей
            $embed = [
                'title' => 'Победа ' . \app\models\Game::getGameResult($game->win_role),
                'description' => $hostEmbedText,
                'footer' => [
                    'text' => "Игра {$hostServerNick} от {$gameDatetime} (МСК)"
                ],
                'color' => '15724534' // Цвет в десятичном формате hex (пр. ff0000 -> 16711680)
            ];

            Yii::$app->bot->sendEmbed($hostDiscordId, $embed);
            return true;
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), 'finish-game');
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
                    'guild_id' => strval($game->guild_id),
                    'change_rating' => strval($writeGuildRatingResult)
                ]);
                $memberRatingHistory->save();
            }
            return  true;
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), 'write-rating');
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
            $theBestMoveChange = isset($memberResult['the_best_move']['right_count']) ? intval($memberResult['the_best_move']['right_count']) : 0;

            $isMemberWin = $this->isMemberWin($gameMember, $memberGames[0]);
            if($isMemberWin) {
                $streak = $this->getWinStreak($gameMember, $memberGames);
            } else {
//                $streak = $this->getLoseStreak($gameMember, $memberGames);
                $streak = 0;
            }
            $streakCoeff = $this->getStreakCoeff($streak);

            if(!empty($memberResult['delete_reason'])){
                $foulsCoeff = in_array($memberResult['delete_reason'], [\app\models\Game::REASON_FOULED, \app\models\Game::REASON_TECH]) ? 3 : 0;
            } else {
                $foulsCoeff = 0;
            }

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
            $theBestMoveChange = isset($memberResult['the_best_move']['right_count']) ? intval($memberResult['the_best_move']['right_count']) : 0;

            $isMemberWin = $this->isMemberWin($gameMember, $memberGames[0]);
            if($isMemberWin) {
                $streak = $this->getWinStreak($gameMember, $memberGames);
            } else {
//                $streak = $this->getLoseStreak($gameMember, $memberGames);
                $streak = 0;
            }
            $streakCoeff = $this->getStreakCoeff($streak);

            if(!empty($memberResult['delete_reason'])){
                $foulsCoeff = in_array($memberResult['delete_reason'], [\app\models\Game::REASON_FOULED, \app\models\Game::REASON_TECH]) ? 3 : 0;
            } else {
                $foulsCoeff = 0;
            }

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
            $winStreak = 0;
            foreach ($memberGames as $memberGame) {
                $gameMember = GameMember::find()->where(['game_id' => $memberGame->id, 'discord_id' => $gameMember->discord_id])->one();
                $memberRole = in_array($gameMember->role, [\app\models\Game::ROLE_SHERIFF, \app\models\Game::ROLE_MIR]) ? \app\models\Game::ROLE_MIR : \app\models\Game::ROLE_MAF;
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