<?php
namespace app\assets\bootstrap;

use yii\web\AssetBundle;

class BootstrapIconsAsset  extends AssetBundle
{
    public $sourcePath = '@npm/bootstrap-icons';
    public $css = ['font/bootstrap-icons.css'];
}