<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\SalesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Sales');
$this->params['breadcrumbs'][] = $this->title;
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
?>
<div class="sales-index">
    <p>
        <?= Html::a(Yii::t('app', 'Add Sales'), ['sales/create'], ['class' => 'btn btn-warning']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => $business->getFormatter(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'date',
            'amount_food:currency',
            'amount_drinking:currency',
            'amount_other:currency',

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{update} {delete}"
            ],
        ],
    ]); ?>

</div>


<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-add-sale',
    'title' => Yii::t('app', "Add Sales")
]);
echo \yii\bootstrap5\Html::tag('div', $this->render('_form', [
    'model' => new \common\models\Sales()
]), [
    'id' => 'form-sales-container'
]);

\yii\bootstrap5\Modal::end();
?>

<?php
$js = <<< JS
    $(document).on('click', "a[href*='update'], a[href*='create']", function(event){
        event.preventDefault();
        const url = $(this).attr('href');
        
        $.ajax({
            url: url
        }).done(function(response){
            $("#form-sales-container").html(response);
            $("#modal-add-sale").modal('show');
        })
        
        return false;
    });
JS;

$this->registerJs($js);
?>
