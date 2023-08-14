<?php

namespace app\components\game;

use app\models\ChannelMember;
use app\models\DiscordUser as DiscordUserModel;
use app\models\GameMember;
use app\models\GameSetting;
use app\models\User;
use GuzzleHttp\Client;
use Yii;

class Game
{
    public function startGame($settings, $gameMembers)
    {
        try {
            $transaction = Yii::$app->db->beginTransaction();

            $hostUser = Yii::$app->user->getIdentity();
            $game = new \app\models\Game([
                'host_id' => $hostUser->getId(),
                'guild_id' => $this->getGuildId($hostUser),
                'status' => \app\models\Game::GAME_IN_PROCESS,
                'start_time' => time()
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
            return [$game, $gameMembers];
        } catch (\Exception $e) {
            $transaction->rollBack();
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

    public function prepareGameSettings($settings)
    {
        try {

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}