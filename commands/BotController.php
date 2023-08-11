<?php

namespace app\commands;

use app\components\discord_bot\DiscordBot;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class BotController extends Controller
{
    public function actionIndex()
    {
        $token = env('BOT_TOKEN');
        $bot = new DiscordBot($token);
        $bot->run();

        return $this->render('index');
    }
}
