<?php

namespace app\commands;

use app\components\discord_bot\DiscordBot;
use app\models\Guild;
use Yii;
use yii\base\BaseObject;
use yii\console\Controller;
use yii\console\ExitCode;
use Discord\Discord;
use Discord\Parts\Channel\Message;

class WebBotController extends Controller
{
    public function actionIndex()
    {
        try {
            $guildsVoiceChannels = [];
            $botGuilds = Yii::$app->bot->getBotGuilds();

            if (!empty($botGuilds)) {
                foreach ($botGuilds as $botGuild) {
                    $botGuildVoiceChannels = Yii::$app->bot->getBotGuildVoiceChannels($botGuild['id']);

                    if (!empty($botGuildVoiceChannels)) {
                        $guildsVoiceChannels[$botGuild['id']]['channels'] = $botGuildVoiceChannels;
                        $guildsVoiceChannels[$botGuild['id']]['guild'] = $botGuild['id'];
                        $guildsVoiceChannels[$botGuild['id']]['name'] = $botGuild['name'];
                    }
                }
            }

            if (!empty($guildsVoiceChannels)) {
                foreach ($guildsVoiceChannels as $guildId => $guildsVoiceChannel) {
                    $guildModel = Guild::find()->where(['discord_id' => $guildId])->one();
                    if (!empty($guildModel)) {
//                        $guildModel->delete();
                        $guildModel->updateAttributes(['voice_channels' => json_encode($guildsVoiceChannel['channels'], JSON_UNESCAPED_UNICODE)]);
                    } else {
                        $guildModel = new Guild([
                            'discord_id' => strval($guildId),
                            'name' => preg_replace('/[^a-zA-Zа-яА-Я0-9\s\p{P}]+/u', '', $guildsVoiceChannel['name']),
                            'voice_channels' => json_encode($guildsVoiceChannel['channels'], JSON_UNESCAPED_UNICODE)
                        ]);
                    }

                    if(!$guildModel->save()){
                        throw new \Exception("Гильдия не сохранена в базу!");
                    }
                }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            return ExitCode::OK;
        }
    }
}
