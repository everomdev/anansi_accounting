<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Expense */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Gastos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$frequencies = $model::getFrequencyOptions();
?>
<div class="expense-view">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <!-- <div>
            <?= Html::a('<i class="fas fa-edit"></i> Editar', ['update', 'id' => $model->id], [
                'class' => 'btn btn-warning'
            ]) ?>
            <?= Html::a('<i class="fas fa-trash"></i> Eliminar', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => '¿Estás seguro de que deseas eliminar este gasto?',
                    'method' => 'post',
                ],
            ]) ?>
            <?= Html::a('<i class="fas fa-arrow-left"></i> Volver al Catálogo', ['index'], [
                'class' => 'btn btn-secondary'
            ]) ?>
        </div> -->
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información del Gasto
                    </h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped detail-view'],
                        'attributes' => [
                            [
                                'attribute' => 'key',
                                'label' => 'Clave',
                                'format' => 'raw',
                                'value' => '<span class="badge bg-primary font-monospace">' . Html::encode($model->key) . '</span>',
                            ],
                            'name:text:Nombre del Gasto',
                            'description:ntext:Descripción',
                            [
                                'attribute' => 'amount',
                                'label' => 'Monto',
                                'format' => 'currency',
                            ],
                            [
                                'attribute' => 'unit_measurement_id',
                                'label' => 'Unidad de Medida',
                                'value' => $model->unitMeasurement ? $model->unitMeasurement->name : '-',
                            ],
                            [
                                'attribute' => 'category_id',
                                'label' => 'Categoría',
                                'value' => $model->category ? $model->category->name : '-',
                            ],
                            [
                                'attribute' => 'frequency',
                                'label' => 'Frecuencia',
                                'value' => $frequencies[$model->frequency] ?? $model->frequency,
                            ],
                            [
                                'attribute' => 'expense_date',
                                'label' => 'Fecha del Gasto',
                                'format' => 'date',
                            ],
                            [
                                'attribute' => 'provider_id',
                                'label' => 'Proveedor',
                                'value' => $model->provider ? $model->provider->business_name : 'Sin proveedor asignado',
                            ],
                            [
                                'attribute' => 'is_active',
                                'label' => 'Estado',
                                'format' => 'raw',
                                'value' => $model->is_active 
                                    ? '<span class="badge bg-success">Activo</span>' 
                                    : '<span class="badge bg-secondary">Inactivo</span>',
                            ],
                            'observations:ntext:Observaciones',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        
       <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calculator"></i> Cálculos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h3 class="text-primary mb-3">
                            $<?= number_format($model->getMonthlyAmount(), 2) ?>
                        </h3>
                        <p class="text-muted mb-0">Monto Mensual Prorrateado</p>
                        <small class="text-muted">
                            Calculado según la frecuencia: <strong><?= $frequencies[$model->frequency] ?></strong>
                        </small>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h6 class="text-muted mb-1">Anual</h6>
                                <span class="h6 text-dark">$<?= number_format($model->getMonthlyAmount() * 12, 2) ?></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <h6 class="text-muted mb-1">Diario</h6>
                            <span class="h6 text-dark">$<?= number_format($model->getMonthlyAmount() / 30, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
             <!-- <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history"></i> Información de Registro
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Creado:</strong><br>
                        <small class="text-muted">
                            <?= Yii::$app->formatter->asDatetime($model->created_at) ?>
                        </small>
                    </p>
                    <p class="mb-0">
                        <strong>Última actualización:</strong><br>
                        <small class="text-muted">
                            <?= Yii::$app->formatter->asDatetime($model->updated_at) ?>
                        </small>
                    </p>
                </div>
            </div>
        </div> -->
    </div>
</div>
