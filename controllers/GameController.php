<?php

namespace app\controllers;

use app\models\ChannelMember;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use Yii;

//use function RingCentral\Psr7\str;


class GameController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                ],
            ]
        );
    }

    public function actionIndex(): string
    {
//        Yii::$app->bot->sendMessage(1046294793157885996, 'Hello!');
        return $this->render('game');
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

    public function actionGame(): string
    {
        try {
            /*$post = $_POST;
            $gameMembers = $post['members'];
            $gameSettings = $post['settings'];

            [$game, $gameMembers] = Yii::$app->Game->startGame($gameSettings, $gameMembers);*/

            return $this->render('/game/index');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->render('starting');
        }
    }

    public function actionTest()
    {

        $this->render('/game/index');
    }
}
