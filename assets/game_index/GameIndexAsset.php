<?php

namespace app\assets\game_index;

use yii\web\AssetBundle;

class GameIndexAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/game_index/src';

    public $js = [
        'js/game_index.js',
    ];

    public $css = [
        'css/game.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
