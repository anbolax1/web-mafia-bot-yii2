<?php

use yii\grid\GridView;
/* @var $dataProvider yii\data\ActiveDataProvider */


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
]); ?>