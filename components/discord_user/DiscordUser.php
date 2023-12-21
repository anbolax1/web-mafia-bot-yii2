<?php

namespace app\components\discord_user;

use app\models\DiscordUser as DiscordUserModel;
use app\models\User;
use GuzzleHttp\Client;
use Yii;

class DiscordUser
{
    public function getTokens($get)
    {
        try {
            if (!isset($get['code'])) {
                throw new \Exception('No code!');
            }

            $discordCode = $get['code'];

            $payload = [
                'code' => $discordCode,
                'client_id' => env('DISCORD_CLIENT_ID'),
                'client_secret' => env('DISCORD_CLIENT_SECRET'),
                'grant_type' => 'authorization_code',
                'redirect_uri' => env('DISCORD_REDIRECT_URI'),
//                'scope' => 'identify%20guilds%20guilds.join%20guilds.members.read',
                'scope' => 'identify%20guilds%20guilds.members.read',
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

            if (!empty($result['error'])) {
                throw new \Exception("{$result['error']}: {$result['error_description']}");
            }
            if (!$result) {
                throw new \Exception(curl_error($curl));
            }

            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function checkToken($tokens)
    {
        try {
            $accessToken = $tokens['access_token'];
            $refreshToken = $tokens['refresh_token'];
            $updatedAt = $tokens['updated_at'];

            $currentTimestamp = time();
            if ($currentTimestamp - $updatedAt > 300000) {
                $tokens = $this->updateToken($tokens);

                $discordUserModel = DiscordUserModel::find()->where(['access_token' => $accessToken])->one();
                if (!empty($discordUserModel)) {
                    $discordUserModel->updateAttributes([
                        'access_token' => $tokens['access_token'],
                        'updated_at' => time()
                    ]);
                }
            }
            return !empty($discordUserModel) ? $discordUserModel->access_token : $accessToken;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function updateToken($tokens)
    {
        $accessToken = $tokens['access_token'];
        $refreshToken = $tokens['refresh_token'];

        $data = array(
            'client_id' => env('DISCORD_CLIENT_ID'),
            'client_secret' => env('DISCORD_CLIENT_SECRET'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'redirect_uri' => env('DISCORD_REDIRECT_URI'),
            'scope' => 'identify', // запрашиваемые разрешения
        );

        $options = array(
            CURLOPT_URL => 'https://discord.com/api/v10/oauth2/token',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
        );

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        curl_close($curl);

        $accessTokenData = json_decode($response, true);
        $accessToken = $accessTokenData['access_token'];

        return ['access_token' => $accessToken, 'refresh_token' => $accessTokenData['refresh_data']];
    }

    public function getDiscordUser($result)
    {
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

        $discordUser = curl_exec($curl);

        $discordUser = json_decode($discordUser, true);

        return [$result, $discordUser];
    }

    public function createUser($tokens, $discordUser)
    {
        try {
            $transaction = Yii::$app->db->beginTransaction();

            $user = new User(
                [
                    'username' => $discordUser['username'],
                    'email' => $discordUser['username'],
                    'role' => User::ROLE_USER,
                    'created_at' => time(),
                    'updated_at' => time(),
                ]
            );
            $password = Yii::$app->security->generateRandomString(8);
            $user->setPassword($password);
            $user->generateAuthKey();

            $user->save();

            $discordUser = new DiscordUserModel(
                [
                    'user_id' => $user->id,
                    'discord_id' => $discordUser['id'],
                    'username' => $discordUser['username'],
                    'avatar' => $discordUser['avatar'],
                    'access_token' => $tokens['access_token'],
                    'refresh_token' => $tokens['refresh_token'],
                    'updated_at' => time()
                ]
            );

            $discordUser->save();

            $transaction->commit();
            return $user;
        } catch (\Exception $e) {
            if (isset($transaction)) {
                $transaction->rollback();
            }
            throw new \Exception($e->getMessage());
        }
    }

    public function sendRequest($userToken, $baseUri = 'https://discord.com/api/v10/', $method = 'GET', $url, $headers = [], $body = null)
    {
        try {
            $headers = empty($headers) ? [
                'Authorization' => 'Bearer ' . $userToken,
                'Content-Type' => 'application/json'
            ] : $headers;

            $client = new Client([
                 'base_uri' => $baseUri,
                 'headers' => $headers,
             ]);

            $response = $client->request($method, $url, [
                'body' => $body,
            ]);

            return json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getCurrentGuild()
    {
        try {
            $userDiscordId = Yii::$app->user->getIdentity()->discordId;
            $discordUserModel = DiscordUserModel::find()->where(['discord_id' => $userDiscordId])->one();
            if(!empty($discordUserModel)){

                $userToken = $discordUserModel->access_token;
                $refreshToken = $discordUserModel->access_token;
                $userToken = $this->checkToken(['access_token' => $userToken, 'refresh_token' => $refreshToken, 'updated_at' => $discordUserModel->updated_at]);
                $result = $this->sendRequest($userToken, 'https://discord.com/api/v9/', 'GET', "users/@me");
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}