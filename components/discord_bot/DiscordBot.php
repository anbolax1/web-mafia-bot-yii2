<?php
namespace app\components\discord_bot;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use GuzzleHttp\Client;
use Yii;

class DiscordBot
{
    public $discord;

    public function sendRequest($method, $url, $baseUri = 'https://discord.com/api/v9/', $headers = [], $body = null)
    {
        try {
            $headers = empty($headers) ? [
                'Authorization' => 'Bot ' . env('BOT_TOKEN'),
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

    public function sendTestMessage()
    {
        try {
            $channelId = 1046294793157885996;
            $message = 'СРАБОТАЙ ПОЖАЛУЙСТА!!!';

            $body = json_encode([
                'content' => $message,
            ]);

            $response = $this->sendRequest('POST', "channels/{$channelId}/messages", '', [], $body);

            if (isset($response['id'])) {
                echo 'Message sent successfully!';
            } else {
                echo 'Failed to send message.';
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getCurrentChannelMembers()
    {
        try {
            $userId = Yii::$app->user->getIdentity()->discordId;

            $botGuilds = $this->getBotGuilds();

            foreach ($botGuilds as $botGuild) {
                $guildId = $botGuild['id'];
                $channels = $this->sendRequest('GET', "guilds/$guildId/channels", 'https://discord.com/api/v10/');

                foreach ($channels as $channel) {
                    if($channel['type'] !== 2) {
                        continue;
                    } else {
//                        $response = $this->sendRequest('https://discord.com/api/v10/', 'GET', "guilds/$guildId/channels/{$channel['id']}/members");
                        $response = $this->sendRequest(
                            'GET',
                            "guilds/{$guildId}/members",
                            'https://discord.com/api/v10/'
                        );
                    }
                }

//                $voiceChannelId = $data['voice_channel_id'];
            }


            if ($response) {
                $channels = json_decode($response, true);
                foreach ($channels as $channel) {
                    if ($channel['type'] == 2) {
                        $voiceChannelId = $channel['id'];
                        // Используйте полученный идентификатор голосового канала
                        break;
                    }
                }
            } else {
                // Обработка ошибки
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getBotGuilds()
    {
        try {
            $response = $this->sendRequest('GET', "users/@me/guilds", 'https://discord.com/api/v10/');

            if($response){
                return $response;
            } else {
                throw new \Exception("Бот не подключен ни к одному серверу!");
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getBotGuildVoiceChannels($guildId): array
    {
        try {
            $channels = $this->sendRequest('GET', "guilds/$guildId/channels", 'https://discord.com/api/v10/');

            $resultChannels = [];
            if($channels){
                foreach ($channels as $channel) {
                    if($channel['type'] == 2){
                        $resultChannels[] = $channel['id'];
                    }
                }
                return $resultChannels;
            } else {
                throw new \Exception("Не найдены голосовые каналы!");
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function changeUserNick($guildId, $userDiscordId, $oldNick, $slot = '')
    {
        try {
            if($oldNick[2] == '.'){
                $oldNick = trim(substr($oldNick, 3));
            }
            if(!empty($slot)){
                $newNick = $slot . '. ' . $oldNick;
            } else {
                $newNick = $oldNick;
            }

            $baseUri = 'https://discord.com/api/v9/';

            $headers = empty($headers) ? [
                'Authorization' => 'Bot ' . env('BOT_TOKEN'),
                'Content-Type' => 'application/json'
            ] : $headers;

            $client = new Client([
                'base_uri' => $baseUri,
                'headers' => $headers,
            ]);


            $response = $client->patch("guilds/{$guildId}/members/{$userDiscordId}", [
                'json' => [
                    'nick' => $newNick,
                ],
            ]);


            if ($response->getStatusCode() === 200) {
                echo "Nickname changed successfully!";
            } else {
                echo "Failed to change nickname.";
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function sendMessage($userId, $message)
    {
        try {
            $client = new Client([
                 'base_uri' => 'https://discord.com/api/',
                 'headers' => [
                     'Authorization' => 'Bot ' . env('BOT_TOKEN'),
                     'Content-Type' => 'application/json',
                 ],
             ]);

            $response = $client->post('users/@me/channels', [
                'json' => [
                    'recipient_id' => $userId,
                ],
            ]);

            $channelId = json_decode($response->getBody(), true)['id'];

            $response = $client->post("channels/{$channelId}/messages", [
                'json' => [
                    'content' => $message
                ],
            ]);
        }
        catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function sendEmbed($userDiscordId, $embed)
    {
        try {
            $client = new Client([
                 'base_uri' => 'https://discord.com/api/',
                 'headers' => [
                     'Authorization' => 'Bot ' . env('BOT_TOKEN'),
                     'Content-Type' => 'application/json',
                 ],
             ]);

            $response = $client->post('users/@me/channels', [
                'json' => [
                    'recipient_id' => $userDiscordId,
                ],
            ]);

            $channelId = json_decode($response->getBody(), true)['id'];

            // Создание сообщения-эмбед
            $message = [
                'embed' => $embed
            ];

            $response = $client->post("channels/{$channelId}/messages", [
                'json' => [
                    'embed' => $message['embed']
                ],
            ]);

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function getBotApplications()
    {
        try {
            $response = $this->sendRequest('get', "oauth2/applications/@me", 'https://discord.com/api/');
        }
        catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function createTextChannel($guildId, $categoryId, $channelName, $params = [])
    {
        try {
            $client = new Client([
                 'base_uri' => 'https://discord.com/api/v9/',
                 'headers' => [
                     'Authorization' => 'Bot ' . env('BOT_TOKEN'),
                     'Content-Type' => 'application/json',
                 ],
             ]);

            // Создание текстового канала
            $response = $client->post("guilds/{$guildId}/channels", [
                'json' => [
                    'name' => $channelName,
                    'type' => 0, // 0 - текстовый канал
                    /*'permission_overwrites' => [
                        [
                            'id' => '162954416528293889',
                            'type' => 'member',
                            'allow' => 3072, // Разрешение для пользователя с Discord ID 162954416528293889
                        ],
                        [
                            'id' => '789239526396395523',
                            'type' => 'member',
                            'allow' => 3072, // Разрешение для пользователя с Discord ID 162954416528293889
                        ],
                    ],*/
                    'parent_id' => $categoryId, // ID категории, в которую нужно добавить канал
                ],
            ]);

            // Получение ответа от Discord API
            $body = json_decode($response->getBody(), true);

            return $body['id'];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function createThread($channelId, $threadName, $params = [])
    {
        try {
            // Создаем экземпляр клиента Guzzle
            $client = new Client([
                 'base_uri' => 'https://discord.com/api/v9/',
                 'headers' => [
                     'Authorization' => 'Bot ' . env('BOT_TOKEN'),
                     'Content-Type' => 'application/json',
                 ],
             ]);

            // Создаем приватную ветку
            $response = $client->post("channels/{$channelId}/threads", [
                'json' => [
                    'name' => $threadName,
                    'type' => 12, // 12 для приватной ветки
                ],
            ]);

            $threadData = json_decode($response->getBody(), true);

            if ($response->getStatusCode() === 201) {
                $threadId = $threadData['id'];
                return $threadId;
            } else {
                throw new \Exception("Произошла ошибка при создании ветки!");
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function inviteUserToThread($threadId, $userId)
    {
        try {
            // Создаем экземпляр клиента Guzzle
            $client = new Client([
                 'base_uri' => 'https://discord.com/api/v9/',
                 'headers' => [
                     'Authorization' => 'Bot ' . env('BOT_TOKEN'),
                     'Content-Type' => 'application/json',
                 ],
             ]);

            // Добавляем участников в ветку
            $response = $client->put("channels/{$threadId}/thread-members/{$userId}");
            if ($response->getStatusCode() === 204) {
                return true;
            } else {
                throw new \Exception("Участник не добавлен в ветку!");
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function deleteChannel($channelId)
    {
        try {
            // Создаем экземпляр клиента Guzzle
            $client = new Client([
                 'base_uri' => 'https://discord.com/api/v9/',
                 'headers' => [
                     'Authorization' => 'Bot ' . env('BOT_TOKEN'),
                     'Content-Type' => 'application/json',
                 ],
             ]);

            // Добавляем участников в ветку
            $response = $client->delete("channels/{$channelId}");

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    //todo это не работает
    public function kickMember($userId)
    {
        $token = env('BOT_TOKEN');
        $guildId = 803807947066703883;
        $voiceChannelId = 1106277407792566382;

        $client = new Client([
             'base_uri' => 'https://discord.com/api/v9/',
             'headers' => [
                 'Authorization' => 'Bot ' . $token,
                 'Content-Type' => 'application/json'
             ]
         ]);

        $response = $client->request('DELETE', "guilds/{$guildId}/voice-states/{$userId}", [
            'json' => [
                'channel_id' => $voiceChannelId
            ]
        ]);

        if ($response->getStatusCode() === 204) {
            echo "Member kicked from the voice channel successfully.";
        } else {
            echo "Failed to kick member from the voice channel.";
        }
    }
}