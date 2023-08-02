<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\StockPrice */

$this->title = Yii::t('app', 'Create Stock Price');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Stock Prices'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-price-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
