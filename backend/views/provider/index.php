<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\models\ProviderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Providers');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="provider-index">


    <p>
        <?= Html::a(Yii::t('app', 'Create Provider'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'name',
            'address',
            'phone',
            'second_phone',
            'email:email',
            'payment_method',
            'account',
            'credit_days',
            'rfc',
            'business_name',
            'advantages',
            'disadvantages',
            'observations',
//            'fax',
            //'business_id',

            [
                    'class' => 'yii\grid\ActionColumn',
                "template" => "{update} {delete}"
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
