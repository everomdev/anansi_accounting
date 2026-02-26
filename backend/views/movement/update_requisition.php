<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Movement;
use common\models\RequisitionConfig;
use kartik\select2\Select2Asset;

/* @var $this yii\web\View */
/* @var $model common\models\Movement */
/* @var $existingItems array */
/* @var $form yii\widgets\ActiveForm */
/* @var $config common\models\RequisitionConfig */

// Registrar Select2
Select2Asset::register($this);

$this->title = 'Editar Requisición #' . $model->requisition_number;
$this->params['breadcrumbs'][] = ['label' => 'Movimientos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);

// Obtener configuración de requisiciones
$config = RequisitionConfig::getForBusiness($business->id);

// Obtener centro de consumo del usuario
$user = Yii::$app->user->identity;
$defaultCenter = \common\models\ConsumptionCenter::findOne($model->consumption_center_id);

// Obtener reglas de requisición para el centro actual
$defaultCenterRules = null;
if ($defaultCenter) {
    $defaultCenterRules = \common\models\ConsumptionCenterRequisitionRules::getForConsumptionCenter(
        $defaultCenter->id,
        $business->id
    );
}

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

// Registrar reglas de requisición del centro por defecto
if ($defaultCenterRules) {
    // Cargar horarios individuales por día
    $schedules = \common\models\ConsumptionCenterSchedule::find()
        ->where(['consumption_center_id' => $defaultCenter->id])
        ->orderBy(['day_of_week' => SORT_ASC, 'start_time' => SORT_ASC])
        ->all();
    
    // Convertir schedules a array para JavaScript (ahora puede tener múltiples rangos por día)
    $daySchedules = [];
    foreach ($schedules as $schedule) {
        if (!isset($daySchedules[$schedule->day_of_week])) {
            $daySchedules[$schedule->day_of_week] = [];
        }
        $daySchedules[$schedule->day_of_week][] = [
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
        ];
    }
    
    $this->registerJsVar('requisitionAllowedDays', $defaultCenterRules->getRequisitionAllowedDaysArray());
    $this->registerJsVar('daySchedules', $daySchedules); // Ahora es un array de rangos por día
    $this->registerJsVar('requisitionStartTime', $defaultCenterRules->requisition_start_time ?: '00:00');
    $this->registerJsVar('requisitionEndTime', $defaultCenterRules->requisition_end_time ?: '23:59');
    $this->registerJsVar('allowExtemporaneousRequisitions', $defaultCenterRules->allow_extemporaneous_requisitions ?? true);
    $this->registerJsVar('requireExtemporaneousReason', $defaultCenterRules->require_extemporaneous_reason ?? true);
} else {
    // Valores por defecto si no hay reglas configuradas
    $this->registerJsVar('requisitionAllowedDays', [1,2,3,4,5]); // Lunes a Viernes
    $this->registerJsVar('daySchedules', []); // Sin horarios específicos
    $this->registerJsVar('requisitionStartTime', '00:00');
    $this->registerJsVar('requisitionEndTime', '23:59');
    $this->registerJsVar('allowExtemporaneousRequisitions', true);
    $this->registerJsVar('requireExtemporaneousReason', true);
}

// URL para cargar reglas dinámicamente cuando cambie el centro
$this->registerJsVar('loadCenterRulesUrl', \yii\helpers\Url::to(['business/load-consumption-center-rules']));
$this->registerJsVar('businessId', $business->id);

// Registrar script de zona horaria
$this->registerJsFile('@web/js/utils/client-timezone.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<div class="requisition-form">
    
    <!-- Alerta de Requisición Extemporánea -->
    <div id="extemporaneous-alert" class="alert alert-warning alert-dismissible fade" role="alert" style="display: none;">
        <i class="bx bx-error-circle me-2"></i>
        <strong id="alert-title"><?= Yii::t('app', 'Requisición Fuera de Tiempo') ?></strong>
        <p class="mb-2" id="extemporaneous-message"></p>
        <div id="extemporaneous-reason-container" style="display: none;">
            <label class="form-label fw-bold"><?= Yii::t('app', 'Motivo de la requisición extemporánea (opcional):') ?></label>
            <small class="text-muted d-block mb-2">Si proporciona un motivo, la requisición se marcará como "Extemporánea". Si no, se marcará como "Fuera de tiempo".</small>
            <?= Html::textarea('extemporaneous_reason', '', [
                'class' => 'form-control',
                'rows' => 3,
                'id' => 'extemporaneous-reason',
                'placeholder' => Yii::t('app', 'Opcional: Explique el motivo especial de esta requisición...'),
            ]) ?>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <!-- Contenedor para Bootstrap Toasts (notificaciones pequeñas) -->
    <div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1080;"></div>

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
                        'value' => $model->required_date, // Valor de la requisición existente
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

                <!-- Urgencia -->
                <div class="col-md-6">
                    <label class="form-label">Urgencia <span class="text-danger">*</span></label>
                    <?= Html::dropDownList(
                        'urgency',
                        $model->urgency ?? Movement::URGENCY_NORMAL, // Valor de la requisición existente
                        Movement::getUrgencyLevels(),
                        [
                            'class' => 'form-select',
                            'id' => 'urgency-select',
                            'required' => true
                        ]
                    ) ?>
                    <small class="text-muted">Prioridad para el almacén (no afecta estados)</small>
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
                    <?= Html::textarea('observations', $model->observations, [ // Valor de la requisición existente
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
                        '<i class="bx bx-save"></i> Actualizar Requisición',
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
    
    <!-- Campo oculto para indicar si es requisición extemporánea -->
    <?= Html::hiddenInput('is_extemporaneous', '0', ['id' => 'is-extemporaneous']) ?>
    
    <!-- Campo oculto para el motivo de requisición extemporánea -->
    <?= Html::hiddenInput('extemporaneous_reason_hidden', '', ['id' => 'extemporaneous-reason-hidden']) ?>
    
    <!-- Campo oculto para items existentes (usado en edición) -->
    <input type="hidden" id="existing-items-data" value='<?= htmlspecialchars(json_encode(
        isset($existingItems) && is_array($existingItems) 
        ? array_values(array_map(function($item) {
            return [
                'ingredient_id' => (int)$item->ingredient_id,
                'quantity' => (float)$item->quantity_requested,
                'ingredient_name' => isset($item->ingredient) ? (string)$item->ingredient->ingredient : '',
            ];
        }, $existingItems))
        : []
    ), ENT_QUOTES, 'UTF-8') ?>' />

    <?php ActiveForm::end(); ?>
</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="confirmationModalLabel">
                    <i class="bx bx-check-circle"></i> Confirmar Envío de Requisición
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3"><strong>Por favor, revise cuidadosamente los datos antes de confirmar:</strong></p>
                
                <!-- Información General -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <strong><i class="bx bx-info-circle"></i> Información General</strong>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <strong>Centro de Consumo:</strong>
                                <p class="mb-0" id="confirm-center">-</p>
                            </div>
                            <div class="col-md-6">
                                <strong>Urgencia:</strong>
                                <p class="mb-0" id="confirm-urgency">-</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <strong>Fecha/Hora Requerida:</strong>
                                <p class="mb-0 text-primary fw-bold" id="confirm-date">-</p>
                            </div>
                        </div>
                        <div class="row mt-2" id="confirm-extemporaneous-info" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-warning mb-0" role="alert">
                                    <i class="bx bx-error-circle"></i> <strong>Requisición Fuera de Tiempo</strong>
                                    <p class="mb-0 mt-2" id="confirm-extemporaneous-text"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Insumos Solicitados -->
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <strong><i class="bx bx-list-ul"></i> Insumos Solicitados</strong>
                        <span class="badge bg-secondary ms-2" id="confirm-items-count">0</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50%">Insumo</th>
                                        <th width="20%" class="text-end">Cantidad</th>
                                        <th width="15%" class="text-center">Stock</th>
                                        <th width="15%" class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="confirm-items-list">
                                    <!-- Se llena dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Observaciones -->
                <div class="card" id="confirm-observations-card" style="display: none;">
                    <div class="card-header bg-light">
                        <strong><i class="bx bx-note"></i> Observaciones</strong>
                    </div>
                    <div class="card-body">
                        <p class="mb-0" id="confirm-observations">-</p>
                    </div>
                </div>
                
                <!-- Alertas -->
                <div id="confirm-alerts-container" class="mt-3">
                    <!-- Se llenan dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-arrow-back"></i> Regresar a Revisar
                </button>
                <button type="button" class="btn btn-warning text-dark fw-bold" id="confirm-submit-btn">
                    <i class="bx bx-send"></i> Confirmar y Enviar Requisición
                </button>
            </div>
        </div>
    </div>
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

/* Estilos para Select2 en la tabla */
.select2-container {
    width: 100% !important;
}

.select2-container .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

.select2-container .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
    padding-left: 12px;
}

.select2-container .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

.select2-dropdown {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

.select2-search--dropdown .select2-search__field {
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
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
    
    // Función para cargar reglas del centro de consumo seleccionado
    function loadCenterRules(centerId) {
        if (!centerId) {
            console.log('No hay centro seleccionado');
            return;
        }
        
        console.log('Cargando reglas para centro:', centerId);
        
        $.ajax({
            url: loadCenterRulesUrl,
            type: 'POST',
            data: {
                consumption_center_id: centerId,
                business_id: businessId
            },
            dataType: 'json',
            success: function(response) {
                console.log('Reglas cargadas:', response);
                
                if (response.success) {
                    const rules = response.rules;
                    
                    // Actualizar variables globales
                    requisitionAllowedDays = rules.requisition_allowed_days || [1,2,3,4,5];
                    daySchedules = rules.day_schedules || {}; // Nuevo: horarios por día
                    requisitionStartTime = rules.requisition_start_time || '00:00';
                    requisitionEndTime = rules.requisition_end_time || '23:59';
                    allowExtemporaneousRequisitions = rules.allow_extemporaneous_requisitions == 1 || rules.allow_extemporaneous_requisitions === true;
                    requireExtemporaneousReason = rules.require_extemporaneous_reason == 1 || rules.require_extemporaneous_reason === true;
                    
                    console.log('Reglas actualizadas:', {
                        días: requisitionAllowedDays,
                        horariosPorDía: daySchedules,
                        inicio: requisitionStartTime,
                        fin: requisitionEndTime,
                        permitirExtemp: allowExtemporaneousRequisitions,
                        requiereMotivo: requireExtemporaneousReason
                    });
                    
                    // Re-validar con las nuevas reglas
                    checkRequisitionRules();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar reglas:', status, error);
            }
        });
    }
    
    // Evento cuando cambia el centro de consumo
    $('#consumption-center-select').on('change', function() {
        const centerId = $(this).val();
        console.log('Centro de consumo cambiado a:', centerId);
        loadCenterRules(centerId);
    });
    
    // Función para validar si la requisición es extemporánea
    function checkRequisitionRules() {
        // Obtener la fecha requerida seleccionada por el usuario
        const requiredDateValue = $('#required-date-input').val();
        
        // Si no hay fecha seleccionada, usar la fecha actual
        let checkDate = now;
        if (requiredDateValue) {
            // Parsear la fecha seleccionada (formato: yyyy-mm-dd hh:ii)
            checkDate = new Date(requiredDateValue.replace(' ', 'T'));
            // Si la fecha no es válida, usar la actual
            if (isNaN(checkDate.getTime())) {
                checkDate = now;
            }
        }
        
        const currentDay = checkDate.getDay(); // 0 = Domingo, 1 = Lunes, ..., 6 = Sábado
        const currentTime = String(checkDate.getHours()).padStart(2, '0') + ':' + String(checkDate.getMinutes()).padStart(2, '0');
        
        // Obtener horarios para este día (ahora puede ser múltiples rangos)
        let dayRanges = [];
        
        if (daySchedules && daySchedules[currentDay] && Array.isArray(daySchedules[currentDay])) {
            // Tiene múltiples rangos configurados para este día
            dayRanges = daySchedules[currentDay];
        } else if (daySchedules && daySchedules[currentDay]) {
            // Formato antiguo: un solo objeto con start_time y end_time
            dayRanges = [{
                start_time: daySchedules[currentDay].start_time,
                end_time: daySchedules[currentDay].end_time
            }];
        } else {
            // Fallback: usar horario global
            dayRanges = [{
                start_time: requisitionStartTime,
                end_time: requisitionEndTime
            }];
        }
        
        console.log('Validando reglas:', {
            fechaRequerida: requiredDateValue,
            fechaValidación: checkDate.toISOString(),
            díaActual: currentDay,
            horaActual: currentTime,
            díasPermitidos: requisitionAllowedDays,
            rangosHorarios: dayRanges,
            cantidadDeRangos: dayRanges.length
        });
        
        // Verificar si el día está permitido
        const isDayAllowed = requisitionAllowedDays.includes(currentDay);
        
        // Verificar si la hora está dentro de ALGUNO de los rangos configurados
        let isTimeAllowed = false;
        let matchedRange = null;
        
        for (let range of dayRanges) {
            if (currentTime >= range.start_time && currentTime <= range.end_time) {
                isTimeAllowed = true;
                matchedRange = range;
                break;
            }
        }
        
        // Si está fuera de las reglas
        const isExtemporaneous = !isDayAllowed || !isTimeAllowed;
        
        console.log('Resultado validación:', {
            díaPermitido: isDayAllowed,
            horaPermitida: isTimeAllowed,
            esExtemporánea: isExtemporaneous
        });
        
        // Actualizar campo oculto
        $('#is-extemporaneous').val(isExtemporaneous ? '1' : '0');
        
        if (isExtemporaneous) {
            // Construir mensaje
            let message = '';
            const dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
            const selectedDayName = dayNames[currentDay];
            const selectedDateTime = checkDate.toLocaleString('es-ES', { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            
            if (!isDayAllowed && !isTimeAllowed) {
                const allowedDaysStr = requisitionAllowedDays.map(d => dayNames[d]).join(', ');
                message = `La fecha requerida (${selectedDateTime}) está fuera de los días y horarios permitidos para este centro de consumo. Días permitidos: ${allowedDaysStr}.`;
            } else if (!isDayAllowed) {
                const allowedDaysStr = requisitionAllowedDays.map(d => dayNames[d]).join(', ');
                message = `La fecha requerida está en ${selectedDayName}, que no está permitido para este centro. Días permitidos: ${allowedDaysStr}.`;
            } else {
                // El día está permitido pero la hora no está en ninguno de los rangos
                let rangesStr = dayRanges.map(r => `${r.start_time} - ${r.end_time}`).join(', ');
                message = `La hora de la fecha requerida (${currentTime}) está fuera de los horarios permitidos para ${selectedDayName}. Horarios permitidos: ${rangesStr}.`;
            }
            
            // Mostrar alerta (siempre amarilla, nunca bloquear)
            $('#extemporaneous-alert').removeClass('alert-danger').addClass('alert-warning show').fadeIn();
            $('#extemporaneous-message').text(message);
            
            // Mostrar campo de motivo (opcional si allow_extemporaneous, sino informativo)
            if (allowExtemporaneousRequisitions) {
                $('#alert-title').text('Requisición Fuera de Tiempo');
                $('#extemporaneous-reason-container').show();
                $('#extemporaneous-reason').prop('required', false); // Ahora es opcional
                message += '\n\nPuede proporcionar un motivo para clasificarla como "Extemporánea", o dejarla como "Fuera de tiempo".';
                $('#extemporaneous-message').text(message);
                $('#submit-btn').prop('disabled', false).html('<i class="bx bx-send"></i> Enviar Requisición');
            } else {
                // No se permiten extemporáneas, pero igual se puede enviar como "fuera de tiempo"
                $('#alert-title').text('Requisición Fuera de Tiempo');
                $('#extemporaneous-reason-container').hide();
                message += ' Esta requisición se registrará como "Fuera de tiempo".';
                $('#extemporaneous-message').text(message);
                $('#submit-btn').prop('disabled', false).html('<i class="bx bx-send"></i> Enviar Requisición Fuera de Tiempo');
            }
        } else {
            // Ocultar alerta si está dentro de las reglas
            $('#extemporaneous-alert').removeClass('show').fadeOut();
            $('#extemporaneous-reason-container').hide();
            $('#extemporaneous-reason').prop('required', false);
            $('#submit-btn').prop('disabled', false).html('<i class="bx bx-send"></i> Enviar Requisición');
        }
    }
    
    // Validar reglas al cargar la página
    checkRequisitionRules();
    
    // Re-validar cada vez que cambie la fecha requerida
    $('#required-date-input').on('change changeDate', function() {
        console.log('Fecha requerida cambiada a:', $(this).val());
        checkRequisitionRules();
    });
    
    // También validar cuando el DateTimePicker oculta (después de seleccionar)
    $('#required-date-input').on('dp.change', function(e) {
        console.log('DateTimePicker cambió:', e.date);
        checkRequisitionRules();
    });
    
    let itemIndex = 0;
    
    // Cargar items existentes de la requisición desde el campo oculto
    let existingItems = [];
    try {
        const itemsDataElement = document.getElementById('existing-items-data');
        if (itemsDataElement && itemsDataElement.value) {
            existingItems = JSON.parse(itemsDataElement.value);
            console.log('Items existentes parseados correctamente:', existingItems);
        } else {
            console.log('No se encontró el elemento existing-items-data o está vacío');
        }
    } catch (e) {
        console.error('Error al parsear items existentes:', e);
        console.log('Valor del campo:', document.getElementById('existing-items-data')?.value);
        existingItems = [];
    }
    
    console.log('Items existentes cargados:', existingItems);
    
    // Cargar items existentes si los hay
    if (existingItems && existingItems.length > 0) {
        console.log('Cargando ' + existingItems.length + ' items existentes...');
        existingItems.forEach(function(item, idx) {
            console.log('Cargando item ' + (idx + 1) + ':', item);
            addItemRow(item.ingredient_id, item.quantity);
        });
    } else {
        console.log('No hay items existentes, agregando fila vacía');
        // Agregar primera fila vacía si no hay items
        addItemRow();
    }
    
    // Botón para agregar nueva fila
    $('#add-item-btn').on('click', function(e) {
        e.preventDefault();
        addItemRow(null, null, true);
    });
    
    // Función para agregar una fila de insumo
    function addItemRow(preselectedIngredientId = null, preselectedQuantity = null, autoOpen = false) {
        const index = itemIndex++;
        
        console.log('addItemRow llamado con:', { index, preselectedIngredientId, preselectedQuantity });
        
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
        
        // Inicializar Select2 en el nuevo select
        const $select = $(`.ingredient-select[data-index="${index}"]`);
        $select.select2({
            placeholder: 'Buscar insumo...',
            allowClear: true,
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });
        
        // Forzar foco en el campo de búsqueda cada vez que se abre el dropdown
        $select.on('select2:open', function() {
            setTimeout(function() {
                let searchField = document.querySelector('.select2-container--open .select2-search__field');
                if (searchField) {
                    searchField.focus();
                }
            }, 0);
        });
        
        // Si hay valores preseleccionados, aplicarlos después de inicializar Select2
        if (preselectedIngredientId) {
            console.log('Estableciendo ingrediente seleccionado:', preselectedIngredientId);
            // Usar setTimeout para asegurar que Select2 esté completamente inicializado
            setTimeout(function() {
                $select.val(preselectedIngredientId).trigger('change');
                console.log('Ingrediente establecido, valor actual:', $select.val());
            }, 100);
        } else if (autoOpen === true) {
            // Solo abrir el dropdown automáticamente si se solicita explícitamente (al hacer clic en "Agregar Item")
            setTimeout(function() {
                $select.select2('open');
            }, 100);
        }
        
        if (preselectedQuantity) {
            console.log('Estableciendo cantidad:', preselectedQuantity);
            // Usar setTimeout para que se establezca después del ingrediente
            setTimeout(function() {
                $(`.quantity-input[data-index="${index}"]`).val(preselectedQuantity).trigger('input');
            }, 200);
        }
        
        updateItemsCount();
    }
    
    // Evento al seleccionar insumo
    $(document).on('change', '.ingredient-select', function() {
        const index = $(this).data('index');
        const selectedOption = $(this).find('option:selected');
        const um = selectedOption.data('um') || '';
        const stock = parseFloat(selectedOption.data('stock')) || 0;
        const ingredientId = $(this).val();

        // Detectar si el insumo ya existe en otra fila
        if (ingredientId) {
            let duplicateFound = false;
            let duplicateIndex = null;

            $('#items-tbody tr').each(function() {
                const rowIndex = $(this).data('index');
                if (rowIndex == index) return; // saltar la fila actual
                const otherSelect = $(this).find('.ingredient-select');
                if (otherSelect.length && otherSelect.val() == ingredientId) {
                    duplicateFound = true;
                    duplicateIndex = rowIndex;
                    return false; // break
                }
            });

            if (duplicateFound && duplicateIndex !== null) {
                // Sumar cantidades: tomar cantidad actual de la fila duplicada y de la fila seleccionada
                const currentQtyInput = $(`.quantity-input[data-index="${index}"]`);
                const duplicateQtyInput = $(`.quantity-input[data-index="${duplicateIndex}"]`);

                const currentQty = parseFloat(currentQtyInput.val()) || 0;
                const duplicateQty = parseFloat(duplicateQtyInput.val()) || 0;

                const newQty = currentQty + duplicateQty;

                // Actualizar la fila existente con la suma
                duplicateQtyInput.val(newQty).trigger('input');

                // Destruir Select2 y remover la fila duplicada (la que acaba de seleccionar)
                $(`.ingredient-select[data-index="${index}"]`).select2('destroy');
                $(`tr[data-index="${index}"]`).remove();

                // Actualizar contador
                updateItemsCount();

                // Recalcular disponibilidad en la fila donde quedó la suma
                const selectedOptionExisting = $(`.ingredient-select[data-index="${duplicateIndex}"] option:selected`);
                const umExisting = selectedOptionExisting.data('um') || '';
                const stockExisting = parseFloat(selectedOptionExisting.data('stock')) || 0;
                updateAvailability(duplicateIndex, newQty, stockExisting, umExisting);

                // Mostrar toast informativo
                showToast('Insumo duplicado: se sumó la cantidad a la línea existente');

                // Enfocar cantidad actualizada
                duplicateQtyInput.focus();

                return; // salir del handler
            }
        }

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
    
    // Función para mostrar un toast Bootstrap
    function showToast(message) {
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" style="background-color: #ffc107; color: #000;">
                <div class="d-flex align-items-center">
                    <div class="toast-body fw-bold">
                        <i class="bx bx-info-circle me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close me-2" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        const $el = $(toastHtml);
        $('#toast-container').append($el);
        const toast = new bootstrap.Toast(document.getElementById(toastId), { delay: 4000 });
        toast.show();

        // Remover del DOM cuando termine
        $el.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
    
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
        
        // Destruir Select2 antes de eliminar la fila
        $(`.ingredient-select[data-index="${index}"]`).select2('destroy');
        
        $(`tr[data-index="${index}"]`).remove();
        updateItemsCount();
        
        // Si no quedan filas, agregar una nueva (sin auto-abrir)
        if ($('#items-tbody tr').length === 0) {
            addItemRow(null, null, false);
        }
    });
    
    // Actualizar contador de items
    function updateItemsCount() {
        const count = $('#items-tbody tr').length;
        $('#items-count').text(count + ' item' + (count !== 1 ? 's' : ''));
    }
    
    // Validación antes de enviar - Mostrar modal de confirmación
    $('#requisition-form').on('submit', function(e) {
        e.preventDefault(); // Prevenir envío directo
        
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
        
        // Validar motivo extemporáneo si hay
        const reason = $('#extemporaneous-reason').val().trim();
        if (reason) {
            $('#extemporaneous-reason-hidden').val(reason);
        }
        
        // Llenar modal de confirmación
        populateConfirmationModal();
        
        // Mostrar modal
        const confirmModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
        confirmModal.show();
        
        return false;
    });
    
    // Función para llenar el modal de confirmación
    function populateConfirmationModal() {
        // Centro de consumo
        const centerSelect = $('#consumption-center-select');
        let centerName = '';
        
        // Verificar si es un select (usuario puede modificar) o input readonly
        if (centerSelect.is('select')) {
            centerName = centerSelect.find('option:selected').text();
        } else {
            // Es un input hidden, buscar el nombre en el input de texto visible
            centerName = $('input[name="consumption_center_name"]').val();
        }
        
        $('#confirm-center').text(centerName || 'No especificado');
        
        // Urgencia
        const urgencySelect = $('#urgency-select');
        const urgencyText = urgencySelect.find('option:selected').text();
        let urgencyBadge = '';
        const urgencyValue = urgencySelect.val();
        if (urgencyValue === 'very_urgent') {
            urgencyBadge = '<span class="badge bg-danger"><i class="bx bx-up-arrow-alt"></i> ' + urgencyText + '</span>';
        } else if (urgencyValue === 'low') {
            urgencyBadge = '<span class="badge bg-info"><i class="bx bx-down-arrow-alt"></i> ' + urgencyText + '</span>';
        } else {
            urgencyBadge = '<span class="badge bg-secondary"><i class="bx bx-minus"></i> ' + urgencyText + '</span>';
        }
        $('#confirm-urgency').html(urgencyBadge);
        
        // Fecha requerida
        const requiredDate = $('#required-date-input').val();
        if (requiredDate) {
            const dateObj = new Date(requiredDate.replace(' ', 'T'));
            const formattedDate = dateObj.toLocaleString('es-ES', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
            $('#confirm-date').text(formattedDate);
        }
        
        // Información extemporánea
        const isExtemporaneous = $('#is-extemporaneous').val() === '1';
        const reason = $('#extemporaneous-reason').val().trim();
        if (isExtemporaneous) {
            $('#confirm-extemporaneous-info').show();
            if (reason) {
                $('#confirm-extemporaneous-text').html('<strong>Tipo:</strong> Extemporánea (con motivo justificado)<br><strong>Motivo:</strong> ' + reason);
            } else {
                $('#confirm-extemporaneous-text').html('<strong>Tipo:</strong> Fuera de tiempo (sin motivo especial)');
            }
        } else {
            $('#confirm-extemporaneous-info').hide();
        }
        
        // Observaciones
        const observations = $('textarea[name="observations"]').val().trim();
        if (observations) {
            $('#confirm-observations-card').show();
            $('#confirm-observations').text(observations);
        } else {
            $('#confirm-observations-card').hide();
        }
        
        // Insumos
        let itemsHtml = '';
        let itemCount = 0;
        let hasWarnings = false;
        let hasCritical = false;
        const warnings = [];
        
        $('#items-tbody tr').each(function() {
            const row = $(this);
            const index = row.data('index');
            const ingredientSelect = row.find('.ingredient-select');
            const ingredientName = ingredientSelect.find('option:selected').text();
            const ingredientId = ingredientSelect.val();
            const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
            const um = row.find('.um-display').val();
            const stock = parseFloat(ingredientSelect.find('option:selected').data('stock')) || 0;
            const availabilityIndicator = row.find('.availability-indicator');
            
            if (ingredientId && quantity > 0) {
                itemCount++;
                
                // Determinar estado de disponibilidad
                let statusBadge = '';
                let statusClass = '';
                let percentage = stock > 0 ? (quantity / stock) * 100 : 999;
                
                if (stock <= 0) {
                    statusBadge = '<span class="badge bg-danger">🔴 Sin stock</span>';
                    statusClass = 'table-danger';
                    hasCritical = true;
                    warnings.push({
                        type: 'danger',
                        message: `<strong>${ingredientName}:</strong> Sin stock disponible`
                    });
                } else if (percentage > yellowThreshold) {
                    statusBadge = '<span class="badge bg-danger">🔴 Insuficiente</span>';
                    statusClass = 'table-danger';
                    hasCritical = true;
                    warnings.push({
                        type: 'danger',
                        message: `<strong>${ingredientName}:</strong> Stock insuficiente (solicita ${quantity.toFixed(2)} ${um}, disponible ${stock.toFixed(2)} ${um})`
                    });
                } else if (percentage > greenThreshold) {
                    statusBadge = '<span class="badge bg-warning">🟡 Advertencia</span>';
                    statusClass = 'table-warning';
                    hasWarnings = true;
                    warnings.push({
                        type: 'warning',
                        message: `<strong>${ingredientName}:</strong> Cantidad elevada respecto al stock (solicita ${quantity.toFixed(2)} ${um}, disponible ${stock.toFixed(2)} ${um})`
                    });
                } else {
                    statusBadge = '<span class="badge bg-success">🟢 Disponible</span>';
                }
                
                itemsHtml += `
                    <tr class="${statusClass}">
                        <td>${ingredientName}</td>
                        <td class="text-end fw-bold">${quantity.toFixed(2)} ${um}</td>
                        <td class="text-center">${stock.toFixed(2)} ${um}</td>
                        <td class="text-center">${statusBadge}</td>
                    </tr>
                `;
            }
        });
        
        $('#confirm-items-list').html(itemsHtml);
        $('#confirm-items-count').text(itemCount + ' item' + (itemCount !== 1 ? 's' : ''));
        
        // Alertas
        let alertsHtml = '';
        if (hasCritical) {
            const criticalWarnings = warnings.filter(w => w.type === 'danger');
            alertsHtml += '<div class="alert alert-danger" role="alert">';
            alertsHtml += '<i class="bx bx-error-circle"></i> <strong>Atención: Problemas Críticos de Stock</strong>';
            alertsHtml += '<ul class="mb-0 mt-2">';
            criticalWarnings.forEach(w => {
                alertsHtml += '<li>' + w.message + '</li>';
            });
            alertsHtml += '</ul>';
            alertsHtml += '</div>';
        }
        
        if (hasWarnings) {
            const warningList = warnings.filter(w => w.type === 'warning');
            alertsHtml += '<div class="alert alert-warning" role="alert">';
            alertsHtml += '<i class="bx bx-error-circle"></i> <strong>Advertencias de Stock</strong>';
            alertsHtml += '<ul class="mb-0 mt-2">';
            warningList.forEach(w => {
                alertsHtml += '<li>' + w.message + '</li>';
            });
            alertsHtml += '</ul>';
            alertsHtml += '</div>';
        }
        
        if (!hasCritical && !hasWarnings) {
            alertsHtml = '<div class="alert alert-success" role="alert">';
            alertsHtml += '<i class="bx bx-check-circle"></i> <strong>Todos los insumos están disponibles</strong>';
            alertsHtml += '</div>';
        }
        
        $('#confirm-alerts-container').html(alertsHtml);
    }
    
    // Confirmar y enviar desde el modal
    $('#confirm-submit-btn').on('click', function() {
        // Cerrar modal
        bootstrap.Modal.getInstance(document.getElementById('confirmationModal')).hide();
        
        // Enviar formulario (sin validación beforeSubmit para evitar loop)
        const form = $('#requisition-form')[0];
        form.submit();
    });
});
JS
);
?>
