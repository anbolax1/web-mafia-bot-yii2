<?php

namespace app\assets\discord;

use yii\web\AssetBundle;

class DiscordAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/discord/src';

    public $js = [
        'js/discord.js',
    ];

    public $css = [
        'css/discord.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
