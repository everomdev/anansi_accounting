<?php
/** @var $this \yii\web\View */
/** @var $users \common\models\User[] */
$this->title = Yii::t('app', "Users");

?>
<?php if (!Yii::$app->user->identity->hasRestrictions('users')): ?>
<?= \yii\bootstrap5\Html::a(
    Yii::t('app', "Add new user"),
    ['//user/admin/create-user'],
    [
        'class' => 'btn btn-success mb-3'
    ]
) ?>
<?php endif; ?>
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
                        ['//user/admin/delete-user', 'id' => $model->id],
                        [
                            'class' => 'text-danger',
                            'data' => [
                                'method' => 'post',
                                'confirm' => Yii::t('app', 'Are you sure you want to delete this user?'),
                            ],
                        ]
                    );
                },

            ]
        ]
    ]
])
?>
