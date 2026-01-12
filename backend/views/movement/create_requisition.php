<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Movement;
use common\models\RequisitionConfig;

/* @var $this yii\web\View */
/* @var $model common\models\Movement */
/* @var $form yii\widgets\ActiveForm */
/* @var $config common\models\RequisitionConfig */

$this->title = 'Crear Requisición';
$this->params['breadcrumbs'][] = ['label' => 'Movimientos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);

// Obtener configuración de requisiciones
$config = RequisitionConfig::getForBusiness($business->id);

// Obtener centro de consumo del usuario
$user = Yii::$app->user->identity;
$defaultCenter = $user->getDefaultConsumptionCenter();

// Obtener todos los centros de consumo del usuario
$userCenters = $user->getConsumptionCenters()->all();

// Si el usuario no tiene centros asignados y puede modificar (admin/owner), mostrar todos
$canModifyCenter = Yii::$app->user->can('admin') || Yii::$app->user->can('owner') || Yii::$app->user->can('administrator');
if ($canModifyCenter) {
    $availableCenters = \common\models\ConsumptionCenter::find()
        ->where(['business_id' => $business->id])
        ->andWhere(['!=', 'name', 'Almacén'])
        ->all();
} else {
    $availableCenters = $userCenters;
}

// Insumos disponibles (usando portion_um para requisiciones)
$ingredients = (new \yii\db\Query())
    ->select([
        "ingredient_stock.id",
        "ingredient_stock.ingredient",
        "ingredient_stock.brand",
        "ingredient_stock.presentation",
        "ingredient_stock.portion_um",
        "ingredient_stock.quantity",
        "CONCAT(
            COALESCE(ingredient_stock.key, ''),
            CASE 
                WHEN ingredient_stock.key IS NOT NULL AND TRIM(ingredient_stock.key) != '' 
                THEN ' - ' 
                ELSE '' 
            END,
            COALESCE(ingredient_stock.ingredient, ''),
            CASE 
                WHEN ingredient_stock.brand IS NOT NULL AND TRIM(ingredient_stock.brand) != '' 
                THEN CONCAT('  ', ingredient_stock.brand) 
                ELSE '' 
            END,
            CASE 
                WHEN ingredient_stock.presentation IS NOT NULL AND TRIM(ingredient_stock.presentation) != '' 
                THEN CONCAT('  ', ingredient_stock.presentation) 
                ELSE '' 
            END,
            CASE 
                WHEN ingredient_stock.portion_um IS NOT NULL AND TRIM(ingredient_stock.portion_um) != '' 
                THEN CONCAT(' (', ingredient_stock.portion_um, ')') 
                ELSE '' 
            END
        ) as label"
    ])
    ->from('ingredient_stock')
    ->where(['business_id' => $business->id])
    ->orderBy('ingredient_stock.ingredient ASC')
    ->all();

// Registrar variables JavaScript
$this->registerJsVar('maxFutureDays', $config->max_future_days);
$this->registerJsVar('greenThreshold', $config->availability_green_threshold);
$this->registerJsVar('yellowThreshold', $config->availability_yellow_threshold);
$this->registerJsVar('checkStockUrl', \yii\helpers\Url::to(['movement/check-stock-availability']));
$this->registerJsVar('ingredientsData', array_reduce($ingredients, function($carry, $item) {
    $carry[$item['id']] = [
        'id' => $item['id'],
        'name' => $item['ingredient'],
        'um' => $item['portion_um'],
        'quantity' => floatval($item['quantity']),
        'label' => $item['label']
    ];
    return $carry;
}, []));

