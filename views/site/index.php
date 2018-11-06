<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
$this->title = 'Ads';
?>
<div class="ads-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php Pjax::begin(); ?>
    <p>
        <?= Html::a('Parse <i class="glyphicon glyphicon-import"></i>', ['parse'], [
            'class' => 'btn btn-success',
            'data' => [
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <?= $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'title',
            'key',
            [
                'attribute' => 'cost',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->cost? ($model->cost . ' руб/кв.м') : 'Цена договорная';
                },
            ],
            'address',
            [
                'attribute' => 'link',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->link? Html::a('Read more', Url::to($model->link, true),['target'=>'_blank', 'data-pjax'=>"0"]) : 'none';
                },
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
