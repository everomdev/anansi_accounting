<?php
/** @var $this \yii\web\View */
/** @var $model \backend\models\UpdateAccountForm */

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use common\models\Business;

$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
$dayNames = Business::getDayNames();

// Obtener centros de consumo del business (excluyendo Almacén)
$consumptionCenters = \common\models\ConsumptionCenter::find()
    ->where(['business_id' => $business->id])
    ->andWhere(['!=', 'name', 'Almacén'])
    ->orderBy(['name' => SORT_ASC])
    ->all();
?>

<style>
.requisition-rules-container {
    background: #ffffff;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #e9ecef;
}

.day-checkbox-container {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 1rem;
}

.day-checkbox-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.day-checkbox-item.active {
    background: #e7f3ff;
    border-color: #0d6efd;
}

.day-checkbox-item .day-label {
    min-width: 100px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.day-checkbox-item .time-inputs {
    display: none;
    flex: 1;
    gap: 1rem;
    align-items: center;
}

.day-checkbox-item.active .time-inputs {
    display: flex;
}

.day-checkbox-item .time-input-wrapper {
    flex: 1;
}

.day-checkbox-item .time-input-wrapper label {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
    display: block;
}

.day-checkbox-item .time-input-wrapper input {
    width: 100%;
}

.time-input-group {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.time-input-wrapper {
    flex: 1;
}

.switch-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.form-switch .form-check-input {
    width: 3rem;
    height: 1.5rem;
}
</style>

<div class="requisition-rules-container">
    <h4 class="section-title">
        <i class="bx bx-calendar-check me-2"></i>
        Reglas de Requisición por Centro de Consumo
    </h4>
    
    <div class="alert alert-info">
        <i class="bx bx-info-circle"></i>
        <strong>Información:</strong>
        Configure los días y horarios permitidos para crear requisiciones en cada centro de consumo. 
        Cada centro puede tener reglas diferentes según sus necesidades operativas.
    </div>

    <!-- Selector de Centro de Consumo -->
    <div class="mb-4 p-3" style="background: #f8f9fa; border-radius: 8px;">
        <label class="form-label fw-bold">
            <i class="bx bx-building me-1"></i>
            Seleccione el Centro de Consumo a configurar
        </label>
        <select id="consumption-center-selector" class="form-control">
            <option value="">-- Seleccione un centro de consumo --</option>
            <?php foreach ($consumptionCenters as $center): ?>
                <option value="<?= $center->id ?>"><?= Html::encode($center->name) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Formulario de configuración (inicialmente oculto) -->
    <div id="rules-form-container" style="display: none;">
        <!-- Loading indicator -->
        <div id="loading-indicator" class="text-center p-5" style="display: none;">
            <i class="bx bx-loader bx-spin" style="font-size: 3rem;"></i>
            <br>Cargando configuración...
        </div>
        
        <!-- Formulario -->
        <div id="rules-form-content">
            <div class="alert alert-primary mb-3">
                <i class="bx bx-info-circle"></i>
                Configurando reglas para: <strong id="selected-center-name"></strong>
            </div>

            <?php $form = ActiveForm::begin([
                'id' => 'requisition-rules-form',
                'action' => ['business/save-consumption-center-rules'],
                'options' => ['class' => 'form-horizontal'],
            ]); ?>

            <input type="hidden" id="selected-consumption-center-id" name="consumption_center_id" value="">
            <input type="hidden" name="business_id" value="<?= $business->id ?>">

    <!-- Días Permitidos -->
    <div class="mb-4">
        <label class="form-label fw-bold">
            <i class="bx bx-calendar me-1"></i>
            Días permitidos para requisiciones
        </label>
        <p class="text-muted">
            Seleccione los días de la semana y configure el horario permitido para cada día.
        </p>
        
        <div class="day-checkbox-container">
            <?php foreach ($dayNames as $dayNumber => $dayName): ?>
                <div class="day-checkbox-item" data-day="<?= $dayNumber ?>">
                    <div class="day-label">
                        <input 
                            type="checkbox" 
                            name="requisition_allowed_days[]" 
                            value="<?= $dayNumber ?>" 
                            id="day-<?= $dayNumber ?>"
                            class="day-checkbox form-check-input"
                        >
                        <label for="day-<?= $dayNumber ?>" class="mb-0">
                            <?= $dayName ?>
                        </label>
                    </div>
                    
                    <div class="time-inputs">
                        <div class="time-input-wrapper">
                            <label>Hora inicio</label>
                            <input 
                                type="time" 
                                name="day_start_time[<?= $dayNumber ?>]" 
                                id="start-time-<?= $dayNumber ?>"
                                class="form-control form-control-sm" 
                                value="00:00"
                            >
                        </div>
                        
                        <span style="padding-top: 1.5rem;">→</span>
                        
                        <div class="time-input-wrapper">
                            <label>Hora fin</label>
                            <input 
                                type="time" 
                                name="day_end_time[<?= $dayNumber ?>]" 
                                id="end-time-<?= $dayNumber ?>"
                                class="form-control form-control-sm" 
                                value="23:59"
                            >
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Ventana Horaria -->
    <div class="mb-4" style="display: none;">
        <label class="form-label fw-bold">
            <i class="bx bx-time me-1"></i>
            Ventana horaria permitida (DEPRECADO - usar horarios por día)
        </label>
        <p class="text-muted">
            Este campo se mantiene por compatibilidad pero ya no se usa.
        </p>
        
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Hora de inicio</label>
                <input type="time" id="requisition_start_time" name="requisition_start_time" class="form-control" value="00:00">
            </div>
            
            <div class="col-md-6">
                <label class="form-label">Hora de fin</label>
                <input type="time" id="requisition_end_time" name="requisition_end_time" class="form-control" value="23:59">
            </div>
        </div>
    </div>

    <!-- Requisiciones Extemporáneas -->
    <div class="mb-4">
        <label class="form-label fw-bold">
            <i class="bx bx-bell me-1"></i>
            Requisiciones Extemporáneas
        </label>
        <p class="text-muted">
            Configure si se permiten requisiciones fuera de los días/horarios establecidos.
        </p>
        
        <div class="switch-container">
            <div>
                <div class="fw-bold">Permitir requisiciones extemporáneas</div>
                <small class="text-muted">Los usuarios podrán crear requisiciones fuera de los días/horarios configurados</small>
            </div>
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="allow_extemporaneous_requisitions" name="allow_extemporaneous_requisitions" value="1" checked>
            </div>
        </div>
        
        <div class="switch-container" id="require-reason-container">
            <div>
                <div class="fw-bold">Requiere motivo para requisiciones extemporáneas</div>
                <small class="text-muted">Solicitar que el usuario indique el motivo de la requisición extemporánea</small>
            </div>
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="require_extemporaneous_reason" name="require_extemporaneous_reason" value="1" checked>
            </div>
        </div>
    </div>

    <div class="form-group mt-4">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bx bx-save me-2"></i> Guardar Configuración
        </button>
    </div>

    <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

<?php
$businessId = $business->id;
$loadRulesUrl = \yii\helpers\Url::to(['business/load-consumption-center-rules']);
$saveRulesUrl = \yii\helpers\Url::to(['business/save-consumption-center-rules']);

$js = <<<JS
console.log('Script de requisiciones cargado');

const loadRulesUrl = '{$loadRulesUrl}';
const saveRulesUrl = '{$saveRulesUrl}';
const businessId = {$businessId};

console.log('URLs configuradas:', loadRulesUrl, saveRulesUrl);
console.log('Business ID:', businessId);

// Manejar activación/desactivación visual de días
$('.day-checkbox').on('change', function() {
    const dayItem = $(this).closest('.day-checkbox-item');
    if ($(this).is(':checked')) {
        dayItem.addClass('active');
    } else {
        dayItem.removeClass('active');
    }
});

// Cuando se selecciona un centro de consumo
$('#consumption-center-selector').on('change', function() {
    const centerId = $(this).val();
    const centerName = $(this).find('option:selected').text();
    
    console.log('Centro seleccionado:', centerId, centerName);
    
    if (!centerId) {
        $('#rules-form-container').hide();
        return;
    }
    
    // Mostrar loading
    $('#rules-form-container').show();
    $('#loading-indicator').show();
    $('#rules-form-content').hide();
    
    console.log('Enviando AJAX a:', loadRulesUrl);
    
    // Cargar reglas del centro seleccionado
    $.ajax({
        url: loadRulesUrl,
        type: 'POST',
        data: {
            consumption_center_id: centerId,
            business_id: businessId
        },
        dataType: 'json',
        success: function(response) {
            console.log('Respuesta del servidor:', response);
            
            $('#loading-indicator').hide();
            
            if (response.success) {
                // Actualizar nombre del centro
                $('#selected-center-name').text(centerName);
                $('#selected-consumption-center-id').val(centerId);
                
                // Mostrar formulario
                $('#rules-form-content').show();
                
                // Cargar datos
                const rules = response.rules;
                
                // Días permitidos y horarios
                $('.day-checkbox').prop('checked', false);
                $('.day-checkbox-item').removeClass('active');
                
                if (rules.requisition_allowed_days && rules.requisition_allowed_days.length > 0) {
                    rules.requisition_allowed_days.forEach(function(day) {
                        const checkbox = $('#day-' + day);
                        checkbox.prop('checked', true);
                        checkbox.closest('.day-checkbox-item').addClass('active');
                    });
                } else {
                    // Default: Lunes a Viernes
                    [1, 2, 3, 4, 5].forEach(function(day) {
                        const checkbox = $('#day-' + day);
                        checkbox.prop('checked', true);
                        checkbox.closest('.day-checkbox-item').addClass('active');
                    });
                }
                
                // Cargar horarios por día
                if (rules.day_schedules && typeof rules.day_schedules === 'object') {
                    Object.keys(rules.day_schedules).forEach(function(day) {
                        const schedule = rules.day_schedules[day];
                        $('#start-time-' + day).val(schedule.start_time || '00:00');
                        $('#end-time-' + day).val(schedule.end_time || '23:59');
                    });
                } else {
                    // Default: 00:00 - 23:59 para todos los días
                    [0, 1, 2, 3, 4, 5, 6].forEach(function(day) {
                        $('#start-time-' + day).val('00:00');
                        $('#end-time-' + day).val('23:59');
                    });
                }
                
                // Horarios globales (deprecados, solo por compatibilidad)
                $('#requisition_start_time').val(rules.requisition_start_time || '00:00');
                $('#requisition_end_time').val(rules.requisition_end_time || '23:59');
                
                // Switches - convertir valores 1/0 a boolean
                $('#allow_extemporaneous_requisitions').prop('checked', rules.allow_extemporaneous_requisitions == 1 || rules.allow_extemporaneous_requisitions === true);
                $('#require_extemporaneous_reason').prop('checked', rules.require_extemporaneous_reason == 1 || rules.require_extemporaneous_reason === true);
                
                toggleRequireReason();
            } else {
                alert('Error al cargar las reglas: ' + (response.message || 'Error desconocido'));
                $('#rules-form-container').hide();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', status, error);
            console.error('Response:', xhr.responseText);
            $('#loading-indicator').hide();
            alert('Error al comunicarse con el servidor: ' + error);
            $('#rules-form-container').hide();
        }
    });
});

// Mostrar/ocultar el switch de "requiere motivo"
function toggleRequireReason() {
    const allowExtemporaneous = $('#allow_extemporaneous_requisitions').is(':checked');
    $('#require-reason-container').toggle(allowExtemporaneous);
}

$('#allow_extemporaneous_requisitions').on('change', toggleRequireReason);

// Validación del formulario
$('#requisition-rules-form').on('beforeSubmit', function(e) {
    e.preventDefault();
    
    const selectedDays = $('input[name="requisition_allowed_days[]"]:checked').length;
    
    if (selectedDays === 0) {
        alert('Por favor seleccione al menos un día permitido para requisiciones.');
        return false;
    }
    
    // Validar que cada día seleccionado tenga horarios válidos
    let hasInvalidSchedule = false;
    $('input[name="requisition_allowed_days[]"]:checked').each(function() {
        const day = $(this).val();
        const startTime = $('input[name="day_start_time[' + day + ']"]').val();
        const endTime = $('input[name="day_end_time[' + day + ']"]').val();
        
        if (!startTime || !endTime) {
            alert('Por favor configure los horarios para todos los días seleccionados.');
            hasInvalidSchedule = true;
            return false;
        }
        
        if (startTime >= endTime) {
            const dayName = $('#day-' + day).closest('.day-checkbox-item').find('label').text().trim();
            alert('La hora de inicio debe ser menor que la hora de fin para ' + dayName);
            hasInvalidSchedule = true;
            return false;
        }
    });
    
    if (hasInvalidSchedule) {
        return false;
    }
    
    console.log('Enviando formulario...');
    
    // Enviar por AJAX
    $.ajax({
        url: saveRulesUrl,
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            console.log('Respuesta del guardado:', response);
            if (response.success) {
                alert('✅ Configuración guardada exitosamente');
            } else {
                alert('❌ Error: ' + (response.message || 'No se pudo guardar'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al guardar:', status, error);
            console.error('Response:', xhr.responseText);
            alert('❌ Error al guardar la configuración: ' + error);
        }
    });
    
    return false;
});

console.log('Event handlers registrados correctamente');
JS;

$this->registerJs($js, \yii\web\View::POS_READY);
?>
