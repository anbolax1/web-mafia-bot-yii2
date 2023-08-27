<?php
use app\assets\game\GameAsset;
GameAsset::register($this);

$user = Yii::$app->user->getIdentity();
$discordUser = $user->getDiscordUser()->one();
?>

<div class="row">
    <div class="col-md-7" style="height: 5em;">
        <img style="width: 7em; height: 7em; border-radius: 50%;" src="<?= "https://cdn.discordapp.com/avatars/$discordUser->discord_id/$discordUser->avatar.jpg"; ?>" alt="Avatar" class="avatar">
    </div>
    <div class="col-md-1" style="background-color:black;height: 5em;"></div>
    <div class="col-md-4" style="background-color:black;height: 5em;"></div>
</div>
