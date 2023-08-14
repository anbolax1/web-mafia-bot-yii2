<?php

namespace app\controllers;

use app\models\ChannelMember;
use app\models\DiscordUser;
use app\models\Guild;
use app\models\User;
use Yii;
use yii\base\BaseObject;
use yii\console\Application;
use GuzzleHttp\Client;
use yii\console\ExitCode;

use function RingCentral\Psr7\str;


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
//            $bot = Yii::$app->bot->getCurrentChannelMembers();
//            $bot = Yii::$app->bot->sendTestMessage();
            return $this->redirect('index');
        } catch (\Exception $e) {
            \Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect('index');
        }
    }

    public function actionStarting()
    {
        try {
            $hostUser = Yii::$app->user->getIdentity();
            $hostDiscordId = $hostUser->discordId;
            $hostChannelMember = ChannelMember::find()->where(['discord_id' => $hostDiscordId])->one();
            if(!empty($hostChannelMember)){
                $channelMembers = ChannelMember::find()
                    ->where(['channel_id' => $hostChannelMember->channel_id])
                    ->andWhere(['<>', 'discord_id', $hostDiscordId])
                    ->all();
            } else {
                throw new \Exception("Пожалуйста, зайдите в голосовой канал!");
            }
            return $this->render('starting', ['host' => $hostChannelMember, 'members' => $channelMembers]);
//        return $this->redirect(["starting", 'id' => $payroll_model->id]);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->render('starting');
        }
    }

    public function actionStartGame()
    {
        try {
            $get = $_GET;
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->render('starting');
        }
    }
}
