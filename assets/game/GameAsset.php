<?php

namespace app\assets\game;

use yii\web\AssetBundle;

class GameAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/game/src';

    public $js = [
        'js/game.js',
    ];

    public $css = [
        'css/game.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
