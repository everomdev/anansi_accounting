<?php
/** @var $this \yii\web\View */

/** @var $model \backend\models\CreateUserForm */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

$this->title = Yii::t('app', "Update user");

// Cargar roles igual que en create_user.php
$roles = Yii::$app->authManager->getRoles();
$roles = array_filter($roles, function ($role) {
    return $role->name != 'admin' && $role->name != 'owner';
});
// Ordenar roles por descripción
usort($roles, function($a, $b) {
    return strcmp($a->description, $b->description);
});

// Obtener centros de consumo del restaurante (excluyendo "Almacén")
$business = \backend\helpers\RedisKeys::getBusiness();
$consumptionCenters = \yii\helpers\ArrayHelper::map(
    \common\models\ConsumptionCenter::find()
        ->where(['business_id' => $business->id])
        ->andWhere(['!=', 'name', 'Almacén'])
        ->all(),
    'id',
    'name'
);

// Obtener todos los permisos disponibles para permisos adicionales
$allPermissions = Yii::$app->authManager->getPermissions();
if (!is_array($allPermissions)) {
    $allPermissions = [];
}

// Agrupar permisos por categoría (basado en el menú lateral)
$groups = [
    'Configuración Base' => [],
    'Gestión de Insumos y Proveedores' => [],
    'Costeo' => [],
    'Gastos' => [],
    'Menú y Ventas' => [],
    'Almacén y Movimientos' => [],
    'Rentabilidad y Análisis' => [],
    'KPI\'s y Control' => [],
    'Recursos Humanos' => [],
    'Administración y Configuración' => [],
    'Dashboard' => [],
    'Otros' => [],
];

// Añadir los permisos de empleados (RRHH) al grupo de Administración y Configuración
$prefixes = [
    // Configuración Base
    'category_' => 'Configuración Base',
    'recipe_category_' => 'Configuración Base',
    'unit_measurement_' => 'Configuración Base',
    'consumption_center_' => 'Configuración Base',
    
    // Costeo
    'recipe_' => 'Costeo',
    'subrecipe_' => 'Costeo',
    'convoy_' => 'Costeo',
    'combo_' => 'Costeo',
    
    // Gestión de Insumos y Proveedores
    'ingredients_' => 'Gestión de Insumos y Proveedores',
    'providers_' => 'Gestión de Insumos y Proveedores',
    
    // Gastos
    'expense_' => 'Gastos',
    'expensecategory_' => 'Gastos',
    'expensemovement_' => 'Gastos',
    'expenseunitmeasurement_' => 'Gastos',
    
    // Menú y Ventas
    'menu_' => 'Menú y Ventas',
    'sales_' => 'Menú y Ventas',
    
    // Almacén y Movimientos
    'movements_' => 'Almacén y Movimientos',
    'storage_' => 'Almacén y Movimientos',
    'price_trend_' => 'Almacén y Movimientos',
    'inventory_' => 'Almacén y Movimientos',
    'requisitions_' => 'Almacén y Movimientos',
    
    // Rentabilidad y Análisis
    'theoretical_' => 'Rentabilidad y Análisis',
    'real_' => 'Rentabilidad y Análisis',
    'charts_' => 'Rentabilidad y Análisis',
    'analytics_' => 'Rentabilidad y Análisis',
    'menu_improvement' => 'Rentabilidad y Análisis',
    'profit_' => 'Rentabilidad y Análisis',
    'matrix_bcg' => 'Rentabilidad y Análisis',
    'abc_analysis_' => 'Rentabilidad y Análisis',
    
    // KPI's y Control
    'kpi_' => 'KPI\'s y Control',
    'control_' => 'KPI\'s y Control',
    'compras_' => 'KPI\'s y Control',
    'planeacion_' => 'KPI\'s y Control',
    'comparativa_' => 'KPI\'s y Control',
    'eficiencia_' => 'KPI\'s y Control',
    'mix_' => 'KPI\'s y Control',
    'factibilidad' => 'KPI\'s y Control',
    'estado_' => 'KPI\'s y Control',
    
    // Administración y Configuración
    'users_' => 'Administración y Configuración',
    'roles_' => 'Administración y Configuración',
    'manage_' => 'Administración y Configuración',
    // Recursos Humanos
    'empleado_' => 'Recursos Humanos',
    
    // Dashboard
    'dashboard_' => 'Dashboard',
];

