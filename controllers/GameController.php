<?php

namespace app\controllers;

use app\models\ChannelMember;
use app\models\Game;
use app\models\GameHistory;
use app\models\GameMember;
use app\models\Meta;
use yii\base\BaseObject;
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
        if(!empty($_GET) && !empty($_GET['redirect_to_starting']) && $_GET['redirect_to_starting'] == 'true') {
            return $this->render('index', ['redirect_to_starting' => 'true']);
        } else {
            return $this->render('index');
        }
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
            /*if(!empty($_GET) && !empty($_GET['status']) && $_GET['status'] == 'wait') {
                sleep(10);
            }*/

            $metaModel = Meta::find()->where(['key' => Meta::IS_UPDATE_CHANNEL_MEMBERS])->one();
            if(!empty($metaModel)){
                $metaModel->updateAttributes(['timestamp' => strval(time())]);
            } else {
                $metaModel = new Meta([
                    'key' => Meta::IS_UPDATE_CHANNEL_MEMBERS,
                    'value' => 'true',
                    'timestamp' => strval(time())
                ]);
                $metaModel->save();
            }

            $game = Yii::$app->user->getIdentity()->getGameInProcess();
            if(!empty($game)){
                return $this->redirect(['game', 'game' => $game]);
            }

            $hostUser = Yii::$app->user->getIdentity();
            $hostDiscordId = $hostUser->discordId;
            try {
                $hostChannelMember = ChannelMember::find()->where(['discord_id' => $hostDiscordId])->one();
                if(!empty($hostChannelMember)){
                    $channelMembers = ChannelMember::find()
                        ->where(['channel_id' => $hostChannelMember->channel_id])
                        ->andWhere(['<>', 'discord_id', $hostDiscordId])
                        ->all();
                } else {
                    throw new \Exception("Пожалуйста, зайдите в голосовой канал, если ещё не зашли, и подождите 10 секунд, страница обновится сама!");
                }
            } catch (\Exception $e) {
//                sleep(10);
                Yii::$app->session->setFlash('error', $e->getMessage());
                return $this->redirect(['index', 'redirect_to_starting' => 'true']);
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

    public function actionGame()
    {
        try {
            $game = Yii::$app->user->getIdentity()->getGameInProcess();
            if(!empty($game)){
                return $this->render('game', ['game' => $game]);
            } else {
                return $this->redirect('starting');
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->render('starting');
        }
    }

    public function actionDeleteMemberFromGame()
    {
        try {
            $post = $_POST;
            $gameMemberModel = GameMember::find()->where(['game_id' => $post['gameId'], 'discord_id' => $post['memberDiscordId']])->one();
            if(empty($gameMemberModel)){
                throw  new \Exception("Участник игры в базе не найден!");
            }
            $gameMember = GameMember::find()->where(['game_id' => $post['gameId'], 'discord_id' => $post['memberDiscordId']])->one();
            if(empty($gameMember)){
                throw new \Exception("В игре (id = {$post['gameId']}) не найден участник (discord_id = {$post['memberDiscordId']})");
            }
            $gameMemberResult = json_decode($gameMember->result, true);

            if($post['toDelete'] == 'true'){
                $gameHistory = new GameHistory([
                   'game_id' => $post['gameId'],
                   'member_discord_id' => strval($post['memberDiscordId']),
//                   'description' => Game::getGameActionDescription($post['deleteReason']),
                   'description' => $post['deleteReason'],
                   'time' => strval(time())
                ]);
                if(!$gameHistory->save()){
                    throw new \Exception("В историю игры (id = {$post['gameId']} не записано удаление игрока {$post['memberDiscordId']}");
                }

                $gameMemberResult['is_deleted'] = 'true';
                $gameMemberResult['delete_reason'] = $post['deleteReason'];
                $gameMemberResult['fouls_count'] = $post['foulsCount'];
                $gameMemberResult['killed_first'] = $post['killedFirst'];
            } else {
                GameHistory::deleteAll(['game_id' => $post['gameId'], 'member_discord_id' => $post['memberDiscordId']]);
//                unset($gameMemberResult['is_deleted'], $gameMemberResult['delete_reason'], $gameMemberResult['fouls_count'], $gameMemberResult['killed_first']);
                $gameMemberResult = [];
            }
            $gameMember->updateAttributes(['result' => json_encode($gameMemberResult, JSON_UNESCAPED_UNICODE)]);

        } catch (\Exception $e) {
            return json_encode(['message' => $e->getMessage()]);
        }
    }

    public function actionWriteTheBestMove()
    {
        try {
            $post = $_POST;
            $gameMembers = GameMember::find()->where(['game_id' => $post['gameId']])->all();
            if(empty($gameMembers)){
                throw  new \Exception("Участники игры (id = {$post['gameId']}) не найдены в базе!");
            }
            $theBestMove = [
                'slots' => [],
                'rightCount' => 0
            ];
            foreach ($gameMembers as $gameMember) {
                if(in_array($gameMember->role, ['maf', 'don']) && in_array($gameMember->slot, $post['theBestMoveSlots'])){
                    array_push($theBestMove['slots'], $gameMember->slot);
                    $theBestMove['rightCount']++;
                }
            }

            $gameMemberModel = GameMember::find()->where(['game_id' => $post['gameId'], 'discord_id' => $post['memberDiscordId']])->one();
            if(empty($gameMemberModel)){
                throw  new \Exception("Участник игры в базе не найден!");
            }

            $gameMemberResult = json_decode($gameMemberModel->result, true);
            $gameMemberResult['the_best_move'] = [
                'slots' => $post['theBestMoveSlots'],
                'right_slots' => $theBestMove['slots'],
                'right_count' => $theBestMove['rightCount']
            ];

            $gameMemberModel->updateAttributes(['result' => json_encode($gameMemberResult, JSON_UNESCAPED_UNICODE)]);

            $gameHistory = new GameHistory([
                'game_id' => $post['gameId'],
                'member_discord_id' => strval($post['memberDiscordId']),
                'description' => Game::THE_BEST_MOVE,
                'time' => strval(time())
            ]);
            if(!$gameHistory->save()){
                throw new \Exception("В историю игры (id = {$post['gameId']} не записан ЛХ игрока {$post['memberDiscordId']}");
            }
        } catch (\Exception $e) {
            return json_encode(['message' => $e->getMessage()]);
        }
    }

    public function actionFinishGame()
    {
        try {
            $post = $_POST;
            $game = Game::find()->where(['id' => $post['gameId']])->one();
            if(empty($game)){
                throw new \Exception("Игра (id = {$post['gameId']}) не найдена в базе!");
            }

            $result = Yii::$app->Game->finishGame($game, $post);

            if($post['finishType'] == 'canceled') {
                return $this->render('starting');
            } else {
                return $this->render('finish', ['game' => $game]);
            }
        } catch (\Exception $e) {
            return json_encode(['message' => $e->getMessage()]);
        }
    }

    public function actionFinish()
    {
        try {
            $game = Yii::$app->user->getIdentity()->getFinishedGame();
            return $this->render('finish', ['game' => $game]);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->render('starting');
        }
    }

    public function actionTest()
    {
        try {
            $result = Yii::$app->bot->sendMessage();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
