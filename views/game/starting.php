<?php
use app\assets\game\GameAsset;
GameAsset::register($this);

/* @var $host \yii\db\ActiveRecord */
/* @var $members \yii\db\ActiveRecord */


$hostAvatar = $host->avatar;
?>
<!--<button id="testButton">test</button><br>-->
<head>
    <title>Панель игры</title>
    <style>
    </style>
</head>

<div class="row" style="height: 80vh;">
    <div class="col-md-6" style="padding:0; background: rgba(96, 96, 112 ,0.8); border-radius: 15px; box-shadow: 5px 5px 5px black; max-height: 100%;">
        <h2 class="game-settings-h2" style="border-top-left-radius: 15px 15px; border-top-right-radius: 15px 15px; ">
            <p style="margin: 0;">Ведущий -
                <img id="hostAvatar" src="<?= $hostAvatar;?>" alt="Avatar" class="avatar">
                <span id="hostName">Istwood100</span>
            </p>
        </h2>
        <div style="height:86%; color: whitesmoke; font-size: 1.6em; overflow: auto; padding-left: 0.5em;">
            <ol>
                <?php foreach ($members as $member):?>
                <li>
                    <p id="member" discord_id="<?=$member->discord_id; ?>">
                        <img src="<?= $member->avatar;?>" alt="Avatar" class="avatar">
                        <span><?= $member->name;?></span>
                    </p>
                </li>
                <?php endforeach;?>
                <?php foreach ($members as $member):?>
                <li>
                    <p id="member" discordId="<?=$member->discord_id; ?>">
                        <img src="<?= $member->avatar;?>" alt="Avatar" class="avatar">
                        <span><?= $member->name;?></span>
                    </p>
                </li>
                <?php endforeach;?>
                <?php foreach ($members as $member):?>
                <li>
                    <p id="member" discordId="<?=$member->discord_id; ?>">
                        <img src="<?= $member->avatar;?>" alt="Avatar" class="avatar">
                        <span><?= $member->name;?></span>
                    </p>
                </li>
                <?php endforeach;?>
                <?php foreach ($members as $member):?>
                <li>
                    <p id="member" discordId="<?=$member->discord_id; ?>">
                        <img src="<?= $member->avatar;?>" alt="Avatar" class="avatar">
                        <span><?= $member->name;?></span>
                    </p>
                </li>
                <?php endforeach;?>
                <?php foreach ($members as $member):?>
                <li>
                    <p id="member" discordId="<?=$member->discord_id; ?>">
                        <img src="<?= $member->avatar;?>" alt="Avatar" class="avatar">
                        <span><?= $member->name;?></span>
                    </p>
                </li>
                <?php endforeach;?>
                <?php foreach ($members as $member):?>
                <li>
                    <p id="member" discordId="<?=$member->discord_id; ?>">
                        <img src="<?= $member->avatar;?>" alt="Avatar" class="avatar">
                        <span><?= $member->name;?></span>
                    </p>
                </li>
                <?php endforeach;?>
            </ol>
        </div>
        <h2 class="game-settings-h2 game-start-h2" style="border-bottom-left-radius: 15px 15px; border-bottom-right-radius: 15px 15px;">Обновить участников</h2>

    </div>
    <div class="col-md-1"></div>
    <div class="col-md-5" style="padding:0; background: rgba(96, 96, 112 ,0.8); border-radius: 15px; box-shadow: 5px 5px 5px black;">
        <h2 class="game-settings-h2" style="border-top-left-radius: 15px 15px; border-top-right-radius: 15px 15px; ">Настройки игры</h2>
        <div id="switches">
            <div>
                <div class="d-inline-block" style="font-size: 2em">РЕЙТИНГОВАЯ ИГРА</div>
                <div class="form-check form-switch d-inline-block" style="">
                    <input class="form-check-input custom-switch" type="checkbox" id="isRating" checked>
                </div>
            </div>
            <div>
                <div class="d-inline-block" style="font-size: 2em">КОЛ-ВО ФОЛОВ</div>
                <div class="form-check form-switch d-inline-block" style="">
                    <input class="form-check-input custom-switch" type="checkbox" id="foulsCount" >
                </div>
            </div>
            <div>
                <div class="d-inline-block" style="font-size: 2em">+30 СЕКУНД</div>
                <div class="form-check form-switch d-inline-block" style="">
                    <input class="form-check-input custom-switch" type="checkbox" id="withExtraTime" >
                </div>
            </div>
            <div>
                <div class="d-inline-block" style="font-size: 2em">ЗАПРЕТ НА МАТ</div>
                <div class="form-check form-switch d-inline-block" style="">
                    <input class="form-check-input custom-switch" type="checkbox" id="banOnObsceneSpeech" checked>
                </div>
            </div>
        </div>
        <h2 class="game-settings-h2 game-start-h2" style="border-bottom-left-radius: 15px 15px; border-bottom-right-radius: 15px 15px;">Начать игру</h2>
    </div>
</div>