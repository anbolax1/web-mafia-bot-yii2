<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\User $model */

$this->title = $model->username;
\yii\web\YiiAsset::register($this);

$avatarUrl = "https://cdn.discordapp.com/avatars/$model->discordId/$model->avatar.jpg";
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <img src="<?= $avatarUrl?>"></img>
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

</div>
