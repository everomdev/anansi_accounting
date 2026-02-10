<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ExpenseSubcategory */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Subcategorías de Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="expense-subcategory-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('<i class="fas fa-edit"></i> Actualizar', ['update', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
        <?= Html::a('<i class="fas fa-trash"></i> Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => '¿Está seguro de eliminar esta subcategoría?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('<i class="fas fa-arrow-left"></i> Volver al listado', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </p>

    <div class="card">
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    [
                        'attribute' => 'category_id',
                        'label' => 'Categoría Principal',
                        'value' => function($model) {
                            return $model->category ? $model->category->name : '-';
                        },
                    ],
                    'name',
                    'description:ntext',
                    [
                        'attribute' => 'is_inventoriable',
                        'format' => 'raw',
                        'value' => function($model) {
                            return $model->is_inventoriable 
                                ? '<span class="badge bg-warning">📦 Sí, requiere inventario</span>' 
                                : '<span class="badge bg-secondary">No requiere inventario</span>';
                        },
                    ],
                    'sort_order',
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ]) ?>
        </div>
    </div>

    <?php if ($model->getExpenses()->count() > 0): ?>
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-file-invoice"></i> Gastos Asociados (<?= $model->getExpenses()->count() ?>)</h5>
            </div>
            <div class="card-body">
                <p>Esta subcategoría tiene <strong><?= $model->getExpenses()->count() ?></strong> gasto(s) asociado(s).</p>
                <?= Html::a('<i class="fas fa-list"></i> Ver gastos', ['/expense/index', 'ExpenseSearch[subcategory_id]' => $model->id], ['class' => 'btn btn-info btn-sm']) ?>
            </div>
        </div>
    <?php endif; ?>

</div>