// Registrar script de zona horaria
$this->registerJsFile('@web/js/utils/client-timezone.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<div class="requisition-form">
    <?php $form = ActiveForm::begin([
        'id' => 'requisition-form',
        'enableAjaxValidation' => false,
        'enableClientValidation' => true,
    ]); ?>

    <div class="card">
        
        <div class="card-body">
            <div class="row g-3">
                <!-- Centro de Consumo -->
                <div class="col-md-6">
                    <label class="form-label">Centro de Consumo <span class="text-danger">*</span></label>
                    <?php if ($canModifyCenter): ?>
                        <?= Html::dropDownList(
                            'consumption_center_id',
                            $defaultCenter ? $defaultCenter->id : null,
                            \yii\helpers\ArrayHelper::map($availableCenters, 'id', 'name'),
                            [
                                'class' => 'form-control',
                                'id' => 'consumption-center-select',
                                'required' => true
                            ]
                        ) ?>
                        <small class="text-muted">Puede modificar el centro de consumo</small>
                    <?php else: ?>
                        <?= Html::textInput(
                            'consumption_center_name',
                            $defaultCenter ? $defaultCenter->name : 'Sin centro asignado',
                            [
                                'class' => 'form-control',
                                'readonly' => true,
                                'style' => 'background-color: #e9ecef;'
                            ]
                        ) ?>
                        <?= Html::hiddenInput('consumption_center_id', $defaultCenter ? $defaultCenter->id : '', ['id' => 'consumption-center-select']) ?>
                        <small class="text-muted">Centro asignado a su usuario</small>
                    <?php endif; ?>
                </div>

                <!-- Fecha Requerida -->
                <div class="col-md-6">
                    <label class="form-label">Fecha Requerida <span class="text-danger">*</span></label>
                    <?= \kartik\datetime\DateTimePicker::widget([
                        'name' => 'required_date',
                        'value' => '', // Se llenará con JavaScript
                        'options' => [
                            'placeholder' => 'Seleccionar fecha requerida...',
                            'required' => true,
                            'id' => 'required-date-input',
                        ],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd hh:ii',
                            'todayHighlight' => true,
                            'minuteStep' => 15,
                            'startView' => 1,
                            'minView' => 0,
                            'showMeridian' => false,
                            // NO poner startDate ni endDate aquí - se configuran en JavaScript
                        ],
                        'pluginEvents' => [
                            'show' => 'function(e) {
                                // Asegurar que las restricciones estén aplicadas al abrir
                                var now = new Date();
                                var clientDateTime = now.getFullYear() + "-" + 
                                                    String(now.getMonth() + 1).padStart(2, "0") + "-" + 
                                                    String(now.getDate()).padStart(2, "0") + " " + 
                                                    String(now.getHours()).padStart(2, "0") + ":" + 
                                                    String(now.getMinutes()).padStart(2, "0");
                                var maxDate = new Date(now.getTime() + (' . $config->max_future_days . ' * 24 * 60 * 60 * 1000));
                                $(this).data("datetimepicker").setStartDate(clientDateTime);
                                $(this).data("datetimepicker").setEndDate(maxDate);
                            }',
                        ],
                    ]) ?>
                    <small class="text-muted">Solo fechas futuras. Máximo <?= $config->max_future_days ?> días a futuro</small>
                </div>

                <!-- Tabla de Insumos -->
                <div class="col-12">
                    <hr class="my-4">
                    <h6 class="mb-3">
                        <i class="bx bx-list-ul"></i> Insumos Solicitados
                        <span class="badge bg-secondary" id="items-count">0 items</span>
                    </h6>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="items-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="40%">Insumo</th>
                                    <th width="15%">Cantidad</th>
                                    <th width="10%">Unidad</th>
                                    <th width="15%">Disponibilidad</th>
                                    <th width="15%">Stock Actual</th>
                                    <th width="5%">
                                        <button type="button" class="btn btn-sm btn-success" id="add-item-btn">
                                            <i class="bx bx-plus"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="items-tbody">
                                <!-- Los items se agregan dinámicamente aquí -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <strong>Indicadores de disponibilidad:</strong>
                        <span class="badge bg-success ms-2">🟢 Verde</span> Solicitud ≤ <?= $config->availability_green_threshold ?>% del stock
                        <span class="badge bg-warning ms-2">🟡 Amarillo</span> Solicitud > <?= $config->availability_green_threshold ?>% y ≤ <?= $config->availability_yellow_threshold ?>%
                        <span class="badge bg-danger ms-2">🔴 Rojo</span> Solicitud > <?= $config->availability_yellow_threshold ?>% (no alcanza)
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="col-12">
                    <?= Html::label('Observaciones', 'observations', ['class' => 'form-label']) ?>
                    <?= Html::textarea('observations', '', [
                        'class' => 'form-control',
                        'rows' => 3,
                        'placeholder' => 'Observaciones adicionales...'
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <?= Html::a(
                        '<i class="bx bx-arrow-back"></i> Cancelar',
                        ['index'],
                        ['class' => 'btn btn-outline-secondary']
                    ) ?>
                </div>
                <div>
                    <?= Html::submitButton(
                        '<i class="bx bx-send"></i> Enviar Requisición',
                        [
                            'class' => 'btn btn-primary',
                            'id' => 'submit-btn'
                        ]
                    ) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Campo oculto para zona horaria del cliente -->
    <?= Html::hiddenInput('client_timezone', '', ['id' => 'client-timezone']) ?>
    
    <!-- Campo oculto para fecha/hora actual del cliente -->
    <?= Html::hiddenInput('client_current_datetime', '', ['id' => 'client-current-datetime']) ?>
    
    <!-- Campo oculto para tipo de movimiento -->
    <?= Html::hiddenInput('Movement[type]', Movement::TYPE_REQUISITION) ?>

    <?php ActiveForm::end(); ?>
</div>

<style>
.availability-indicator {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 1.2em;
}

.availability-indicator.success { color: #28a745; }
.availability-indicator.warning { color: #ffc107; }
.availability-indicator.danger { color: #dc3545; }

#items-table tbody tr:hover {
    background-color: #f8f9fa;
}

.quantity-input {
    font-weight: bold;
}

.stock-display {
    font-family: monospace;
}
</style>

<?php
$this->registerJs(<<<'JS'
$(document).ready(function() {
    // Capturar zona horaria y fecha/hora actual del cliente
    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const now = new Date();
    const clientDateTime = now.getFullYear() + '-' + 
                          String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                          String(now.getDate()).padStart(2, '0') + ' ' + 
                          String(now.getHours()).padStart(2, '0') + ':' + 
                          String(now.getMinutes()).padStart(2, '0');
    
    $('#client-timezone').val(timezone);
    $('#client-current-datetime').val(clientDateTime);
    
    // Establecer la fecha/hora del cliente en el DateTimePicker
    $('#required-date-input').val(clientDateTime);
    
    // Inyectar zona horaria del cliente (compatibilidad)
    if (typeof ClientTimezone !== 'undefined') {
        ClientTimezone.inject('requisition-form');
    }
    
    let itemIndex = 0;
    
    // Agregar primera fila automáticamente
    addItemRow();
    
    // Botón para agregar nueva fila
    $('#add-item-btn').on('click', function(e) {
        e.preventDefault();
        addItemRow();
    });
    
    // Función para agregar una fila de insumo
    function addItemRow() {
        const index = itemIndex++;
        
        // Crear opciones del select
        let optionsHtml = '<option value="">Seleccionar insumo...</option>';
        for (let id in ingredientsData) {
            const ing = ingredientsData[id];
            optionsHtml += `<option value="${ing.id}" data-um="${ing.um}" data-stock="${ing.quantity}">${ing.label}</option>`;
        }
        
        const row = `
            <tr data-index="${index}">
                <td>
                    <select name="items[${index}][ingredient_id]" class="form-control ingredient-select" data-index="${index}" required>
                        ${optionsHtml}
                    </select>
                </td>
                <td>
                    <input type="number" 
                           name="items[${index}][quantity]" 
                           class="form-control quantity-input" 
                           data-index="${index}"
                           step="0.01" 
                           min="0.01" 
                           placeholder="0.00"
                           required>
                </td>
                <td>
                    <input type="text" 
                           class="form-control um-display" 
                           data-index="${index}"
                           readonly 
                           style="background-color: #e9ecef;">
                </td>
                <td class="text-center">
                    <span class="availability-indicator" data-index="${index}">-</span>
                </td>
                <td class="text-center">
                    <span class="stock-display" data-index="${index}">-</span>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger remove-item" data-index="${index}">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        
        $('#items-tbody').append(row);
        updateItemsCount();
    }
    
    // Evento al seleccionar insumo
    $(document).on('change', '.ingredient-select', function() {
        const index = $(this).data('index');
        const selectedOption = $(this).find('option:selected');
        const um = selectedOption.data('um') || '';
        const stock = parseFloat(selectedOption.data('stock')) || 0;
        
        // Actualizar unidad de medida
        $(`.um-display[data-index="${index}"]`).val(um);
        
        // Actualizar stock disponible
        $(`.stock-display[data-index="${index}"]`).text(stock.toFixed(2) + ' ' + um);
        
        // Calcular disponibilidad si hay cantidad
        const quantity = parseFloat($(`.quantity-input[data-index="${index}"]`).val()) || 0;
        if (quantity > 0) {
            updateAvailability(index, quantity, stock, um);
        }
    });
    
    // Evento al cambiar cantidad
    $(document).on('input', '.quantity-input', function() {
        const index = $(this).data('index');
        const quantity = parseFloat($(this).val()) || 0;
        const selectedOption = $(`.ingredient-select[data-index="${index}"] option:selected`);
        const stock = parseFloat(selectedOption.data('stock')) || 0;
        const um = selectedOption.data('um') || '';
        
        if (quantity > 0 && stock >= 0) {
            updateAvailability(index, quantity, stock, um);
        }
    });
    
    // Función para actualizar indicador de disponibilidad
    function updateAvailability(index, requested, available, um) {
        if (available <= 0) {
            $(`.availability-indicator[data-index="${index}"]`)
                .html('🔴 Sin stock')
                .removeClass('success warning')
                .addClass('danger');
            return;
        }
        
        const percentage = (requested / available) * 100;
        let icon, className, text;
        
        if (percentage <= greenThreshold) {
            icon = '🟢';
            className = 'success';
            text = 'Disponible';
        } else if (percentage <= yellowThreshold) {
            icon = '🟡';
            className = 'warning';
            text = 'Advertencia';
        } else {
            icon = '🔴';
            className = 'danger';
            text = 'Insuficiente';
        }
        
        $(`.availability-indicator[data-index="${index}"]`)
            .html(`${icon} ${text}`)
            .removeClass('success warning danger')
            .addClass(className);
    }
    
    // Eliminar fila
    $(document).on('click', '.remove-item', function(e) {
        e.preventDefault();
        const index = $(this).data('index');
        $(`tr[data-index="${index}"]`).remove();
        updateItemsCount();
        
        // Si no quedan filas, agregar una nueva
        if ($('#items-tbody tr').length === 0) {
            addItemRow();
        }
    });
    
    // Actualizar contador de items
    function updateItemsCount() {
        const count = $('#items-tbody tr').length;
        $('#items-count').text(count + ' item' + (count !== 1 ? 's' : ''));
    }
    
    // Validación antes de enviar
    $('#requisition-form').on('beforeSubmit', function(e) {
        const itemsCount = $('#items-tbody tr').length;
        
        if (itemsCount === 0) {
            alert('Debe agregar al menos un insumo a la requisición');
            return false;
        }
        
        // Validar que todos los items tengan insumo y cantidad
        let valid = true;
        $('#items-tbody tr').each(function() {
            const ingredientId = $(this).find('.ingredient-select').val();
            const quantity = $(this).find('.quantity-input').val();
            
            if (!ingredientId || !quantity || parseFloat(quantity) <= 0) {
                valid = false;
                return false; // break
            }
        });
        
        if (!valid) {
            alert('Todos los items deben tener un insumo seleccionado y una cantidad válida');
            return false;
        }
        
        // Confirmar envío
        if (!confirm('¿Está seguro de enviar esta requisición?')) {
            return false;
        }
        
        return true;
    });
});
JS
);
?>
