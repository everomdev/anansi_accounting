<?php
/** @var $this \yii\web\View */
/** @var $dataProvider \yii\data\ActiveDataProvider */

$this->title = "Menús guardados";
?>


<?= \yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => \yii\grid\SerialColumn::class],
        'date:date',
        [
            'class' => \yii\grid\ActionColumn::class,
            'template' => '{view}',
            'buttons' => [
                'view' => function ($url, $model, $key) {
                    return \yii\helpers\Html::a('Ver', ['/standard-recipe/menu-recipes', 'bundle' => $model->id], ['class' => 'btn btn-success btn-sm']);
                }
            ]
        ]
    ]
]) ?>
