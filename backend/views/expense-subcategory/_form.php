<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use common\models\ExpenseCategory;

/* @var $this yii\web\View */
/* @var $model common\models\ExpenseSubcategory */
/* @var $form yii\widgets\ActiveForm */

$business = \backend\helpers\RedisKeys::getBusiness();

// Obtener categorías principales
$categories = ArrayHelper::map(
    ExpenseCategory::find()
        ->where(['business_id' => $business->id, 'is_main_category' => true])
        ->orderBy(['sort_order' => SORT_ASC])
        ->all(),
    'id',
    'name'
);
?>

<div class="expense-subcategory-form">

    <?php $form = ActiveForm::begin([
        'id' => 'expense-subcategory-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
        'validateOnSubmit' => true
    ]); ?>

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-sm-12 col-md-6">
                    <?= $form->field($model, 'category_id')->widget(Select2::class, [
                        'data' => $categories,
                        'options' => ['placeholder' => 'Seleccionar categoría principal...'],
                        'pluginOptions' => [
                            'allowClear' => false,
                        ],
                    ])->label('Categoría Principal <span class="text-danger">*</span>') ?>
                    <small class="form-text text-muted">
                        La categoría principal agrupa las subcategorías para el estado de resultados.
                    </small>
                </div>

                <div class="col-sm-12 col-md-6">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Ej: Electricidad, Sueldos operativos...'])->label('Nombre de la Subcategoría <span class="text-danger">*</span>') ?>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-sm-12 col-md-12">
                    <?= $form->field($model, 'description')->textarea(['rows' => 3, 'placeholder' => 'Descripción opcional...'])->label('Descripción') ?>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-sm-12 col-md-6">
                    <div class="form-check form-switch">
                        <?= Html::activeCheckbox($model, 'is_inventoriable', [
                            'class' => 'form-check-input',
                            'id' => 'subcategory-is-inventoriable',
                            'label' => false
                        ]) ?>
                        <label class="form-check-label fw-bold" for="subcategory-is-inventoriable">
                            📦 Requiere Control de Inventario
                        </label>
                        <small class="form-text text-muted d-block">
                            Si esta subcategoría requiere llevar registro de entradas/salidas físicas (ej: suministros de limpieza, artículos de cocina).
                        </small>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6">
                    <?= $form->field($model, 'sort_order')->textInput(['type' => 'number', 'min' => 0, 'value' => $model->isNewRecord ? 0 : $model->sort_order])->label('Orden de Visualización') ?>
                    <small class="form-text text-muted">
                        Número para ordenar dentro de su categoría (menor número aparece primero).
                    </small>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="form-group">
                <?= Html::submitButton($model->isNewRecord ? '<i class="fas fa-save"></i> Crear Subcategoría' : '<i class="fas fa-save"></i> Actualizar Subcategoría', ['class' => 'btn btn-warning']) ?>
                <?= Html::a('<i class="fas fa-times"></i> Cancelar', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
