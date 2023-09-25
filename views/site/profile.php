<?php
use app\assets\game\GameAsset;
GameAsset::register($this);

//$user = Yii::$app->user->getIdentity();
//$discordUser = $user->getDiscordUser()->one();
?>

<div class="row" style="width:100%; background-color: rgba(96, 96, 112, 0.85); border-radius: 15px;">
    <div class="col-md-7" style="height: 8em;width: auto;display: flex;flex-wrap: wrap;align-content: space-around;">
        <img style="width: 7em; height: 7em; border-radius: 50%;" src="<?= "https://cdn.discordapp.com/avatars/$discordUser->discord_id/$discordUser->avatar.jpg"; ?>" alt="Avatar" class="avatar">
    </div>
    <div class="col-md-1" style="height: 8em;width: 10vw;display: flex;flex-wrap: wrap;justify-content: space-between;">
        <p style="display: flex;flex-wrap: wrap;justify-content: space-between;width:100%;margin:0;font-size: 1.7em;color: whitesmoke;font-weight: bold;"><?=$discordUser->username ?></p>
        <p style="display: flex;flex-wrap: wrap;justify-content: space-between;width:100%;color: whitesmoke;margin: 0;"><span>Игр сыграно:</span><span><?=$gamesPlayedCount ?></span></p>
        <p style="display: flex;flex-wrap: wrap;justify-content: space-between;width:100%;color: whitesmoke;margin: 0;"><span>Игр выиграно:</span><span><?=$gamesWonCount ?></span></p>
        <p style="display: flex;flex-wrap: wrap;justify-content: space-between;width:100%;color: whitesmoke;margin: 0;"><span>Игр проведено:</span><span><?=$gamesHostedCount ?></span></p>
    </div>
    <div class="col-md-4" style="height: 8em;width: 29vw;"></div>
</div>