foreach ($allPermissions as $perm) {
    $category = 'Otros';
    foreach ($prefixes as $prefix => $cat) {
        if (strpos($perm->name, $prefix) === 0) {
            $category = $cat;
            break;
        }
    }
    $groups[$category][] = $perm;
}

// Preparar mapa de descripciones de permisos
$permissionDescriptions = [];
foreach ($allPermissions as $perm) {
    $permissionDescriptions[$perm->name] = $perm->description;
}
$this->registerJsVar('permissionDescriptions', $permissionDescriptions);
// URL for AJAX endpoint that returns permissions for a role
$this->registerJsVar('rolePermsUrl', Url::to(['//user/admin/role-permissions']));
// Ordenar permisos por descripción
usort($allPermissions, function($a, $b) {
    return strcmp($a->description, $b->description);
});

$this->registerJsVar('availableTitle', Yii::t('app', "Available permissions"));
$this->registerJsVar('selectedTitle', Yii::t('app', "Additional permissions"));
$this->registerJsVar('addButtonText', Yii::t('app', "Select"));
$this->registerJsVar('addAllButtonText', Yii::t('app', "Select all"));
$this->registerJsVar('removeButtonText', Yii::t('app', "Unselect"));
$this->registerJsVar('removeAllButtonText', Yii::t('app', "Unselect all"));
$this->registerJsVar('searchPlaceholder', Yii::t('app', "Search"));
?>

<div class="card">
    <?php $form = \yii\bootstrap5\ActiveForm::begin([
        'enableAjaxValidation' => true
    ]) ?>
    <div class="card-body">
        <?= $form->field($model, 'name')->textInput() ?>
        <?= $form->field($model, 'email')->textInput() ?>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'role')->dropDownList(
                    \yii\helpers\ArrayHelper::map($roles, 'name', 'description'),
                    ['class' => 'form-control']
                )->label('Rol base <small class="text-muted">(selecciona uno)</small>') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>Permisos del rol seleccionado</label>
                <div id="role-permissions-display" class="border p-2 bg-light">
                    <small class="text-muted">Selecciona un rol para ver sus permisos</small>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>Permisos adicionales asignados</label>
                <div id="additional-permissions-display" class="border p-2 bg-warning">
                    <small class="text-muted">Permisos adicionales al rol</small>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <label>Permisos adicionales <small class="text-muted">(opcional)</small></label>
                <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#permissionsModal" data-additional='<?= json_encode($model->_permissions ?: []) ?>'>Gestionar permisos</button>
                <input type="hidden" name="CreateUserForm[_permissions]" id="permissions-hidden">
            </div>
        </div>
        
        <!-- Campo para Centros de Consumo (solo visible para "Solicitante de Consumo") -->
        <div id="consumption-centers-field" style="display: none;" class="mt-3">
            <label>Centros de Consumo <span class="text-danger">*</span></label>
            <small class="text-muted d-block mb-2">Selecciona los centros de consumo que este usuario puede gestionar. El primero será el predeterminado.</small>
            <?php foreach ($consumptionCenters as $id => $name): ?>
                <div class="form-check">
                    <input type="checkbox" 
                           id="center-<?= $id ?>" 
                           name="CreateUserForm[consumption_center_ids][]" 
                           value="<?= $id ?>" 
                           class="form-check-input consumption-center-checkbox"
                           <?= in_array($id, $model->consumption_center_ids) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="center-<?= $id ?>">
                        <?= $name ?>
                    </label>
                </div>
            <?php endforeach; ?>
            <div class="invalid-feedback" id="consumption-center-error" style="display: none;">
                Debes seleccionar al menos un centro de consumo.
            </div>
        </div>
    </div>
    <div class="card-footer">
        <?= \yii\bootstrap5\Html::submitButton(
            Yii::t('app', "Update"),
            [
                'class' => 'btn btn-success',
                'id' => 'update-button'
            ]
        ) ?>
        <?= \yii\bootstrap5\Html::a(
            Yii::t('app', "Cancel"),
            ['//user/admin/users'],
            [
                'class' => 'btn btn-secondary ms-2'
            ]
        ) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<!-- Modal for permissions -->
