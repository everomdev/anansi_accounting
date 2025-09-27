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
    $business = \common\models\Business::findOne(['id' => $businessData['id']]);
    $searchModel = new IngredientStockSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    $dataProvider->query->andWhere([
            'business_id' => $business->id
        ]);
    // Si el usuario quiere guardar todos los insumos, quitar paginación
    // Mostrar selector de cantidad de elementos por página
    if (isset($_GET['per-page'])) {
        if ($_GET['per-page'] === 'all') {
            $dataProvider->pagination = false;
            echo '<div class="alert alert-warning" style="margin-bottom:16px;">Se están mostrando todos los insumos. Si tienes muchos, la carga puede ser lenta.</div>';
        } else {
            $dataProvider->pagination->pageSize = (int)$_GET['per-page'];
        }
    }
    $this->registerJs(<<<JS
        window.handlePerPageChange = function() {
            var selector = document.getElementById('per-page-selector');
            if (!selector) return;
            const pageSize = selector.value;
            let url = new URL(window.location);
            url.searchParams.set('per-page', pageSize);
            if (window.jQuery && $.pjax) {
                $.pjax.reload({
                    container: '.sticky-header-container',
                    url: url.toString(),
                    timeout: 10000
                });
            } else {
                var form = document.getElementById('per-page-form');
                if (form) {
                    form.submit();
                } else {
                    window.location = url.toString();
                }
            }
        };
        var perPageSelector = document.getElementById('per-page-selector');
        if (perPageSelector) {
            perPageSelector.onchange = window.handlePerPageChange;
        }
    JS);
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
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <form id="per-page-form" method="get" class="d-inline-block" style="margin-bottom:0;">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><?= Yii::t('app', 'Mostrar') ?></span>
                    <select name="per-page" id="per-page-selector" class="form-select" style="width:80px;">
                        <?php foreach ([10, 20, 50, 100, 200, 500] as $size): ?>
                            <option value="<?= $size ?>" <?= (isset($_GET['per-page']) && $_GET['per-page'] == $size) ? 'selected' : '' ?>><?= $size ?></option>
                        <?php endforeach; ?>
                        <option value="all" <?= (isset($_GET['per-page']) && $_GET['per-page'] == 'all') ? 'selected' : '' ?>>Todos</option>
                    </select>
                    <span class="input-group-text bg-light"><?= Yii::t('app', 'elementos') ?></span>
                    <button type="submit" style="display:none"></button>
                </div>
            </form>
        </div>
        <div></div>
    </div>
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
                'label' => 'Unidad<br>Compra',
                'encodeLabel' => false,
                'value' => function($insumo) {
                    return isset($insumo->um) ? $insumo->um : '-';
                },
                'headerOptions' => ['style' => 'min-width: 120px; width: 10%;'],
            ],
            [
                'label' => 'Categoría',
                'encodeLabel' => false,
                'value' => function($insumo) {
                    return $insumo->category && isset($insumo->category->name) ? $insumo->category->name : '-';
                },
                'headerOptions' => ['style' => 'min-width: 120px; width: 12%;'],
                'filter' => \yii\helpers\Html::activeDropDownList(
                    $searchModel,
                    'categoria',
                    \common\models\Category::find()->select(['name', 'id'])->indexBy('id')->column(),
                    [
                        'class' => 'form-control',
                        'prompt' => 'Todas'
                    ]
                ),
            ],
            [
                'label' => 'Almacén',
                'format' => 'raw',
                'value' => function($insumo) {
                    return Html::textInput("inventario[{$insumo->id}][inventario_almacen]", null, ['class' => 'form-control', 'type' => 'number', 'step' => '0.001']);
                }
            ],
            [
                'label' => 'Cocina',
                'format' => 'raw',
                'value' => function($insumo) {
                    return Html::textInput("inventario[{$insumo->id}][inventario_cocina]", null, ['class' => 'form-control', 'type' => 'number', 'step' => '0.001']);
                }
            ],
            [
                'label' => 'Barra',
                'format' => 'raw',
                'value' => function($insumo) {
                    return Html::textInput("inventario[{$insumo->id}][inventario_barra]", null, ['class' => 'form-control', 'type' => 'number', 'step' => '0.001']);
                }
            ],
            [
                'label' => 'Servicio',
                'format' => 'raw',
                'value' => function($insumo) {
                    return Html::textInput("inventario[{$insumo->id}][inventario_servicio]", null, ['class' => 'form-control', 'type' => 'number', 'step' => '0.001']);
                }
            ],
            [
                'label' => 'Otro',
                'format' => 'raw',
                'value' => function($insumo) {
                    return Html::textInput("inventario[{$insumo->id}][inventario_otro]", null, ['class' => 'form-control', 'type' => 'number', 'step' => '0.001']);
                }
            ],
        ],
    ]) ?>
    </div>
    <!-- Botón antiguo eliminado, ahora el enlace está arriba -->
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
