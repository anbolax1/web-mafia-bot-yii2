<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use app\assets\game_index\GameIndexAsset;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = $model->username;
\yii\web\YiiAsset::register($this);
GameIndexAsset::register($this);
$avatarUrl = "https://cdn.discordapp.com/avatars/$model->discordId/$model->avatar.jpg";
?>
<div class="user-view">

    <h1 class="user-name"><?= Html::encode($this->title) ?></h1>

    <img src="<?= $avatarUrl?>" style="border-radius: 50%;"></img>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'discordId',
            'username',
            'roleDescription',
            'statusDescription',
        ],
    ]) ?>
    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>
</div>
