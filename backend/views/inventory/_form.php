<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

// Obtener centros de consumo para el business
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
$consumptionCenters = \common\models\ConsumptionCenter::find()->where(['business_id' => $business->id])->all();

// Crear parámetros dinámicos para mostrar centros
$areaParams = [];
foreach ($consumptionCenters as $center) {
    $areaParams[] = 'show_' . $center->id;
}

$hasAnyArea = false;
foreach ($areaParams as $p) {
    if (isset($_GET[$p])) {
        $hasAnyArea = true;
        break;
    }
}
if (!$hasAnyArea && !Yii::$app->request->isAjax && count($consumptionCenters) > 0) {
    // Solo si no hay ningún parámetro de área, los ponemos todos en 1
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
$inventarios = $inventarios ?? [];
?>
<div class="inventory-form">
    <?php
    use yii\grid\GridView;
    use common\models\IngredientStockSearch;
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
 <!-- FORMULARIO DE BÚSQUEDA SEPARADO -->
    <?php $searchForm = ActiveForm::begin([
        'id' => 'search-form',
        'method' => 'get',
        'action' => ['inventory/create'], // Mantener en la misma página
        'options' => ['class' => 'mb-3']
    ]); ?>
    
    <div class="row">
        <div class="col-md-4">
            <?= $searchForm->field($searchModel, 'ingredient')->textInput([
                'placeholder' => 'Buscar insumo...',
                'name' => 'IngredientStockSearch[ingredient]'
            ])->label(false) ?>
        </div>
        <div class="col-md-3">
            <?= $searchForm->field($searchModel, 'categoria')->dropDownList(
                \common\models\Category::find()
                ->where([
                            'or',
                            ['business_id' => \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY)['id'] ?? null],
                            ['builtin' => 1]
                        ])
                ->select(['name', 'id'])->indexBy('id')->column(),
                [
                    'class' => 'form-control',
                    'prompt' => 'Todas las familias',
                    'name' => 'IngredientStockSearch[categoria]'
                ]
            )->label(false) ?>
        </div>
        <div class="col-md-5 d-flex align-items-end" style="gap: 10px;">
            <?= Html::submitButton('Buscar', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Limpiar', ['inventory/create'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
    
    <?php ActiveForm::end(); ?>

    <!-- FORMULARIO PRINCIPAL DE INVENTARIO -->
    <?php $form = ActiveForm::begin([
        'id' => 'inventory-form',
        'action' => ['inventory/create'],
        'method' => 'post'
    ]); ?>
    
    
    <div style="margin-bottom: 32px;">
        <?php
        // Solo usar fecha del modelo si ya existe, sino dejar vacío para que JavaScript establezca la fecha local
        $defaultFecha = $model->fecha ? date('Y-m-d\TH:i', strtotime($model->fecha)) : '';
        ?>
        <div style="margin-bottom: 32px;">
            <div style="display: flex; align-items: flex-end; gap: 12px;">
                <?= $form->field($model, 'fecha')->textInput([
                    'type' => 'datetime-local',
                    'id' => 'fecha-inventario',
                    'value' => $defaultFecha,
                    'title' => 'Puedes seleccionar desde 24 horas antes hasta 24 horas después de la fecha actual'
                ]) ?>
                <button type="button" class="btn btn-outline-primary" id="btn-aceptar-fecha">Aceptar</button>
            </div>
            <small class="text-muted" id="rango-fechas">
                Calculando rango permitido...
            </small>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var fechaInput = document.getElementById('fecha-inventario');
            var rangoElement = document.getElementById('rango-fechas');
            
            // Verificar si el business_id es 2342 (sin restricción de fecha)
            var businessId = <?= $business->id ?>;
            var sinRestriccion = (businessId === 2342);
            
            // Calcular rango usando la fecha local del usuario
            var now = new Date();
            var fechaMinima, fechaMaxima;
            
            if (sinRestriccion) {
                // Sin restricción: permitir cualquier fecha
                fechaMinima = new Date('1900-01-01');
                fechaMaxima = new Date('2100-12-31');
            } else {
                // Con restricción: 24 horas antes y después
                fechaMinima = new Date(now.getTime() - (24 * 60 * 60 * 1000)); // 24 horas antes
                fechaMaxima = new Date(now.getTime() + (24 * 60 * 60 * 1000)); // 24 horas después
            }
            
            // Formatear fechas para el input datetime-local
            function formatearFechaParaInput(fecha) {
                var year = fecha.getFullYear();
                var month = String(fecha.getMonth() + 1).padStart(2, '0');
                var day = String(fecha.getDate()).padStart(2, '0');
                var hours = String(fecha.getHours()).padStart(2, '0');
                var minutes = String(fecha.getMinutes()).padStart(2, '0');
                return year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
            }
            
            // Formatear fechas para mostrar al usuario
            function formatearFechaParaMostrar(fecha) {
                var day = String(fecha.getDate()).padStart(2, '0');
                var month = String(fecha.getMonth() + 1).padStart(2, '0');
                var year = fecha.getFullYear();
                var hours = String(fecha.getHours()).padStart(2, '0');
                var minutes = String(fecha.getMinutes()).padStart(2, '0');
                return day + '/' + month + '/' + year + ' ' + hours + ':' + minutes;
            }
            
            // Establecer los límites del input
            var minDate = formatearFechaParaInput(fechaMinima);
            var maxDate = formatearFechaParaInput(fechaMaxima);
            fechaInput.setAttribute('min', minDate);
            fechaInput.setAttribute('max', maxDate);
            
            // Mostrar el rango al usuario
            if (sinRestriccion) {
                rangoElement.textContent = 'Sin restricción de fecha: Puedes seleccionar cualquier fecha';
                rangoElement.style.color = '#28a745';
                rangoElement.style.fontWeight = '600';
            } else {
                rangoElement.textContent = 'Rango permitido: ' + 
                    formatearFechaParaMostrar(fechaMinima) + ' - ' + 
                    formatearFechaParaMostrar(fechaMaxima);
            }
            
            // Establecer fecha/hora local del usuario si no hay valor previo
            if (fechaInput && !fechaInput.value) {
                var fechaActual = formatearFechaParaInput(now);
                fechaInput.value = fechaActual;
            }

            // Permitir decimales con coma o punto en los inputs de inventario
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('inventory-input')) {
                    // Si el usuario pone una coma, la convertimos a punto
                    if (e.target.value.includes(',')) {
                        e.target.value = e.target.value.replace(/,/g, '.');
                    }
                }
            });
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
            <span style="font-weight:600; margin-right:12px;">Centros de consumo a mostrar:</span>
            <?php foreach ($consumptionCenters as $center): ?>
                <input type="checkbox" class="btn-check" id="center-<?= $center->id ?>-check" name="show_<?= $center->id ?>" value="1" <?= isset($_GET['show_' . $center->id]) ? 'checked' : '' ?>>
                <label class="btn btn-outline-success btn-sm" for="center-<?= $center->id ?>-check"><?= Html::encode($center->name) ?></label>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn btn-primary btn-sm" id="filter-columns-btn" style="margin-left:18px;">Filtrar columnas</button>
    </div>

    <?php
    // Construir columnas dinámicas
    $columns = [
        [
            'attribute' => 'ingredient',
            'label' => 'Insumo',
            'filter' => false
        ],
        [
            'label' => 'Unidad<br>Uso',
            'encodeLabel' => false,
            'value' => function($insumo) {
                return isset($insumo->portion_um) ? $insumo->portion_um : '-';
            },
            'headerOptions' => ['style' => 'min-width: 120px; width: 10%;'],
        ],
        [
            'label' => 'Familias',
            'encodeLabel' => false,
            'value' => function($insumo) {
                return $insumo->category && isset($insumo->category->name) ? $insumo->category->name : '-';
            },
            'headerOptions' => ['style' => 'min-width: 120px; width: 12%;'],
        ],
    ];

    // Agregar columnas para centros de consumo
    foreach ($consumptionCenters as $center) {
        if (isset($_GET['show_' . $center->id])) {
            $columns[] = [
                'label' => Html::encode($center->name),
                'format' => 'raw',
                'value' => function($insumo) use ($center, $inventarios) {
                    $value = isset($inventarios[$insumo->id][$center->id]) ? $inventarios[$insumo->id][$center->id] : '';
                    return Html::textInput("inventario[{$insumo->id}][{$center->id}]", $value, [
                        'class' => 'form-control inventory-input', 
                        'type' => 'number', 
                        'step' => '0.001',
                        'data-ingredient-id' => $insumo->id,
                        'data-area' => '<?= $center->id ?>'
                    ]);
                }
            ];
        }
    }
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        //'filter' => false,
        'tableOptions' => ['class' => 'table table-striped sticky-header-table'],
        'options' => ['class' => 'grid-view sticky-header-grid'],
        'layout' => "{items}\n<div class='d-flex justify-content-between align-items-center mt-3'><div>{pager}</div><div>{summary}</div></div>",
        'columns' => $columns,
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
    // ========== CONFIGURACIÓN INICIAL ==========
    
    var form = document.getElementById('inventory-form');
    var searchForm = document.getElementById('search-form');
    var btnGuardar = document.getElementById('btn-guardar-inventario');
    var fechaInput = document.getElementById('fecha-inventario');
    var btnCancelar = document.getElementById('btn-cancelar-inventario');
    
    // ========== FUNCIONES DE AUTO-GUARDADO ==========
    
    // Cargar datos guardados del localStorage
    function cargarDatosGuardados() {
        try {
            var savedData = JSON.parse(localStorage.getItem('inventory_draft') || '{}');

            // Restaurar fecha
            if (savedData.fecha && fechaInput) {
                fechaInput.value = savedData.fecha;
            }

            // Restaurar valores de los inputs de inventario
            var inventoryInputs = document.querySelectorAll('input.inventory-input');
            inventoryInputs.forEach(function(input) {
                var ingredientId = input.getAttribute('data-ingredient-id');
                var area = input.getAttribute('data-area');
                
                if (ingredientId && area && savedData.insumos && savedData.insumos[ingredientId]) {
                    var savedValue = savedData.insumos[ingredientId][area];
                    if (savedValue !== undefined && savedValue !== null && savedValue !== '') {
                        input.value = savedValue;
                    }
                }
            });

            // Mostrar notificación si hay datos guardados
            if (savedData.insumos && Object.keys(savedData.insumos).length > 0) {
                //mostrarNotificacion('Se han restaurado datos no guardados de una sesión anterior.');
            }

        } catch (error) {
            console.error('❌ Error al cargar datos guardados:', error);
        }
    }
    
    // Guardar datos en localStorage
    function guardarEnLocalStorage() {
        try {
            var savedData = JSON.parse(localStorage.getItem('inventory_draft') || '{}');
            var insumosData = savedData.insumos || {};
            
            // Recopilar todos los inputs de inventario de la página actual
            var inventoryInputs = document.querySelectorAll('input.inventory-input');
            var cambiosDetectados = false;
            
            inventoryInputs.forEach(function(input) {
                var ingredientId = input.getAttribute('data-ingredient-id');
                var area = input.getAttribute('data-area');
                var value = input.value.trim();
                
                if (ingredientId && area) {
                    if (!insumosData[ingredientId]) {
                        insumosData[ingredientId] = {};
                    }
                    
                    // Solo guardar si el valor cambió
                    var valorAnterior = insumosData[ingredientId][area];
                    if (valorAnterior !== value) {
                        insumosData[ingredientId][area] = value;
                        cambiosDetectados = true;
                    }
                }
            });

            var dataToSave = {
                fecha: fechaInput ? fechaInput.value : '',
                insumos: insumosData,
                timestamp: new Date().toISOString()
            };
            
            localStorage.setItem('inventory_draft', JSON.stringify(dataToSave));
            
        } catch (error) {
            console.error('❌ Error al guardar en localStorage:', error);
        }
    }
    
    // ========== EVENT LISTENERS PARA AUTO-GUARDADO ==========
    
    // Guardar cuando cambia cualquier input de inventario
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('inventory-input')) {
            guardarEnLocalStorage();
        }
    });
    
    // Guardar cuando cambia la fecha
    if (fechaInput) {
        fechaInput.addEventListener('input', function() {
            guardarEnLocalStorage();
        });
        
        fechaInput.addEventListener('change', function() {
            guardarEnLocalStorage();
        });
    }
    
    // Guardar cuando se pierde el foco de un input
    document.addEventListener('blur', function(e) {
        if (e.target.classList.contains('inventory-input')) {
            guardarEnLocalStorage();
        }
    }, true);
    
    // ========== MANEJO DEL FORMULARIO PRINCIPAL ==========
    
    if (form) {
        form.addEventListener('submit', function(e) {
            
            // Validar fecha
            if (!fechaInput || !fechaInput.value) {
                e.preventDefault();
                fechaInput.focus();
                fechaInput.classList.add('is-invalid');
                alert('Por favor, ingresa una fecha antes de guardar.');
                return false;
            }
            
            
            // Preparar datos para enviar
            var savedData = JSON.parse(localStorage.getItem('inventory_draft') || '{}');
            if (savedData.insumos) {
                // Crear inputs ocultos con los datos guardados
                Object.keys(savedData.insumos).forEach(function(ingredientId) {
                    <?php foreach ($consumptionCenters as $center): ?>
                        var value = savedData.insumos[ingredientId]['<?= $center->id ?>'];
                        if (typeof value !== 'undefined' && value !== null && value !== '') {
                            var inputName = `inventario[${ingredientId}][<?= $center->id ?>]`;
                            
                            // Buscar si ya existe el input
                            var existingInput = document.querySelector(`input[name="${inputName}"]`);
                            if (existingInput) {
                                existingInput.value = value;
                            } else {
                                // Crear input oculto si no existe
                                var hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = inputName;
                                hiddenInput.value = value;
                                form.appendChild(hiddenInput);
                            }
                        }
                    <?php endforeach; ?>
                });
            }
            
            // Cambiar estado del botón
            if (btnGuardar) {
                btnGuardar.disabled = true;
                btnGuardar.innerHTML = 'Guardando...';
            }
            
            // Limpiar localStorage después de enviar
            setTimeout(function() {
                localStorage.removeItem('inventory_draft');
            }, 1000);
            
            return true;
        });
    }
    
    // ========== MANEJO DEL BOTÓN CANCELAR ==========
    
    if (btnCancelar) {
        btnCancelar.addEventListener('click', function(e) {
            var savedData = JSON.parse(localStorage.getItem('inventory_draft') || '{}');
            var hasUnsavedData = false;
            
            // Verificar si hay datos sin guardar
            if (savedData.insumos) {
                Object.keys(savedData.insumos).forEach(function(ingredientId) {
                    var areas = Object.keys(savedData.insumos[ingredientId]);
                    if (areas.length > 0) {
                        hasUnsavedData = true;
                    }
                });
            }
            
            if (hasUnsavedData || (savedData.fecha && savedData.fecha !== '')) {
                if (!confirm('Tienes datos sin guardar. ¿Seguro que quieres cancelar y perder los datos?')) {
                    e.preventDefault();
                    return;
                }
            }
            
            localStorage.removeItem('inventory_draft');
            window.location.href = '/inventory/index';
        });
    }
    
    // ========== MANEJO DE FORMULARIO DE BÚSQUEDA ==========
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            // NO guardar en localStorage para búsquedas
        });
    }
    
    // ========== MANEJO DE PAGINACIÓN Y FILTROS ==========
    
    // Selector de elementos por página
    var perPageSelector = document.getElementById('per-page-selector');
    if (perPageSelector) {
        perPageSelector.addEventListener('change', function() {
            // Guardar antes de cambiar de página
            guardarEnLocalStorage();
            
            const pageSize = this.value;
            let url = new URL(window.location);
            url.searchParams.set('per-page', pageSize);
            
            <?php 
            foreach ($consumptionCenters as $center) {
                if (isset($_GET['show_' . $center->id])) {
                    echo "url.searchParams.set('show_{$center->id}', '1');";
                }
            }
            ?>
            
            window.location.href = url.toString();
        });
    }
    
    // Filtro de columnas
    var filterColumnsBtn = document.getElementById('filter-columns-btn');
    if (filterColumnsBtn) {
        filterColumnsBtn.addEventListener('click', function() {
            guardarEnLocalStorage();
            
            let url = new URL(window.location);
            
            <?php 
            foreach ($consumptionCenters as $center) {
                echo "url.searchParams.delete('show_{$center->id}');";
            }
            ?>
            
            var checkboxes = document.querySelectorAll('input[type="checkbox"][name^="show_"]:checked');
            checkboxes.forEach(function(checkbox) {
                url.searchParams.set(checkbox.name, '1');
            });
            
            window.location.href = url.toString();
        });
    }
    
    // ========== FUNCIONES AUXILIARES ==========
    
    function mostrarNotificacion(mensaje) {
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
    
    function validarFecha() {
        if (fechaInput && btnGuardar) {
            if (!fechaInput.value) {
                btnGuardar.disabled = true;
            } else {
                btnGuardar.disabled = false;
            }
        }
    }
    
    // ========== INICIALIZACIÓN ==========
    
    // Cargar datos al iniciar
    cargarDatosGuardados();
    
    // Validar fecha inicial
    validarFecha();
    
    // Validar fecha cuando cambie
    if (fechaInput) {
        fechaInput.addEventListener('input', validarFecha);
    }
});

// Permitir decimales con coma o punto
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('inventory-input')) {
        if (e.target.value.includes(',')) {
            e.target.value = e.target.value.replace(/,/g, '.');
        }
    }
});

// Botón aceptar fecha
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
</script>