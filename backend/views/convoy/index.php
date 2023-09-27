<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ConvoySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->registerJsFile(Yii::getAlias("@web/js/convoy/index.js"), [
    'depends' => [\yii\web\YiiAsset::class]
]);
$this->title = Yii::t('app', 'Convoys');
$this->params['breadcrumbs'][] = $this->title;
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
$totalSales = array_sum(\yii\helpers\ArrayHelper::getColumn($dataProvider->models, 'amount'));
?>
<div class="convoy-index">

    <p>
        <?= Html::a(Yii::t('app', 'Create Convoy'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
//        'filterModel' => $searchModel,
        'formatter' => $business->formatter,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            'um',
            'amount:currency',
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}"
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
