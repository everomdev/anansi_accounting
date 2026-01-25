<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Movement;
use common\models\RequisitionConfig;
use kartik\select2\Select2Asset;

/* @var $this yii\web\View */
/* @var $model common\models\Movement */
/* @var $form yii\widgets\ActiveForm */
/* @var $config common\models\RequisitionConfig */

// Registrar Select2
Select2Asset::register($this);

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

// Obtener reglas de requisición para el centro por defecto
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
    
    <!-- Campo oculto para indicar si es requisición extemporánea -->
    <?= Html::hiddenInput('is_extemporaneous', '0', ['id' => 'is-extemporaneous']) ?>
    
    <!-- Campo oculto para el motivo de requisición extemporánea -->
    <?= Html::hiddenInput('extemporaneous_reason_hidden', '', ['id' => 'extemporaneous-reason-hidden']) ?>

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
        
        // Inicializar Select2 en el nuevo select
        $(`.ingredient-select[data-index="${index}"]`).select2({
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
        
        // Destruir Select2 antes de eliminar la fila
        $(`.ingredient-select[data-index="${index}"]`).select2('destroy');
        
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
        
        // Validar motivo extemporáneo si es requerido
        const reason = $('#extemporaneous-reason').val().trim();
        if (reason) {
            // Si hay motivo, copiar al campo oculto
            $('#extemporaneous-reason-hidden').val(reason);
        }
        
        // Confirmar envío según tipo de requisición
        const isExtemporaneous = $('#is-extemporaneous').val() === '1';
        let confirmMessage = '¿Está seguro de enviar esta requisición?';
        
        if (isExtemporaneous && reason) {
            confirmMessage = '⚠️ Esta es una requisición EXTEMPORÁNEA (con motivo justificado).\n\n¿Confirma que desea enviarla?';
        } else if (isExtemporaneous && !reason) {
            confirmMessage = '⚠️ Esta requisición se marcará como FUERA DE TIEMPO (sin motivo especial).\n\n¿Confirma que desea enviarla?';
        }
        
        if (!confirm(confirmMessage)) {
            return false;
        }
        
        return true;
    });
});
JS
);
?>
