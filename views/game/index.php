<?php
use app\assets\game_index\GameIndexAsset;
GameIndexAsset::register($this);
use yii\helpers\Url;
?>
<p id="isRedirectToStating" style="display:none;"><?= $redirect_to_starting ?></p>