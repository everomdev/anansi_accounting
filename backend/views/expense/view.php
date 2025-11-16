<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Expense */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Catálogo de Gastos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="expense-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Actualizar'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Eliminar'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', '¿Estás seguro de que quieres eliminar este gasto?'),
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a(Yii::t('app', 'Volver al listado'), ['index'], ['class' => 'btn btn-secondary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'key',
            'name',
            'brand',
            'presentation',
            [
                'attribute' => 'category_id',
                'label' => 'Categoría',
                'value' => $model->category ? $model->category->name : 'Sin categoría',
            ],
            'um',
            'quantity',
            'yield',
            'portions_per_unit',
            'portion_um',
            'min_stock',
            'max_stock',
            'observations:ntext',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
