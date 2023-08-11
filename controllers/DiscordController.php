<?php

namespace app\controllers;

use app\models\DiscordUser;
use app\models\User;
use Yii;

use yii\base\BaseObject;

use function PHPUnit\Framework\isTrue;

class DiscordController extends \yii\web\Controller
{
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    public function actionLogin()
    {
        try {
            $get = $_GET;

            $result = Yii::$app->DiscordUser->getTokens($get);

            [$tokens, $discordUser] = Yii::$app->DiscordUser->getDiscordUser($result);

            $discordUserModel = \app\models\DiscordUser::find()->where(['discord_id' => $result['id']])->one();
            if(!isset($discordUserModel)){
                $user = Yii::$app->DiscordUser->createUser($tokens, $discordUser);
            } else {
                $user = User::find()->where(['id' => $discordUserModel->user_id])->one();
            }

            Yii::$app->user->login($user, 3600*24*30);

            return $this->redirect('index');


            /*session_start();
            $_SESSION['logged_in'] = true;
            $_SESSION['userData'] = [
                'name' => $result['username'],
                'discord_id' => $result['discord_id'],
                'avatar' => $result['avatar'],
            ];*/



        } catch (\Exception $e) {
            \Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect('/discord/index');
        }

    }

    public function actionDiscordAuth()
    {
        return $this->render('index');
    }


}
