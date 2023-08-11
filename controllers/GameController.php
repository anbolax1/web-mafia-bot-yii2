<?php

namespace app\controllers;

use app\models\DiscordUser;
use app\models\User;
use Yii;
use yii\base\BaseObject;
use yii\console\Application;
use GuzzleHttp\Client;


class GameController extends \yii\web\Controller
{
    public function actionIndex(): string
    {
//        Yii::$app->bot->sendMessage(1046294793157885996, 'Hello!');
        return $this->render('index');
    }

    public function actionSend()
    {
        try {
//            $result = Yii::$app->DiscordUser->getCurrentGuild();
            $bot = Yii::$app->bot->getCurrentChannelMembers();
            $bot = Yii::$app->bot->sendTestMessage();
        } catch (\Exception $e) {
            \Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect('index');
        }
    }

}
