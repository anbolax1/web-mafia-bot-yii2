<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'KzVmz102Wc5_yPddhdPHB2RGZZiQUMru',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            // send all mails to a file by default.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'exportInterval' => 1,
                    'logFile' => '@runtime/logs/web/game/finish-game.log',
                    'logVars' => [],
                    'levels' => ['error', 'info'],
                    'categories' => ['finish-game'],
                    'prefix' => function($message) {
                        return '';
                    }
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'exportInterval' => 1,
                    'logFile' => '@runtime/logs/web/game/write-rating.log',
                    'logVars' => [],
                    'levels' => ['error', 'info'],
                    'categories' => ['write-rating'],
                    'prefix' => function($message) {
                        return '';
                    }
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'exportInterval' => 1,
                    'logFile' => '@runtime/logs/web/game/create-game.log',
                    'logVars' => [],
                    'levels' => ['error', 'info'],
                    'categories' => ['create-game'],
                    'prefix' => function($message) {
                        return '';
                    }
                ],
            ],
        ],
        'db' => $db,
        'assetManager' => [
            'linkAssets' => false,
            'appendTimestamp' => true,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'bot' => [
            'class' => 'app\components\discord_bot\DiscordBot',
        ],
        'DiscordUser' => [
            'class' => 'app\components\discord_user\DiscordUser',
        ],
        'Game' => [
            'class' => 'app\components\game\Game',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
    $config['components']['assetManager']['forceCopy'] = true;
}

return $config;
