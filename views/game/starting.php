<?php
use app\assets\game\GameAsset;
GameAsset::register($this);

/* @var $host \yii\db\ActiveRecord */
/* @var $members \yii\db\ActiveRecord */

$this->title = 'Подготовка к игре';

$hostAvatar = $host->avatar;
?>
<!--<button id="testButton">test</button><br>-->
<head>
    <title>Панель игры</title>
</head>

<div class="row" style="height: 80vh;">
    <div class="col-md-6" style="position:relative;padding:0; background: rgba(96, 96, 112 ,0.85); border-radius: 15px; box-shadow: 5px 5px 5px black; max-height: 100%;">
        <h2 class="game-settings-h2" style="border-top-left-radius: 15px 15px; border-top-right-radius: 15px 15px; ">
            <p style="margin: 0;">Ведущий -
                <img id="hostAvatar" src="<?= $hostAvatar;?>" alt="Avatar" class="avatar">
                <span id="hostName"><?= $host->name ?></span>
            </p>
        </h2>
        <div style="height:86%; color: whitesmoke; font-size: 1.6em; overflow: auto; padding-left: 0.5em;">
            <ol id="membersList">
                <?php foreach ($members as $member):
                        $selfVideoClass = $member->self_video == 'true' ? 'with-self-video' : 'without-self-video';
                ?>
                <li class="potential-member member <?= $selfVideoClass; ?>">
                    <p id="member" discord_id="<?=$member->discord_id; ?>">
                        <img src="<?= $member->avatar;?>" alt="Avatar" class="avatar">
                        <span id="name"><?= $member->name;?></span>
                        <span id="deleteMember">Х</span>
                    </p>
                </li>
                <?php endforeach;?>
            </ol>
        </div>
        <div style="position: absolute;bottom: 0;display: flex;justify-content: space-between;flex-wrap: nowrap;align-items: flex-end;width: 100%;">
            <h2 id="shuffleMembersButton" class="game-settings-h2 game-start-h2" style="font-size:1.4em; width:24%;border-top-right-radius: 15px 15px;border-bottom-left-radius: 15px 15px; border-bottom-right-radius: 15px 15px; display: inline-block">Перемешать</h2>
            <h2 id="showOnlyWithSelfVideoButton" class="game-settings-h2 game-start-h2" style="font-size:1.4em; width:24%;border-radius: 15px; display: inline-block">Вебки</h2>
            <h2 id="showPrioritiesButton" class="game-settings-h2 game-start-h2" style="font-size:1.4em; width:24%;border-radius: 15px; display: inline-block">Приоритеты</h2>
            <h2 id="updateMembersButton" class="game-settings-h2 game-start-h2" style="font-size:1.4em; width:24%;border-top-left-radius: 15px 15px;border-bottom-left-radius: 15px 15px; border-bottom-right-radius: 15px 15px; display: inline-block; float: right">Обновить</h2>
        </div>
    </div>
    <div class="col-md-1"></div>
    <div class="col-md-5" style="padding:0; background: rgba(96, 96, 112 ,0.85); border-radius: 15px; box-shadow: 5px 5px 5px black;">
        <h2 class="game-settings-h2" style="border-top-left-radius: 15px 15px; border-top-right-radius: 15px 15px; ">Настройки игры</h2>
        <div id="switches">
            <div>
                <div class="d-inline-block" style="font-size: 2em">РЕЙТИНГОВАЯ ИГРА</div>
                <div class="form-check form-switch d-inline-block" style="">
                    <input class="form-check-input custom-switch" type="checkbox" id="isRating" checked>
                </div>
            </div>
            <div>
                <div class="d-inline-block" style="font-size: 2em">БОЛЬШЕ ФОЛОВ</div>
                <div class="form-check form-switch d-inline-block" style="">
                    <input class="form-check-input custom-switch" type="checkbox" id="moreFouls" >
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
        <h2 id="startGameButton" class="game-settings-h2 game-start-h2" style="border-bottom-left-radius: 15px 15px; border-bottom-right-radius: 15px 15px;"><span id="startGameSpinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none"></span>Начать игру (Игроков: <span id="membersCount" style="color: <?php echo count($members) == 10 ? 'green' : 'red'; ?>"><?= count($members);?></span>)</h2>
    </div>
</div>

<div class="modal fade" id="prioritiesModal" tabindex="-1" aria-labelledby="prioritiesModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="height: 80vh !important;">
            <div class="modal-body" style="height: 85%;">
                <p style="color: whitesmoke;font-size: 1.5em;text-align: center;font-weight: bold;">Сыграно игр за последние 24 часа</p>
                <div id="prioritiesBlock" style="overflow: auto;height: 80%;"></div>
            </div>
        </div>
    </div>
</div>