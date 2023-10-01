<?php
use app\assets\game\GameAsset;
GameAsset::register($this);

//$user = Yii::$app->user->getIdentity();
//$discordUser = $user->getDiscordUser()->one();
?>

<div class="container">
    <div class="row" style="width:100%; background-color: rgba(96, 96, 112, 0.85); border-radius: 15px; justify-content: center !important;">
        <div class="" style="height: 8em;width: auto;display: flex;flex-wrap: wrap;align-content: space-around;">
            <img style="width: 7em; height: 7em; border-radius: 50%;" src="<?= $discordUserAvatar; ?>" alt="Avatar" class="avatar">
        </div>
        <div class="custom-row" style="width: 80%;display: flex;justify-content: space-between;">
            <div class="" style="height: 8em;min-width: 11em;max-width: 15%;display: flex;flex-wrap: wrap;justify-content: space-between;">
                <p class="main-profile-stat"><?=$discordUserName ?></p>
                <p class="submain-profile-stat"><span>Игр сыграно:</span><span><?=$gamesPlayedCount ?></span></p>
                <p class="submain-profile-stat"><span>Игр выиграно:</span><span><?=$gamesWonCount ?></span></p>
                <p class="submain-profile-stat"><span>Игр проведено:</span><span><?=$gamesHostedCount ?></span></p>
            </div>
            <div class="col-md-4" style="height: 8em;min-width: 11em;max-width: 15%;display: flex;flex-wrap: wrap;justify-content: space-between;">
                <p class="main-profile-stat">Рейтинг</p>
                <p class="submain-profile-stat"><span>Текущий:</span><span><?=$rating['current'] ?></span></p>
                <p class="submain-profile-stat"><span>Максимальный:</span><span><?=$rating['max'] ?></span></p>
                <p class="submain-profile-stat"><span>Минимальный:</span><span><?=$rating['min'] ?></span></p>
            </div>
            <div class="col-md-4" style="height: 8em;min-width: 15em;max-width: 20%;display: flex;flex-wrap: wrap;justify-content: space-between;">
                <p class="main-profile-stat">Процент побед</p>
                <p class="submain-profile-stat" style="width: 90% !important;"><span>Общий:</span><span><?=$winPercent['general'] ?></span></p>
                <p class="submain-profile-stat" style="width: 90% !important;"><span>На красном (мир, шер):</span><span><?=$winPercent['mir'] ?></span></p>
                <p class="submain-profile-stat" style="width: 90% !important;"><span>На чёрном (маф, дон):</span><span><?=$winPercent['maf'] ?></span></p>
            </div>
            <div class="col-md-4" style="height: 8em;min-width: 15em;max-width: 20%;display: flex;flex-wrap: wrap;justify-content: space-between;">
                <p class="main-profile-stat">Игровой стаж</p>
                <p class="submain-profile-stat" style="width: 90% !important;"><span>Игровых дней:</span><span>...</span></p>
                <p class="submain-profile-stat" style="width: 90% !important;"><span>Дата первой игры:</span><span>...</span></p>
                <p class="submain-profile-stat" style="width: 90% !important;"><span>Дата последней игры:</span><span>...</span></p>
            </div>
        </div>

    </div>
</div>

