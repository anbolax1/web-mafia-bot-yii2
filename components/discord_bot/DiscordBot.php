<?php
namespace app\components\discord_bot;

use Discord\Discord;
use Discord\Parts\Channel\Message;
use GuzzleHttp\Client;
use Yii;

class DiscordBot
{
    public $discord;

    public function sendRequest($baseUri = 'https://discord.com/api/v9/', $method, $url, $headers = [], $body = null)
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

            $response = $this->sendRequest('','POST', "channels/{$channelId}/messages", [], $body);

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
                $channels = $this->sendRequest('https://discord.com/api/v10/', 'GET', "guilds/$guildId/channels");

                foreach ($channels as $channel) {
                    if($channel['type'] !== 2) {
                        continue;
                    } else {
//                        $response = $this->sendRequest('https://discord.com/api/v10/', 'GET', "guilds/$guildId/channels/{$channel['id']}/members");
                        $response = $this->sendRequest('https://discord.com/api/v10/', 'GET', "guilds/{$guildId}/members");
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
            $response = $this->sendRequest('https://discord.com/api/v10/', 'GET', "users/@me/guilds");

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
            $channels = $this->sendRequest('https://discord.com/api/v10/', 'GET', "guilds/$guildId/channels");

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

    public function changeUserNick($guildId, $userDiscordId, $oldNick, $slot)
    {
        try {
            if($oldNick[2] == '.'){
                $oldNick = trim(substr($oldNick, 3));
            }
            $newNick = $slot . '. ' . $oldNick;

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
            $response = $this->sendRequest('https://discord.com/api/', 'get', "oauth2/applications/@me");
        }
        catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}