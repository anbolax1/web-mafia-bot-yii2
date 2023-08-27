<?php

namespace app\commands;

use app\components\discord_bot\DiscordBot;
use app\models\ChannelMember;
use app\models\Guild;
use app\models\Meta;
use app\models\VoiceChannel;
use Discord\Parts\Channel\Channel;
use Yii;
use yii\base\BaseObject;
use yii\console\Controller;
use yii\console\ExitCode;
use Discord\Discord;
use Discord\Parts\Channel\Message;

class BotController extends Controller
{
    public function actionSaveChannelMembers()
    {
        $meta = Meta::find()->where(['key' => Meta::IS_UPDATE_CHANNEL_MEMBERS])->one();
        if(time() - intval($meta->timestamp) > 180) {
            exit();
        }
        $token = env('BOT_TOKEN');
        $discord = new Discord(
            [
                'token' => $token
            ]
        );
        //0. Этот скрипт должен выполняться каждые 5 секунд.
        //1. Достаём из базы все серверы, к которым подключен бот;
        //2. Из этого сервера достаём все голосовые каналы;
        //3. Для каждого голосового канала получаем участников;
        //4. Сохраняем в базу каналы с участниками

        $discord->on('ready',function ($discord) {
            try {
                //0. Удаляем все записи из таблиц voice_channel и channel_member
                VoiceChannel::deleteAll();
                ChannelMember::deleteAll();
                $isError = false;
                $guilds = Guild::find()->all();
                if(empty($guilds)){
                    $isError = true;
                    throw new \Exception("Серверы в базе не найдены!");
                }
                foreach ($guilds as $guild) {
                    $discordGuild = $discord->guilds->get('id', $guild->discord_id);
                    $voiceChannels = json_decode($guild['voice_channels'], true);
                    if(empty($voiceChannels)){
                        $isError = true;
                        throw new \Exception("Голосовые каналы в базе не найдены!");
                    }
                    foreach ($voiceChannels as $voiceChannelId){
                        $discordChannel = $discordGuild->channels->get('id', $voiceChannelId);
                        $members = $discordChannel->members;
                        if(!empty($members)){
                            $voiceChannelModel = new VoiceChannel([
                               'discord_id' => strval($voiceChannelId),
                               'guild_id' => $guild->id
                            ]);
                            $voiceChannelModel->save();
                            foreach ($members as $memberDiscordId => $member) {
//                                file_put_contents('members.log', print_r(json_encode($member->member->nick, JSON_UNESCAPED_UNICODE) . PHP_EOL, true), FILE_APPEND);
                                $name = preg_replace('/[^a-zA-Zа-яА-Я0-9\s\p{P}]+/u', '', $member->member->nick);
                                if(empty($name)){
                                    $name = preg_replace('/[^a-zA-Zа-яА-Я0-9\s\p{P}]+/u', '', $member->member->user->username);
                                }

                                if(strlen($name) > 3) {
                                    if($name[2] == '.'){
                                        $name = trim(substr($name, 3));
                                    }
                                }

                                $name = str_replace("!Вед.", '', $name);
                                $name = str_replace("Зр.", '', $name);
                                $name = trim($name);

//                                file_put_contents('members.log', print_r(json_encode($name, JSON_UNESCAPED_UNICODE) . PHP_EOL, true), FILE_APPEND);
                                $channelMemberModel = new ChannelMember([
                                    'discord_id' => strval($member->member->user->id),
                                    'name' => $name,
                                    'avatar' => $member->member->user->avatar,
                                    'channel_id' => $voiceChannelModel->id
                                ]);
                                unset($name);
                                $channelMemberModel->save();
                            }
                        }
                    }
                }
                if(!$isError){
                    $discord->close();
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
                return ExitCode::OK;
            }
        });
    }
}
