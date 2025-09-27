<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

// Redireccionar si no están todos los parámetros de áreas
$areaParams = ['show_almacen', 'show_cocina', 'show_barra', 'show_servicio', 'show_otro'];
$missing = array_filter($areaParams, function($p) { return !isset($_GET[$p]); });
if (count($missing) > 0 && !Yii::$app->request->isAjax) {
    $url = Yii::$app->request->url;
    $parsed = parse_url($url);
    $base = $parsed['path'];
    $query = isset($parsed['query']) ? $parsed['query'] : '';
    parse_str($query, $params);
    foreach ($areaParams as $p) {
        $params[$p] = 1;
    }
    $newQuery = http_build_query($params);
    $redirectUrl = $base . '?' . $newQuery;
    Yii::$app->response->redirect($redirectUrl)->send();
    return;
}

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
    
    if (isset($_GET['per-page'])) {
        if ($_GET['per-page'] === 'all') {
            $dataProvider->pagination = false;
            echo '<div class="alert alert-warning" style="margin-bottom:16px;">Se están mostrando todos los insumos. Si tienes muchos, la carga puede ser lenta.</div>';
        } else {
            $dataProvider->pagination->pageSize = (int)$_GET['per-page'];
        }
    }
    ?>

    <?php $form = ActiveForm::begin([
        'id' => 'inventory-form',
        'action' => ['inventory/create'],
        'method' => 'post'
    ]); ?>
    
    <div style="margin-bottom: 32px;">
        <?php
        // Set default value to current date/time if not already set
        $defaultFecha = $model->fecha ? date('Y-m-d\TH:i', strtotime($model->fecha)) : date('Y-m-d\TH:i');
        ?>
            <div style="margin-bottom: 32px;">
            <div style="display: flex; align-items: flex-end; gap: 12px;">
                    <?= $form->field($model, 'fecha')->textInput([
                        'type' => 'datetime-local',
                        'id' => 'fecha-inventario',
                        'value' => $defaultFecha
                    ]) ?>
                    <button type="button" class="btn btn-outline-primary" id="btn-aceptar-fecha">Aceptar</button>
                </div>
            </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var btnAceptarFecha = document.getElementById('btn-aceptar-fecha');
        var fechaInput = document.getElementById('fecha-inventario');
        if (btnAceptarFecha && fechaInput) {
            btnAceptarFecha.addEventListener('click', function() {
                fechaInput.blur();
                btnAceptarFecha.classList.add('btn-success');
                btnAceptarFecha.classList.remove('btn-outline-primary');
                setTimeout(function() {
                    btnAceptarFecha.classList.remove('btn-success');
                    btnAceptarFecha.classList.add('btn-outline-primary');
                }, 1200);
            });
        }
    });
    </script>
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
    .column-toggle-group {
        display: flex;
        gap: 8px;
        align-items: center;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px 18px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        margin-bottom: 8px;
    }
    .btn-check:checked + .btn {
        background-color: #198754;
        color: #fff;
        border-color: #198754;
        font-weight: 600;
    }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <div class="d-inline-block">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><?= Yii::t('app', 'Mostrar') ?></span>
                    <select name="per-page" id="per-page-selector" class="form-select" style="width:80px;">
                        <?php foreach ([10, 20, 50, 100, 200, 500] as $size): ?>
                            <option value="<?= $size ?>" <?= (isset($_GET['per-page']) && $_GET['per-page'] == $size) ? 'selected' : '' ?>><?= $size ?></option>
                        <?php endforeach; ?>
                        <option value="all" <?= (isset($_GET['per-page']) && $_GET['per-page'] == 'all') ? 'selected' : '' ?>>Todos</option>
                    </select>
                    <span class="input-group-text bg-light"><?= Yii::t('app', 'elementos') ?></span>
                </div>
            </div>
        </div>
        <div></div>
    </div>

    <div class="table-responsive sticky-header-container">
    
    <div class="mb-3" style="display:flex;gap:16px;align-items:center;">
        <div class="column-toggle-group">
            <span style="font-weight:600; margin-right:12px;">Áreas a mostrar:</span>
            <input type="checkbox" class="btn-check" id="almacen-check" name="show_almacen" value="1" <?= isset($_GET['show_almacen']) ? 'checked' : '' ?>>
            <label class="btn btn-outline-success btn-sm" for="almacen-check">Almacén</label>
            <input type="checkbox" class="btn-check" id="cocina-check" name="show_cocina" value="1" <?= isset($_GET['show_cocina']) ? 'checked' : '' ?>>
            <label class="btn btn-outline-success btn-sm" for="cocina-check">Cocina</label>
            <input type="checkbox" class="btn-check" id="barra-check" name="show_barra" value="1" <?= isset($_GET['show_barra']) ? 'checked' : '' ?>>
            <label class="btn btn-outline-success btn-sm" for="barra-check">Barra</label>
            <input type="checkbox" class="btn-check" id="servicio-check" name="show_servicio" value="1" <?= isset($_GET['show_servicio']) ? 'checked' : '' ?>>
            <label class="btn btn-outline-success btn-sm" for="servicio-check">Servicio</label>
            <input type="checkbox" class="btn-check" id="otro-check" name="show_otro" value="1" <?= isset($_GET['show_otro']) ? 'checked' : '' ?>>
            <label class="btn btn-outline-success btn-sm" for="otro-check">Otro</label>
        </div>
        <button type="button" class="btn btn-primary btn-sm" id="filter-columns-btn" style="margin-left:18px;">Filtrar columnas</button>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'tableOptions' => ['class' => 'table table-striped sticky-header-table'],
        'options' => ['class' => 'grid-view sticky-header-grid'],
        'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center mt-3'><div>{pager}</div><div>{summary}</div></div>",
        'columns' => array_values(array_filter([
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
                        'prompt' => 'Todas',
                        'onchange' => 'this.form.method=\'get\';this.form.submit();'
                    ]
                ),
            ],
            isset($_GET['show_almacen']) ? [
                'label' => 'Almacén',
                'format' => 'raw',
                'value' => function($insumo) {
                    return Html::textInput("inventario[{$insumo->id}][inventario_almacen]", null, [
                        'class' => 'form-control inventory-input', 
                        'type' => 'number', 
                        'step' => '0.001',
                        'data-ingredient-id' => $insumo->id,
                        'data-area' => 'almacen'
                    ]);
                }
            ] : null,
            isset($_GET['show_cocina']) ? [
                'label' => 'Cocina',
                'format' => 'raw',
                'value' => function($insumo) {
                    return Html::textInput("inventario[{$insumo->id}][inventario_cocina]", null, [
                        'class' => 'form-control inventory-input', 
                        'type' => 'number', 
                        'step' => '0.001',
                        'data-ingredient-id' => $insumo->id,
                        'data-area' => 'cocina'
                    ]);
                }
            ] : null,
            isset($_GET['show_barra']) ? [
                'label' => 'Barra',
                'format' => 'raw',
                'value' => function($insumo) {
                    return Html::textInput("inventario[{$insumo->id}][inventario_barra]", null, [
                        'class' => 'form-control inventory-input', 
                        'type' => 'number', 
                        'step' => '0.001',
                        'data-ingredient-id' => $insumo->id,
                        'data-area' => 'barra'
                    ]);
                }
            ] : null,
            isset($_GET['show_servicio']) ? [
                'label' => 'Servicio',
                'format' => 'raw',
                'value' => function($insumo) {
                    return Html::textInput("inventario[{$insumo->id}][inventario_servicio]", null, [
                        'class' => 'form-control inventory-input', 
                        'type' => 'number', 
                        'step' => '0.001',
                        'data-ingredient-id' => $insumo->id,
                        'data-area' => 'servicio'
                    ]);
                }
            ] : null,
            isset($_GET['show_otro']) ? [
                'label' => 'Otro',
                'format' => 'raw',
                'value' => function($insumo) {
                    return Html::textInput("inventario[{$insumo->id}][inventario_otro]", null, [
                        'class' => 'form-control inventory-input', 
                        'type' => 'number', 
                        'step' => '0.001',
                        'data-ingredient-id' => $insumo->id,
                        'data-area' => 'otro'
                    ]);
                }
            ] : null,
        ])),
    ]) ?>
    </div>

    <div style="position: fixed; bottom: 32px; right: 32px; z-index: 1000;">
        <div style="display: flex; gap: 16px;">
            <button type="submit" class="btn btn-success" 
                    style="box-shadow: 0 2px 8px rgba(0,0,0,0.15); font-size: 18px; padding: 12px 32px;"
                    id="btn-guardar-inventario">
                Guardar
            </button>
            <button type="button" class="btn btn-outline-danger" 
                    style="box-shadow: 0 2px 8px rgba(0,0,0,0.10); font-size: 18px; padding: 12px 32px;"
                    id="btn-cancelar-inventario">
                Cancelar
            </button>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========== ENVIAR TODOS LOS INSUMOS EDITADOS AL GUARDAR ==========
    var form = document.getElementById('inventory-form');
    form.addEventListener('submit', function(e) {
        var savedData = JSON.parse(localStorage.getItem('inventory_draft') || '{}');
        if (savedData.insumos) {
            // Elimina inputs temporales previos
            var tempInputs = document.querySelectorAll('.temp-inventario-input');
            tempInputs.forEach(function(input) { input.remove(); });

            // Áreas posibles
            var areas = ['almacen', 'cocina', 'barra', 'servicio', 'otro'];
            Object.keys(savedData.insumos).forEach(function(ingredientId) {
                areas.forEach(function(area) {
                    var value = savedData.insumos[ingredientId][area];
                    if (typeof value !== 'undefined' && value !== null && value !== '') {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `inventario[${ingredientId}][inventario_${area}]`;
                        input.value = value;
                        input.className = 'temp-inventario-input';
                        form.appendChild(input);
                    }
                });
            });

            // Eliminar los inputs ocultos después de enviar el formulario (para no interferir con otros eventos)
            setTimeout(function() {
                var tempInputs = document.querySelectorAll('.temp-inventario-input');
                tempInputs.forEach(function(input) { input.remove(); });
            }, 1000);
        }
    }, true);

    // ========== BOTÓN CANCELAR ==========
    var btnCancelar = document.getElementById('btn-cancelar-inventario');
    if (btnCancelar) {
        btnCancelar.addEventListener('click', function(e) {
            var savedData = JSON.parse(localStorage.getItem('inventory_draft') || '{}');
            var hasUnsavedData = false;
            if ((savedData.insumos && Object.keys(savedData.insumos).length > 0) || (savedData.fecha && savedData.fecha !== '')) {
                if (!confirm('Tienes datos sin guardar. ¿Seguro que quieres cancelar y perder los datos?')) {
                    e.preventDefault();
                    return;
                }
            }
            localStorage.removeItem('inventory_draft');
            window.location.href = '/inventory/index'; // Ajusta la ruta si es necesario
        });
    }

    console.log('=== PÁGINA CARGADA ===');
    
    var btnGuardar = document.getElementById('btn-guardar-inventario');
    var fechaInput = document.getElementById('fecha-inventario');
    
    console.log('Botón encontrado:', !!btnGuardar);
    console.log('Formulario encontrado:', !!form);
    console.log('Fecha input encontrado:', !!fechaInput);
    
    // ========== AUTO-GUARDADO EN LOCALSTORAGE ==========
    
    // Cargar datos guardados del localStorage (acumulativo)
    function cargarDatosGuardados() {
        try {
            var savedData = JSON.parse(localStorage.getItem('inventory_draft') || '{}');
            console.log('Datos cargados del localStorage:', savedData);

            // Restaurar fecha
            if (savedData.fecha && fechaInput) {
                fechaInput.value = savedData.fecha;
                console.log('Fecha restaurada:', savedData.fecha);
            }

            // Restaurar SOLO los insumos presentes en la página actual
            var inventoryInputs = document.querySelectorAll('input[name^="inventario["]');
            inventoryInputs.forEach(function(input) {
                var name = input.name;
                var match = name.match(/inventario\[(\d+)\]\[inventario_(\w+)\]/);
                if (match) {
                    var ingredientId = match[1];
                    var area = match[2];
                    if (
                        savedData.insumos &&
                        savedData.insumos[ingredientId] &&
                        savedData.insumos[ingredientId][area] !== undefined &&
                        savedData.insumos[ingredientId][area] !== null
                    ) {
                        input.value = savedData.insumos[ingredientId][area];
                        console.log('Dato restaurado:', ingredientId, area, savedData.insumos[ingredientId][area]);
                    }
                }
            });

            // Mostrar notificación si hay datos guardados
            if (savedData.insumos && Object.keys(savedData.insumos).length > 0) {
                mostrarNotificacion('Se han restaurado datos no guardados de una sesión anterior.');
            }

            return savedData;
        } catch (error) {
            console.error('Error al cargar datos guardados:', error);
            return {fecha: '', insumos: {}};
        }
    }
    
    // Guardar datos en localStorage (acumulativo)
    function guardarEnLocalStorage() {
        var savedData = JSON.parse(localStorage.getItem('inventory_draft') || '{}');
        var mergedInsumos = savedData.insumos ? JSON.parse(JSON.stringify(savedData.insumos)) : {};
        var fecha = fechaInput ? fechaInput.value : '';

        // Recopilar todos los inputs de inventario de la página actual
        var inventoryInputs = document.querySelectorAll('input[name^="inventario["]');
        inventoryInputs.forEach(function(input) {
            var name = input.name;
            var match = name.match(/inventario\[(\d+)\]\[inventario_(\w+)\]/);
            if (match) {
                var ingredientId = match[1];
                var area = match[2];
                var value = input.value.trim();
                if (!mergedInsumos[ingredientId]) {
                    mergedInsumos[ingredientId] = {};
                }
                if (value !== '') {
                    mergedInsumos[ingredientId][area] = value;
                }
            }
        });

        var dataToSave = {
            fecha: fecha,
            insumos: mergedInsumos,
            timestamp: new Date().toISOString()
        };
        localStorage.setItem('inventory_draft', JSON.stringify(dataToSave));
        console.log('Datos guardados en localStorage:', dataToSave);
    }
    
    // Mostrar notificación
    function mostrarNotificacion(mensaje) {
        // Crear notificación temporal
        var notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 4px;
            border: 1px solid #c3e6cb;
            z-index: 10000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        `;
        notification.textContent = mensaje;
        document.body.appendChild(notification);
        
        setTimeout(function() {
            notification.remove();
        }, 4000);
    }
    
    // ========== VALIDACIÓN Y MANEJO DEL FORMULARIO ==========
    
    // Validar fecha al cargar la página
    function validarFecha() {
        if (!fechaInput.value) {
            btnGuardar.disabled = true;
            console.log('Fecha vacía - botón deshabilitado');
        } else {
            btnGuardar.disabled = false;
            console.log('Fecha llena - botón habilitado');
        }
    }
    
    // Manejar el envío del formulario
    form.addEventListener('submit', function(e) {
        console.log('=== EVENTO SUBMIT DEL FORMULARIO ===');
        
        if (!fechaInput.value) {
            e.preventDefault();
            console.log('Fecha vacía - previniendo envío');
            fechaInput.focus();
            fechaInput.classList.add('is-invalid');
            alert('Por favor, ingresa una fecha antes de guardar.');
            return false;
        }
        
        console.log('Fecha válida - enviando formulario');
        
        // Limpiar datos guardados
        localStorage.removeItem('inventory_draft');
        console.log('LocalStorage limpiado después del guardado');
        
        // Cambiar texto del botón
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = 'Guardando...';
        
        return true;
    });
    
    // ========== EVENT LISTENERS PARA AUTO-GUARDADO ==========
    
    // Cargar datos al iniciar
    cargarDatosGuardados();
    
    // Guardar automáticamente cuando cambia la fecha
    if (fechaInput) {
        fechaInput.addEventListener('input', function() {
            guardarEnLocalStorage();
            console.log('Fecha guardada:', this.value);
        });
    }
    
    // Guardar automáticamente cuando cambia cualquier input de inventario
    document.addEventListener('input', function(e) {
        if (e.target.name && e.target.name.includes('inventario')) {
            guardarEnLocalStorage();
            console.log('Input de inventario cambiado:', e.target.name, e.target.value);
        }
    });
    
    // Guardar ANTES de cambiar de página (paginación)
    document.addEventListener('click', function(e) {
        var pagLink = e.target.closest('.pagination a');
        if (pagLink) {
            e.preventDefault();
            guardarEnLocalStorage();
            console.log('Guardando antes de cambiar de página...');
            window.location.href = pagLink.href;
        }
    });

    // Guardar ANTES de filtrar por categoría
    var catFilter = document.querySelector('select[name="IngredientStockSearch[categoria]"]');
    if (catFilter) {
        catFilter.addEventListener('change', function() {
            guardarEnLocalStorage();
            console.log('Guardando antes de filtrar por categoría...');
        });
    }

    // Guardar ANTES de filtrar por insumo (buscador)
    var insumoFilter = document.querySelector('input[name="IngredientStockSearch[ingredient]"]');
    if (insumoFilter) {
        insumoFilter.addEventListener('change', function() {
            guardarEnLocalStorage();
            console.log('Guardando antes de filtrar por insumo...');
        });
        insumoFilter.addEventListener('blur', function() {
            guardarEnLocalStorage();
            console.log('Guardando antes de filtrar por insumo (blur)...');
        });
    }
    
    // ========== INICIALIZACIÓN ==========
    
    validarFecha();
    if (fechaInput) {
        fechaInput.addEventListener('input', validarFecha);
    }
    
    // Evento de click solo para logging
    btnGuardar.addEventListener('click', function(e) {
        console.log('=== CLICK EN BOTÓN ===');
        console.log('Valor de fecha:', fechaInput.value);
    });
    
    // ========== MANEJO DE FILTROS Y PAGINACIÓN ==========
    
    // Manejo del selector de elementos por página
    var perPageSelector = document.getElementById('per-page-selector');
    if (perPageSelector) {
        perPageSelector.addEventListener('change', function() {
            guardarEnLocalStorage(); // Guardar antes de cambiar
            
            const pageSize = this.value;
            let url = new URL(window.location);
            url.searchParams.set('per-page', pageSize);
            
            // Mantener los parámetros de filtro de columnas
            <?php 
            $showParams = ['show_almacen', 'show_cocina', 'show_barra', 'show_servicio', 'show_otro'];
            foreach ($showParams as $param) {
                if (isset($_GET[$param])) {
                    echo "url.searchParams.set('{$param}', '1');";
                }
            }
            ?>
            
            window.location.href = url.toString();
        });
    }

    // ========== CORRECCIÓN DEL BOTÓN FILTRAR COLUMNAS ==========
    var filterColumnsBtn = document.getElementById('filter-columns-btn');
    if (filterColumnsBtn) {
        filterColumnsBtn.addEventListener('click', function() {
            guardarEnLocalStorage(); // Guardar antes de cambiar
            
            // Crear URL base manteniendo todos los parámetros existentes
            let url = new URL(window.location);
            
            // Eliminar solo los parámetros de columnas anteriores
            <?php 
            foreach ($showParams as $param) {
                echo "url.searchParams.delete('{$param}');";
            }
            ?>
            
            // Agregar los checkboxes seleccionados
            var checkboxes = document.querySelectorAll('input[type="checkbox"][name^="show_"]:checked');
            checkboxes.forEach(function(checkbox) {
                url.searchParams.set(checkbox.name, '1');
            });
            
            // Si no hay checkboxes seleccionados, redireccionar sin parámetros de columnas
            if (checkboxes.length === 0) {
                // Ya eliminamos los parámetros, así que solo redireccionamos
            }
            
            console.log('Redireccionando a:', url.toString());
            window.location.href = url.toString();
        });
    }
    
    // ========== MEJORA ADICIONAL: ENVIAR FORMULARIO AL FILTRAR ==========
    // Esto asegura que los filtros de búsqueda funcionen correctamente
    var searchForm = document.querySelector('form[method="get"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function() {
            guardarEnLocalStorage();
        });
    }
    
    console.log('=== AUTO-GUARDADO CONFIGURADO CORRECTAMENTE ===');
});
</script>