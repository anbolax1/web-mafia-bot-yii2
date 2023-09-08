<?php
use app\assets\game\GameAsset;
GameAsset::register($this);

///* @var $host \yii\db\ActiveRecord */
///* @var $members \yii\db\ActiveRecord */
/* @var $game \yii\db\ActiveRecord */

//$game = \app\models\Game::find()->where(['id' => 13])->one();
$gameStartTimestamp = $game->start_time;
$gameEndTimestamp = $game->end_time;

$gameSettings = Yii::$app->Game->getGameSettings($game);
$gameMembers = \app\models\GameMember::find()->where(['game_id' => $game->id])->all();
//$hostAvatar = $host->avatar;
$gameMembersArray = \app\models\GameMember::find()->where(['game_id' => $game->id])->asArray()->all();
$gameMembersArray = array_combine(array_column($gameMembersArray, 'discord_id'), $gameMembersArray);
$winRole = $game->win_role == 'mir' ? 'Мирных жителей' : 'Мафии';

$gameHistory = \app\models\GameHistory::find()->where(['game_id' => $game->id])->all();
?>
<!--<button id="testButton">test</button><br>-->
<head>
    <title>Панель игры</title>
</head>

<div id="finishGameBlock" class="row" style="height: 80vh;">
    <div id="memberRolesBlock">
        <h2 class="game-settings-h2" style="border-top-left-radius: 15px 15px; border-top-right-radius: 15px 15px; ">
            <p style="margin: 0;">Победа
                <span id="winRoleSpan"><?= $winRole ?></span>
            </p>
        </h2>
        <div id="memberRoles">
            <?php foreach ($gameMembers as $gameMember): ?>
            <?php
                $changeRatingModel = \app\models\MemberRatingHistory::find()->where(['game_id' => $game->id, 'discord_id' => $gameMember->discord_id])->one();
                if(!empty($changeRatingModel)) {
                    if (intval($changeRatingModel->change_rating) > 0) {
                        $changeRating = sprintf("+%s" ,$changeRatingModel->change_rating);
                        $ratingSpanClass = 'rating-green';
                    } else {
                        $changeRating = $changeRatingModel->change_rating;
                        $ratingSpanClass = 'rating-red';
                    }
                } else {
                    $changeRating = '0';
                    $ratingSpanClass  = 'rating-green';
                }

            ?>
                <p class="finish-member-row" discord_id="<?= $gameMember->discord_id;?>" style="margin: 0 0 0.5em 0 !important;">
                    <span class="finish-member-name">
                        <span class="member-slot" slot="<?= $gameMember->slot ?>"><?= $gameMember->slot ?>.</span>
                        <img src="<?= $gameMember->avatar;?>" alt="Avatar" class="avatar">
                        <span><?= $gameMember->name ?></span>
                    </span>
                    <span class="finish-member-name" style="width: 40%;"><?= \app\models\Game::getRoleInRus($gameMember->role); ?></span>
                    <?php if($gameSettings['isRating'] == 'true'): ?>
                    <span class="finish-member-name member-change-rating <?= $ratingSpanClass; ?>" style="margin-right: 0.2em;"><?= strval($changeRating) ?></span>
                    <?php endif; ?>
                </p>
            <?php endforeach; ?>
        </div>
    </div>
    <div id="gameHistoryBlock">
        <div id="gameHistory">
            <h2 class="game-settings-h2" style="border-top-left-radius: 15px 15px; border-top-right-radius: 15px 15px; margin-bottom: 0.2em;">
                <p style="margin: 0;color: black;">
                    <span>Время игры: <?= gmdate("H:i:s", $gameEndTimestamp - $gameStartTimestamp) ?></span>
                </p>
            </h2>
            <?php foreach ($gameHistory as $gameHistoryItemNumber => $gameHistoryItem): ?>
                <p class="finish-member-row" discord_id="<?= $gameMember->discord_id;?>" style="margin: 0 0 0.5em 0 !important; width: 100%;">
                    <span class="finish-member-name" style="width: 5%;display: flex;justify-content: space-around;"><?= $gameHistoryItemNumber; ?></span>
                    <span class="finish-member-name">
                        <span class="member-slot"><?= $gameMembersArray[$gameHistoryItem->member_discord_id]['slot'] ?>.</span>
                        <img src="<?= $gameMembersArray[$gameHistoryItem->member_discord_id]['avatar'];?>" alt="Avatar" class="avatar">
                        <span><?= $gameMembersArray[$gameHistoryItem->member_discord_id]['name'] ?></span>
                    </span>
                    <span class="finish-member-name" style="width: 40%;"><?= \app\models\Game::getRoleInRus($gameMembersArray[$gameHistoryItem->member_discord_id]['role']); ?></span>
                    <span class="finish-member-name" style="width: 40%;"><?= \app\models\Game::getGameActionDescription($gameHistoryItem->description); ?></span>
                    <span class="finish-member-name" style="width: 40%;"><?= gmdate("H:i:s", $gameHistoryItem->time - $gameStartTimestamp) ?></span>
                </p>
            <?php endforeach; ?>
        </div>
        <h2 id="startNewGameButton" href="/starting">Начать новую игру</h2>
    </div>
</div>