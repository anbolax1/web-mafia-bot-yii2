<?php

use yii\grid\GridView;
use yii\helpers\Url;

/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Главная';

//    --bs-table-bg: #999292d1;
?>

<h1 class="grid-title">Последние игры с ботом</h1>

<?= GridView::widget([
     'dataProvider' => $dataProvider,
     'columns' => [
         'id',
         'hostName',
         'guildName',
         'gameStatus',
         'winRole',
         'startTime',
         'endTime',
         // 'created_at',
         // 'updated_at',
     ],
     'rowOptions'   => function ($model, $key, $index, $grid) {
         return [
             'data-id' => $model->id,
         ];
     },
]); ?>

<?php
$this->registerJs("

    $('td').click(function (e) {
        var id = $(this).closest('tr').data('id');
        if(e.target == this)
            location.href = '" . Url::to(['game/view']) . "?id=' + id;
    });

");