<div class="modal fade" id="permissionsModal" tabindex="-1" aria-labelledby="permissionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permissionsModalLabel">Gestionar permisos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="permissions-list">
                    <div class="accordion" id="permissionsAccordion">
                        <?php $index = 0; ?>
                        <?php foreach ($groups as $category => $perms): ?>
                            <?php if (!empty($perms)): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading-<?= $index ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $index ?>" aria-expanded="false" aria-controls="collapse-<?= $index ?>">
                                            <?= $category ?> (<?= count($perms) ?>)
                                        </button>
                                    </h2>
                                    <div id="collapse-<?= $index ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?= $index ?>" data-bs-parent="#permissionsAccordion">
                                        <div class="accordion-body">
                                            <?php foreach ($perms as $perm): ?>
                                                <div class="form-check">
                                                    <input class="form-check-input permission-checkbox" type="checkbox" value="<?= $perm->name ?>" id="perm-<?= $perm->name ?>">
                                                    <label class="form-check-label" for="perm-<?= $perm->name ?>">
                                                        <?= $perm->description ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php $index++; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="save-permissions">Guardar</button>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<< JS
$(function(){
    var additional = JSON.parse($('[data-bs-target="#permissionsModal"]').attr('data-additional') || '[]');
    var currentRolePerms = [];

    // Set initial hidden value
    $('#permissions-hidden').val(JSON.stringify(additional));

    // Function to update checkboxes
    function updateCheckboxes(perms) {
        $('.permission-checkbox').prop('checked', false);
        perms.forEach(function(perm) {
            var permName = typeof perm === 'object' ? perm.name : perm;
            $('#perm-' + permName).prop('checked', true);
        });
    }

    // Function to update role permissions display
    function updateRolePermissionsDisplay(perms) {
        var display = $('#role-permissions-display');
        if (perms.length === 0) {
            display.html('<small class="text-muted">Este rol no tiene permisos asignados</small>');
            return;
        }

        // Group permissions by category
        var roleGroups = {};
        perms.forEach(function(perm) {
            var category = 'Otros';
            
            // Configuración Base
            if (perm.includes('category_') || perm.includes('recipe_category_') || perm.includes('unit_measurement_') || perm.includes('consumption_center_')) {
                category = 'Configuración Base';
            }
            // Costeo
            else if (perm.includes('recipe_') || perm.includes('subrecipe_') || perm.includes('convoy_') || perm.includes('combo_')) {
                category = 'Costeo';
            }
            // Gestión de Insumos y Proveedores
            else if (perm.includes('ingredients_') || perm.includes('providers_')) {
                category = 'Gestión de Insumos y Proveedores';
            }
            // Gastos
            else if (perm.includes('expense_')) {
                category = 'Gastos';
            }
            // Menú y Ventas
            else if (perm.includes('menu_') || perm.includes('sales_')) {
                category = 'Menú y Ventas';
            }
            // Almacén y Movimientos
            else if (perm.includes('movements_') || perm.includes('storage_') || perm.includes('price_trend') || perm.includes('inventory_') || perm.includes('requisitions_')) {
                category = 'Almacén y Movimientos';
            }
            // Rentabilidad y Análisis
            else if (perm.includes('theoretical_') || perm.includes('real_') || perm.includes('charts_') || perm.includes('analytics_') || perm.includes('menu_improvement') || perm.includes('profit_') || perm.includes('matrix_bcg') || perm.includes('abc_analysis_')) {
                category = 'Rentabilidad y Análisis';
            }
            // KPI's y Control
            else if (perm.includes('kpi_') || perm.includes('control_') || perm.includes('compras_') || perm.includes('planeacion_') || perm.includes('comparativa_') || perm.includes('eficiencia_') || perm.includes('mix_') || perm.includes('factibilidad') || perm.includes('estado_')) {
                category = "KPI's y Control";
            }
            // Administración y Configuración
            else if (perm.includes('users_') || perm.includes('roles_') || perm.includes('manage_')) {
                category = 'Administración y Configuración';
            }
            // Dashboard
            else if (perm.includes('dashboard_')) {
                category = 'Dashboard';
            }

            if (!roleGroups[category]) {
                roleGroups[category] = [];
            }
            roleGroups[category].push(perm);
        });

        var html = '';
        for (var category in roleGroups) {
            if (roleGroups[category].length > 0) {
                html += '<strong>' + category + ':</strong><ul class="list-unstyled mb-2" style="margin-left: 10px;">';
                roleGroups[category].forEach(function(perm) {
                    var desc = permissionDescriptions[perm] || perm;
                    html += '<li><small>' + desc + '</small></li>';
                });
                html += '</ul>';
            }
        }
        display.html(html);
    }

    // Function to update additional permissions display
    function updateAdditionalPermissionsDisplay(additionalPerms) {
        var display = $('#additional-permissions-display');
        if (additionalPerms.length === 0) {
            display.html('<small class="text-muted">No hay permisos adicionales</small>');
            return;
        }
        var html = '<ul class="list-unstyled mb-0">';
        additionalPerms.forEach(function(perm) {
            var desc = permissionDescriptions[perm] || perm;
            html += '<li><small>' + desc + '</small></li>';
        });
        html += '</ul>';
        display.html(html);
    }

    // On role change
    $('#createuserform-role').on('change', function() {
        var selectedRole = $(this).val();
        if (!selectedRole) {
            currentRolePerms = [];
            updateCheckboxes(additional);
            updateRolePermissionsDisplay([]);
            return;
        }

        // Fetch role permissions
        $.getJSON(rolePermsUrl, {role: selectedRole})
            .done(function(data) {
                if (Array.isArray(data)) {
                    currentRolePerms = data;
                    var allChecked = [...new Set(data.concat(additional))];
                    updateCheckboxes(allChecked);
                    updateRolePermissionsDisplay(data);
                } else {
                    currentRolePerms = [];
                    updateCheckboxes(additional);
                    updateRolePermissionsDisplay([]);
                }
            })
            .fail(function(xhr, status, error) {
                console.error('Error al obtener permisos del rol:', error);
                currentRolePerms = rolePermissions[selectedRole] || [];
                var allChecked = [...new Set(currentRolePerms.concat(additional))];
                updateCheckboxes(allChecked);
                updateRolePermissionsDisplay(currentRolePerms);
            });
    });

    // Save permissions
    $('#save-permissions').on('click', function() {
        var selected = [];
        $('.permission-checkbox:checked').each(function() {
            selected.push($(this).val());
        });
        // Remove role perms to get additional
        var additionalOnly = selected.filter(function(perm) {
            return currentRolePerms.indexOf(perm) === -1;
        });
        $('#permissions-hidden').val(JSON.stringify(additionalOnly));
        updateAdditionalPermissionsDisplay(additionalOnly);
        $('#permissionsModal').modal('hide');
    });

    // Trigger change on load if role is selected
    if ($('#createuserform-role').val()) {
        $('#createuserform-role').trigger('change');
    }

    // Initial load of additional permissions
    updateAdditionalPermissionsDisplay(additional);

    // Initial load
    $('#createuserform-role').trigger('change');
    
    // Mostrar/ocultar centros de consumo según el rol seleccionado
    function toggleConsumptionCenters() {
        var selectedRole = $('#createuserform-role').val();
        if (selectedRole === 'consumption_requester') {
            $('#consumption-centers-field').slideDown();
        } else {
            $('#consumption-centers-field').slideUp();
            // Desmarcar todos los checkboxes
            $('.consumption-center-checkbox').prop('checked', false);
        }
    }
    
    // Ejecutar al cambiar el rol
    $('#createuserform-role').on('change', function() {
        toggleConsumptionCenters();
    });
    
    // Ejecutar al cargar la página
    toggleConsumptionCenters();
    
    // Handle form submit - ÚNICO MANEJADOR
    $('form').on('submit', function(e) {
        var selectedRole = $('#createuserform-role').val();
        
        // Validar centros de consumo solo si es consumption_requester
        if (selectedRole === 'consumption_requester') {
            var checkedCount = $('.consumption-center-checkbox:checked').length;
            if (checkedCount === 0) {
                e.preventDefault();
                $('#consumption-center-error').show();
                $('.consumption-center-checkbox').first().focus();
                return false;
            } else {
                $('#consumption-center-error').hide();
            }
        }
        
        // Si pasó la validación, mostrar loading en el botón
        var button = $('#update-button');
        button.prop('disabled', true);
        button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Actualizando...');
        
        // Permitir que el formulario se envíe
        return true;
    });
    
    // Ocultar error al seleccionar un checkbox
    $('.consumption-center-checkbox').on('change', function() {
        if ($('.consumption-center-checkbox:checked').length > 0) {
            $('#consumption-center-error').hide();
        }
    });
});
JS;
$this->registerJs($js);
?>
