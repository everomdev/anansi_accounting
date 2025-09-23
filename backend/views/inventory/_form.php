<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Inventory */

?>
<div class="inventory-form">
    <?php
    use yii\grid\GridView;
    use common\models\IngredientStockSearch;
    $businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
    $businessId = $businessData['id'];
    $searchModel = new IngredientStockSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $businessId);
    // Si el usuario quiere guardar todos los insumos, quitar paginación
    $showAll = isset($_GET['showAll']) && $_GET['showAll'] == '1';
    if ($showAll) {
        $dataProvider->pagination = false;
        echo '<div class="alert alert-warning">Se están mostrando todos los insumos. Si tienes muchos, la carga puede ser lenta.</div>';
    }
    ?>
    <?php $form = ActiveForm::begin(); ?>
    <div style="margin-bottom: 32px;">
        <?= $form->field($model, 'fecha')->textInput(['type' => 'datetime-local']) ?>
    </div>
    <style>
    .sticky-header-container {
        position: relative;
        overflow: auto;
        max-height: calc(90vh - 180px);
        margin-bottom: 15px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }
    .sticky-header-table {
        margin-bottom: 0;
    }
    .sticky-header-table thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 10;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        white-space: normal;
        vertical-align: middle;
    }
    </style>
    <div class="table-responsive sticky-header-container">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-striped sticky-header-table'],
        'options' => ['class' => 'grid-view sticky-header-grid'],
        'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center mt-3'><div>{pager}</div><div>{summary}</div></div>",
        'columns' => [
            [
                'attribute' => 'ingredient',
                'label' => 'Insumo',
            ],
            [
                'label' => 'Inventario en almacén',
                'format' => 'raw',
                'value' => function($insumo) {
                    return Html::textInput("inventario[{$insumo->id}][inventario_almacen]", null, ['class' => 'form-control', 'type' => 'number', 'step' => '0.001']);
                }
            ],
            [
                'label' => 'Inventario en cocina',
                'format' => 'raw',
                'value' => function($insumo) {
                    return Html::textInput("inventario[{$insumo->id}][inventario_cocina]", null, ['class' => 'form-control', 'type' => 'number', 'step' => '0.001']);
                }
            ],
            [
                'label' => 'Inventario en barra',
                'format' => 'raw',
                'value' => function($insumo) {
                    return Html::textInput("inventario[{$insumo->id}][inventario_barra]", null, ['class' => 'form-control', 'type' => 'number', 'step' => '0.001']);
                }
            ],
            [
                'label' => 'Inventario en servicio',
                'format' => 'raw',
                'value' => function($insumo) {
                    return Html::textInput("inventario[{$insumo->id}][inventario_servicio]", null, ['class' => 'form-control', 'type' => 'number', 'step' => '0.001']);
                }
            ],
            [
                'label' => 'Inventario en otro',
                'format' => 'raw',
                'value' => function($insumo) {
                    return Html::textInput("inventario[{$insumo->id}][inventario_otro]", null, ['class' => 'form-control', 'type' => 'number', 'step' => '0.001']);
                }
            ],
        ],
    ]) ?>
    </div>
    <div class="mb-3">
        <a href="?showAll=1" class="btn btn-warning">Mostrar todos los insumos para guardar inventario masivo</a>
    </div>
    <div style="position: fixed; bottom: 32px; right: 32px; z-index: 1000;">
        <?= Html::submitButton('Guardar', [
            'class' => 'btn btn-success',
            'id' => 'btn-guardar-inventario',
            'style' => 'box-shadow: 0 2px 8px rgba(0,0,0,0.15); font-size: 18px; padding: 12px 32px;'
        ]) ?>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var btnGuardar = document.getElementById('btn-guardar-inventario');
        var fechaInput = document.querySelector('input[name="Inventory[fecha]"]');
        function validarFecha() {
            if (!fechaInput.value) {
                btnGuardar.disabled = true;
            } else {
                btnGuardar.disabled = false;
            }
        }
        validarFecha();
        fechaInput.addEventListener('input', validarFecha);
        // Evitar submit si no hay fecha
        btnGuardar.form.addEventListener('submit', function(e) {
            if (!fechaInput.value) {
                e.preventDefault();
                fechaInput.focus();
                fechaInput.classList.add('is-invalid');
                setTimeout(function(){ fechaInput.classList.remove('is-invalid'); }, 2000);
            }
        });
    });
    </script>
    <?php ActiveForm::end(); ?>
</div>
