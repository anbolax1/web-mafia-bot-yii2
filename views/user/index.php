<?php

use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Пользователи';
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>



    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'username',
            'discordId',
            'roleDescription',
            /*[
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, User $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],*/
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{myButton}',  // the default buttons + your custom button
                'buttons' => [
                    'myButton' => function($url, $model, $key) {     // render your custom button
                        return Html::a(
                            "<button class='btn btn-success'>Авторизоваться</button>",
                            Url::to(['site/login-as-user', 'id' => $model->ID]),
                            [
                                'id'=>'grid-custom-button',
                                'data-pjax'=>true,
                                'action'=>Url::to(['site/login-as-user', 'id' => $model->ID]),
                                'class'=>'button btn btn-default',
                            ]
                        );
                    }
                ],
                'visibleButtons' =>
                    [
                        'myButton' => Yii::$app->user->getIdentity()->isAdmin(),
                        'delete' => Yii::$app->user->can('updatePost')
                    ]

            ]
        ],
         'rowOptions'   => function ($model, $key, $index, $grid) {
             return [
                 'data-id' => $model->id,
                 'style' => 'cursor:pointer;'
             ];
         },
    ]); ?>


</div>

<?php
$this->registerJs("

    $('td').click(function (e) {
        var id = $(this).closest('tr').data('id');
        if(e.target == this)
            location.href = '" . Url::to(['user/view']) . "?id=' + id;
    });

");

