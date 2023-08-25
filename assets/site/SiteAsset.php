<?php

namespace app\assets\site;

use yii\web\AssetBundle;

class SiteAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/site/src';

    public $js = [
        'js/site.js',
    ];

    public $css = [
        'css/site.css',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
