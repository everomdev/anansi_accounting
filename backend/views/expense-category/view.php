<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ExpenseCategory */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Categorías de Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expense-category-view">


    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-info-circle"></i> Información de la Categoría
            </h5>
        </div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'options' => ['class' => 'table table-striped detail-view'],
                'attributes' => [
                    'name:text:Nombre',
                    'description:ntext:Descripción',
                    // [
                    //     'attribute' => 'created_at',
                    //     'label' => 'Fecha de Creación',
                    //     'format' => 'datetime',
                    // ],
                    // [
                    //     'attribute' => 'updated_at',
                    //     'label' => 'Última Actualización',
                    //     'format' => 'datetime',
                    // ],
                ],
            ]) ?>
        </div>
    </div>

    <?php if ($model->expenses): ?>
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-receipt"></i> Gastos con esta Categoría (<?= count($model->expenses) ?>)
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php foreach ($model->expenses as $expense): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= Html::a(Html::encode($expense->name), ['/expense/view', 'id' => $expense->id]) ?>
                            <span class="badge bg-primary rounded-pill">
                                <?= Yii::$app->formatter->asCurrency($expense->amount) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

</div>
