<?php
/** @var $this \yii\web\View */
/** @var $users \common\models\User[] */
$this->title = Yii::t('app', "Users");

?>
<?= \yii\bootstrap5\Html::a(
    Yii::t('app', "Add new user"),
    ['//user/admin/create-user'],
    [
        'class' => 'btn btn-success mb-3'
    ]
) ?>
<?=
\yii\grid\GridView::widget([
    'dataProvider' => new \yii\data\ActiveDataProvider(['models' => $users]),
    'columns' => [
        ['class' => \yii\grid\SerialColumn::class],
        'profile.name',
        'username',
        [
            'class' => \yii\grid\ActionColumn::class,
            'template' => "{update} {delete}",
            'buttons' => [
                'update' => function($key, $model, $url){
                    return \yii\bootstrap5\Html::a(
                        "<i class='bx bxs-pencil'></i>",
                        ['//user/admin/update-user', 'id' => $model->id],
                        ['class' => 'text-warning']
                    );
                },
                'delete' => function($key, $model, $url){
                    return \yii\bootstrap5\Html::a(
                        "<i class='bx bxs-trash'></i>",
                        ['//user/admin/delete-user'],
                        ['class' => 'text-warning']
                    );
                },

            ]
        ]
    ]
])
?>
