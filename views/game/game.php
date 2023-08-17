<?php
use app\assets\game\GameAsset;
GameAsset::register($this);

/* @var $game \app\models\Game */

$gameSettings = json_decode($game->getGameSettings()->one()->settings, true);
$gameSettings = array_combine(array_column($gameSettings, 'id'), array_column($gameSettings, 'value'));

$isMoreFouls =  $gameSettings['moreFouls'];
$foulsCount = $isMoreFouls == 'true' ? 5 : 4;
$gameMembers = $game->getGameMembers()->all();
$gameMembersCount = count($gameMembers);

$gameStartTime = $game->start_time;
$gameSeconds = time() - $gameStartTime;
?>

<head>
    <title>Игра</title>
</head>
<div id="techFields" style="display: none">
    <span id="gameId"><?= $game->id; ?></span>
    <span id="gameSeconds"><?= $gameSeconds ?></span>
</div>

<div id="gameBlock" class="row" style="">
    <div id="gameInfoBlock">
        <div id="timerBlock">
            <div id="timerWithButtonsBlock">
                    <div id="timerDisplay">
                        <span id="timerDisplay"></span>
                    </div>
                    <div id="timerButtons">
                        <span class="timer-button seconds set-seconds" id="30">30</span>
                        <span class="timer-button seconds set-seconds" id="60">60</span>
                        <span class="timer-button seconds set-seconds" id="120">120</span>
                        <?php if($gameSettings['withExtraTime'] == 'true'): ?>
                            <span class="timer-button seconds plus-seconds" id="30">+30</span>
                        <?php endif; ?>
                        <span class="timer-button action-with-timer" id="pause"><i class="bi bi-pause"></i></span>
                        <span class="timer-button action-with-timer" id="continue"><i class="bi bi-play"></i></span>
                    </div>
                </div>

            <div id="membersOnVoteBLock">
                <p style="width: 100%; text-align: center; color: whitesmoke; margin: 0;">Выставлены</p>
                <span id="membersOnVoteSpan">
                    <!--<span class="memberOnVote" id="01">01</span>
                    <span class="memberOnVote" id="05">05</span>
                    <span class="memberOnVote" id="07">07</span>-->
                </span>
                <span id="clearMembersOnVote">Очистить</span>
            </div>
            <div id="hideGameRulesButtonBlock">
                <span id="hideGameRulesButton">></span>
            </div>
        </div>
        <div id="membersBlock">
            <?php foreach ($gameMembers as $gameMember): ?>
                <p class="member-row" discord_id="<?= $gameMember->discord_id;?>">
                    <span class="member-name">
                        <span class="member-slot" slot="<?= $gameMember->slot ?>"><?= $gameMember->slot ?>.</span>
                        <img src="<?= $gameMember->avatar;?>" alt="Avatar" class="avatar">
                        <span><?= $gameMember->name ?></span>
                    </span>
                    <span class="fouls-list">
                        <?php for ($i = 1; $i <= $foulsCount; $i++): ?>
                            <span id="<?= $i; ?>" class="foul"><?= $i; ?></span>
                        <?php endfor; ?>
                    </span>
                    <?php if($gameSettings['withExtraTime'] == 'true'): ?>
                        <span id="plus30">+30</span>
                    <?php endif; ?>
                    <span class="delete-member">
                        X
                    </span>
                </p>
            <?php endforeach; ?>
        </div>
    </div>
    <div id="gameRulesBlock">
        <h2 id="gameTime">Время игры: <span></span></h2>
        <div id="deleteReasons">
            <p>Подъём со стола за</p>
            <ul>
                <li>Метаинфу</li>
                <li>Оскорбление ведущего / игроков</li>
                <li>Спор с ведущим</li>
                <li><?= $foulsCount ?> фол</li>
            </ul>
        </div>
        <div id="gameNotes">
            <ul>
                <li><?= $foulsCount - 1 ?> фол - мут</li>
                <li>Несострелы разрешены</li>
                <li>Правильное выставление: "Я ВЫСТАВЛЯЮ [НОМЕР]</li>
            </ul>
        </div>
        <div id="roleNote">
            <p>Роли на столе</p>
            <ul>
                <li>x6 Мирный</li>
                <li>x2 Мафия</li>
                <li>x1 Дон</li>
                <li>x1 Комиссар</li>
            </ul>
        </div>
        <h2 id="finishGameButton">Завершить игру</h2>
    </div>

</div>


<!-- Модальное окно -->
<div class="modal fade modal-sm" id="deleteMemberReasonModal" tabindex="-1" aria-labelledby="deleteMemberReasonModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div id="techFieldsModal" style="display: none;">
                    <span id="gameId"></span>
                    <span id="memberDiscordId"></span>
                    <span id="foulsCount"></span>
                </div>

                <div id="deleteMemberReasonModal" class="modal-buttons">
                    <span id="voted">Заголосован</span>
                    <span id="killed" data-bs-target="#theBestMoveModal" data-bs-toggle="modal" data-bs-dismiss="modal">Убит</span>
                    <span id="fouled">По фолам</span>
                    <span id="techReason">Тех. причина</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-sm" id="theBestMoveModal" aria-hidden="true" aria-labelledby="theBestMoveModalLabel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="theBestMoveModalHeader">
                <h5 class="modal-title" id="exampleModalToggleLabel2">Лучший ход</h5>
            </div>
            <div class="modal-body" id="theBestMoveModalBody">
                <span id="theBestMoveMembersList">
                    <?php foreach ($gameMembers as $gameMember): ?>
                        <span id="<?= $gameMember->slot; ?>" class="the-best-move-member"><?= $gameMember->slot; ?></span>
                    <?php endforeach; ?>
                </span>
            </div>
            <div class="modal-footer" id="theBestMoveModalFooter">
                <span id="sendTheBestMove">Отправить</span>
            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-sm" id="finishGameModal" tabindex="-1" aria-labelledby="finishGameModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div id="techFieldsModal" style="display: none;">
                    <span id="gameId"></span>
                </div>

                <div class="modal-buttons">
                    <span class="finish-game" id="maf">Победа мирных</span>
                    <span class="finish-game" id="mir" >Победа мафии</span>
                    <span class="finish-game" id="canceled">Отмена игры</span>
                </div>
            </div>
        </div>
    </div>
</div>