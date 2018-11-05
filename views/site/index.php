<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
$this->title = 'Ads';
?>
<div class="ads-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php if (Yii::$app->session->hasFlash('success')) { ?>
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            <?= Yii::$app->session->getFlash('success'); ?>
        </div>
    <?php } ?>
    <?php if (Yii::$app->session->hasFlash('error')) { ?>
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            <?= Yii::$app->session->getFlash('error'); ?>
        </div>
    <?php } ?>
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
                    return $model->link? Html::a('Read more', Url::to($model->link, true)) : 'none';
                },
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
