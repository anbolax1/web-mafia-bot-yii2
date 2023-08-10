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

            if(!isset($get['code'])){
                throw new \Exception('No code!');
            }

            $discordCode = $get['code'];

            $payload = [
                'code' => $discordCode,
                'client_id' => env('DISCORD_CLIENT_ID'),
                'client_secret' => env('DISCORD_CLIENT_SECRET'),
                'grant_type' => 'authorization_code',
                'redirect_uri' => env('DISCORD_REDIRECT_URI'),
                'scope' => 'identify%20guilds',
            ];

            $payloadString = http_build_query($payload);
            $discordTokenUrl = "https://discordapp.com/api/oauth2/token";

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $discordTokenUrl);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payloadString);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

            $result = curl_exec($curl);
            curl_close($curl);

            $result = json_decode($result, true);

            if(!empty($result['error'])){
                throw new \Exception("{$result['error']}: {$result['error_description']}");
            }
            if(!$result){
                throw new \Exception(curl_error($curl));
            }

            $accessToken = $result['access_token'];

            $discordUsersUrl = "https://discordapp.com/api/users/@me";
            $header = array("Authorization: Bearer $accessToken", "Content-Type: application/x-www-form-urlencoded");

            $curl = curl_init();

            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_URL, $discordUsersUrl);
            curl_setopt($curl, CURLOPT_POST, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

            $result = curl_exec($curl);

            $result = json_decode($result, true);

            $discordUser = DiscordUser::find()->where(['discord_id' => $result['id']])->one();
            if(!isset($discordUser)){
                $user = $this->createUser($result);
            } else {
                $user = User::find()->where(['id' => $discordUser->user_id])->one();
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

    public function createUser($result)
    {
        try {
            $transaction = Yii::$app->db->beginTransaction();

            $user = new User([
                 'username' => $result['username'],
                 'email' => $result['username'],
                 'role' => User::ROLE_USER,
                 'created_at' => time(),
                 'updated_at' => time(),
             ]);
            $password = Yii::$app->security->generateRandomString(8);
            $user->setPassword($password);
            $user->generateAuthKey();

            $user->save();

            $discordUser = new DiscordUser([
               'user_id' => $user->id,
               'discord_id' => $result['id'],
               'username' => $result['username'],
               'avatar' => $result['avatar'],
           ]);

            $discordUser->save();

            $transaction->commit();
            return $user;
        } catch (\Exception $e) {
            if(isset($transaction)){
                $transaction->rollback();
            }
            Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect('site/login');
        }
    }
}
