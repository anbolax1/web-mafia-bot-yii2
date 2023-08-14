<?php
use app\assets\game\GameAsset;
GameAsset::register($this);

$hostAvatar = \yii\helpers\Url::home(true) . 'images/night_city.jpg';
?>
<!--<button id="testButton">test</button><br>-->
<head>
    <title>Панель игры</title>
    <style>
    </style>
</head>

<div class="row" style="height: 80vh;">
    <div class="col-md-6" style="background: rgba(96, 96, 112 ,0.8);border-radius: 15px;box-shadow: 5px 5px 5px black;">
        <div style="color: whitesmoke; font-size: 2em;"><p>Ведущий -  <img id="hostAvatar" src="<?= $hostAvatar;?>" alt="Avatar" class="avatar"><span id="hostName">Istwood100</span></p></div>
        <div>Участники: <span id="hostName"></span></div>
    </div>
    <div class="col-md-1"></div>
    <div class="col-md-5" style="background: rgba(96, 96, 112 ,0.8);">
        <h2>Настройки игры</h2>
    </div>
</div>

<head>
    <title>Панель игры</title>
    <style>
        /*#main {
            background-image: url(../../web/images/night_city.png);
            background-size: cover;
            background-position: center;
            height: 100vh;
        }*/
        .avatar {
            vertical-align: sub;
            width: 1em;
            height: 1em;
            border-radius: 50%;
            margin-right: 0.3em;
        }
    </style>
</head>



