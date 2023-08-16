<?php

namespace app\controllers;

use app\models\ChannelMember;
use app\models\GameMember;
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
            $game = Yii::$app->user->getIdentity()->getGameInProcess();
            if(!empty($game)){
                return $this->redirect(['game', 'game' => $game]);
            }

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
            return $this->render('index');
        }
    }

    public function actionCreateGame()
    {
        try {
            $post = $_POST;
            $gameMembers = $post['members'];
            $gameSettings = $post['settings'];

            $game = Yii::$app->Game->createGame($gameSettings, $gameMembers);

            return $this->render('game', ['game' => $game]);
//            return $this->render('/game/game');
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->render('starting');
        }
    }

    public function actionGame(): string
    {
        try {
            $game = Yii::$app->user->getIdentity()->getGameInProcess();
            return $this->render('game', ['game' => $game]);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->render('starting');
        }
    }

    public function actionDeleteMemberFromGame()
    {
        try {
            $post = $_POST;
            $gameMemberModel = GameMember::find()->where(['game_id' => $post['gameId'], 'discord_id' => $post['discordId']])->one();
            if(empty($gameMemberModel)){
                throw  new \Exception("Участник игры в базе не найден!");
            }
            //TODO
        } catch (\Exception $e) {
            return json_encode(['message' => $e->getMessage()]);
        }
    }
}
