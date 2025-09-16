<?php
/** @var $this \yii\web\View */
/** @var $foodDataProvider \yii\data\ActiveDataProvider */
/** @var $drinkDataProvider \yii\data\ActiveDataProvider */
/** @var $comboDataProvider \yii\data\ActiveDataProvider */
/** @var $foodSearchModel \common\models\StandardRecipeSearch */
/** @var $drinkSearchModel \common\models\StandardRecipeSearch */
/** @var $comboSearchModel \common\models\MenuSearch */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;

/**
 * Función auxiliar que devuelve el nombre del mes en español
 * @param int $month Número del mes (1-12)
 * @return string Nombre del mes en español
 */
function getMonthName($month) {
    if ($month == 0) {
        return 'Todos los meses';
    }
    $months = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre'
    ];
    return isset($months[$month]) ? $months[$month] : '';
}

$this->title = Yii::t('app', "Sales");

// Preparamos las columnas para las tablas de alimentos
$foodGridColumns = [
    ['class' => \yii\grid\SerialColumn::class],
    [
        'attribute' => 'title',
        'label' => 'Nombre de la receta',
        'filter' => '<div class="position-relative">' . 
            Html::textInput('food_title', 
                Yii::$app->request->get('food_title', ''), 
                [
                    'class' => 'form-control pr-4',
                    'placeholder' => 'Buscar alimento...',
                    'onkeypress' => 'if(event.keyCode == 13) { filterFoodSearch(this.value); return false; }',
                    'onblur' => 'filterFoodSearch(this.value)',
                    'id' => 'food-search-input'
                ]
            ) . 
            '<button type="button" class="btn btn-link position-absolute clear-search-btn" onclick="clearFoodSearch()" style="right: 5px; top: 50%; transform: translateY(-50%); padding: 2px 4px; border: none; background: none; color: #6c757d; font-size: 16px; line-height: 1; display: ' . (Yii::$app->request->get('food_title', '') ? 'block' : 'none') . '; z-index: 10;">&times;</button>' .
            '</div>'
    ],
    [
        'attribute' => 'cost',
        'label' => "Costo",
        'value' => function ($data) {
            return formatCost($data->cost);
        },
        'filter' => false
    ],
    [
        'attribute' => 'costPercent',
        'label' => 'Porcentaje de Costo',
        'value' => function ($data) {
            return formatPercentage($data->costPercent*100);
        },
        'filter' => false
    ],
    [
        'label' => Yii::t('app', "Sales"),
        'format' => 'raw',
        'filter' => false,
        'value' => function ($data) use ($selectedMonth, $selectedYear) {
            $inputName = 'food[' . $data->id . ']';
            // Convert sales to integer to remove decimal places
            $integerValue = intval($data->sales);
            return Html::input('number', $inputName, $integerValue, [
                'class' => 'form-control sales-input',
                'data-id' => $data->id,
                'data-type' => 'recipe',
                'min' => '0',
                'step' => '1',
                'pattern' => '[0-9]*'
            ]);
        },
    ]
];

// Preparamos las columnas para las tablas de bebidas
$drinkGridColumns = [
    ['class' => \yii\grid\SerialColumn::class],
    [
        'attribute' => 'title',
        'label' => 'Nombre de la receta',
        'filter' => '<div class="position-relative">' . 
            Html::textInput('drink_title', 
                Yii::$app->request->get('drink_title', ''), 
                [
                    'class' => 'form-control pr-4',
                    'placeholder' => 'Buscar bebida...',
                    'onkeypress' => 'if(event.keyCode == 13) { filterDrinkSearch(this.value); return false; }',
                    'onblur' => 'filterDrinkSearch(this.value)',
                    'id' => 'drink-search-input'
                ]
            ) . 
            '<button type="button" class="btn btn-link position-absolute clear-search-btn" onclick="clearDrinkSearch()" style="right: 5px; top: 50%; transform: translateY(-50%); padding: 2px 4px; border: none; background: none; color: #6c757d; font-size: 16px; line-height: 1; display: ' . (Yii::$app->request->get('drink_title', '') ? 'block' : 'none') . '; z-index: 10;">&times;</button>' .
            '</div>'
    ],    [
        'attribute' => 'cost',
        'label' => "Costo",
        'value' => function ($data) {
            return formatCost($data->cost);
        },
        'filter' => false
    ],    
    [
        'attribute' => 'costPercent',
        'label' => 'Porcentaje de Costo',
        'value' => function ($data) {
            return formatPercentage($data->costPercent*100);
        },
        'filter' => false
    ],
    [
        'label' => Yii::t('app', "Sales"),
        'format' => 'raw',
        'filter' => false,
        'value' => function ($data) use ($selectedMonth, $selectedYear) {
            $inputName = 'drink[' . $data->id . ']';
            // Convert sales to integer to remove decimal places
            $integerValue = intval($data->sales);
            return Html::input('number', $inputName, $integerValue, [
                'class' => 'form-control sales-input',
                'data-id' => $data->id,
                'data-type' => 'recipe',
                'min' => '0',
                'step' => '1',
                'pattern' => '[0-9]*'
            ]);
        },
    ]
];

// Preparamos las columnas para las tablas de combos
$comboGridColumns = [
    ['class' => \yii\grid\SerialColumn::class],
    [
        'attribute' => 'name',
        'label' => 'Nombre del combo',
        'filter' => '<div class="position-relative">' . 
            Html::textInput('combo_name', 
                Yii::$app->request->get('combo_name', ''), 
                [
                    'class' => 'form-control pr-4',
                    'placeholder' => 'Buscar combo...',
                    'onkeypress' => 'if(event.keyCode == 13) { filterComboSearch(this.value); return false; }',
                    'onblur' => 'filterComboSearch(this.value)',
                    'id' => 'combo-search-input'
                ]
            ) . 
            '<button type="button" class="btn btn-link position-absolute clear-search-btn" onclick="clearComboSearch()" style="right: 5px; top: 50%; transform: translateY(-50%); padding: 2px 4px; border: none; background: none; color: #6c757d; font-size: 16px; line-height: 1; display: ' . (Yii::$app->request->get('combo_name', '') ? 'block' : 'none') . '; z-index: 10;">&times;</button>' .
            '</div>'
    ],    [
        'attribute' => 'total_cost',
        'label' => "Costo",
        'value' => function ($data) {
            return formatCost($data->total_cost);
        },
        'filter' => false
    ],
    [
        'attribute' => 'cost_precent',
        'label' => ' Porcentaje de Costo',
        'value' => function ($data) {
            return formatPercentage($data->cost_precent*100);
        },
        'filter' => false
    ],
    [
        'label' => Yii::t('app', "Sales"),
        'format' => 'raw',
        'filter' => false,
        'value' => function ($data) use ($selectedMonth, $selectedYear) {
            $inputName = 'combo[' . $data->id . ']';
            // Convert sales to integer to remove decimal places
            $integerValue = intval($data->sales);
            return Html::input('number', $inputName, $integerValue, [
                'class' => 'form-control sales-input',
                'data-id' => $data->id,
                'data-type' => 'menu',
                'min' => '0',
                'step' => '1',
                'pattern' => '[0-9]*'
            ]);
        },
    ]
];

// Generar años para el selector (5 años atrás y 5 adelante)
$years = [];
$currentYear = date('Y');
for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
    $years[$i] = $i;
}

?>
<?php \yii\widgets\Pjax::begin(['id' => 'pjax-sales']) ?>

<div class="card mb-4">
    <div class="card-header filter-header" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="true" aria-controls="filterCollapse" role="button">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0"><?= Yii::t('app', 'Filtros e Importación') ?></h3>
            <i class="bx bx-chevron-up filter-icon"></i>
        </div>
    </div>
    <div class="collapse show" id="filterCollapse">
        <div class="card-body">
            <!-- Filtros de mes y año -->
            <?= Html::beginForm(['sales'], 'get', ['data-pjax' => 1]) ?>
            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <label for="month-select" class="form-label">Mes</label>
                    <?= Html::dropDownList('month',
                        $selectedMonth ?? date('n'), 
                        array_merge(['0' => 'TODOS'], [
                            '1' => 'Enero',
                            '2' => 'Febrero',
                            '3' => 'Marzo',
                            '4' => 'Abril',
                            '5' => 'Mayo',
                            '6' => 'Junio',
                            '7' => 'Julio',
                            '8' => 'Agosto',
                            '9' => 'Septiembre',
                            '10' => 'Octubre',
                            '11' => 'Noviembre',
                            '12' => 'Diciembre',
                        ]),
                        ['class' => 'form-select', 'id' => 'month-select']
                    ) ?>
                </div>
                
                <div class="col-md-4">
                    <label for="year-select" class="form-label">Año</label>
                    <?= Html::dropDownList('year',
                        $selectedYear ?? date('Y'),
                        ['0' => 'TODOS'] + $years,
                        ['class' => 'form-select', 'id' => 'year-select']
                    ) ?>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <?= Html::submitButton('Filtrar', ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
            <?= Html::endForm() ?>
            
            <!-- Separador visual -->
            <hr class="my-4">
            
            <!-- Sección de importar ventas -->
            <div class="row">
                <div class="col-12">
                    <h6 class="mb-3 text-muted">
                        <i class="bx bx-upload me-2"></i>Importar ventas desde Excel
                    </h6>
                    
                    <div class="row g-3 align-items-end">
                        <!-- Botón para descargar plantilla -->
                        <div class="col-md-3">
                            <?= Html::a('Descargar Plantilla', ['download-sales-template'], [
                                'class' => 'btn btn-outline-primary',
                                'title' => 'Descargar plantilla Excel para importar ventas',
                                'data-pjax' => '0'
                            ]) ?>
                        </div>
                        
                        <!-- Formulario de importación -->
                        <?= Html::beginForm(['import-sales-excel'], 'post', [
                            'enctype' => 'multipart/form-data',
                            'id' => 'import-form',
                            'class' => 'col-md-9'
                        ]) ?>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-8">
                                <label for="sales-file" class="form-label">Archivo Excel</label>
                                <?= Html::fileInput('sales_file', '', [
                                    'class' => 'form-control',
                                    'id' => 'sales-file',
                                    'accept' => '.xlsx,.xls'
                                ]) ?>
                            </div>
                            <div class="col-md-4">
                                <?= Html::submitButton('Importar', [
                                    'class' => 'btn btn-success',
                                    'id' => 'btn-import-excel'
                                ]) ?>
                            </div>
                        </div>
                        <?= Html::endForm() ?>
                    </div>
                    
                    <div class="form-text mt-2">
                        Descargue la plantilla, complete los datos (MES, AÑO, DESCRIPCIÓN, VENTAS) y súbala para importar.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para resultados de importación -->
<div class="modal fade" id="importResultModal" tabindex="-1" aria-labelledby="importResultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importResultModalLabel">Resultado de la Importación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="import-result-content">
                    <!-- El contenido será cargado dinámicamente -->
                </div>
            </div>
            <div class="modal-footer">
                <!-- <button type="button" class="btn btn-success" id="reload-page-btn" style="display: none;">
                    <i class="fas fa-sync-alt"></i> Recargar página
                </button> -->
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?= Html::beginForm(['save-monthly-sales'], 'post', ['id' => 'sales-form']) ?>
    <?= Html::hiddenInput('month', $selectedMonth, ['id' => 'month-hidden']) ?>
    <?= Html::hiddenInput('year', $selectedYear, ['id' => 'year-hidden']) ?>

<div class="card mb-4" id="food-section">
    <div class="card-header">
        <h3 class="card-title">
            <?php
            $showAllSales = ($selectedMonth == 0 || $selectedYear == 0);
            if ($showAllSales) {
                echo Yii::t('app', 'Todas las ventas de alimentos');
            } else {
                echo Yii::t('app', 'Food sales') . ' - ' . getMonthName($selectedMonth) . ' ' . $selectedYear;
            }
            ?>
        </h3>
        <small class="text-muted"><?= $foodDataProvider->getTotalCount() ?> recetas encontradas</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <?= \yii\grid\GridView::widget([
                'dataProvider' => $foodDataProvider,
                'filterModel' => $foodSearchModel,
                'columns' => $foodGridColumns,
                'filterUrl' => Url::current([
                    'month' => $selectedMonth,
                    'year' => $selectedYear
                ], true),
                'options' => ['class' => 'grid-view'],
                'tableOptions' => ['class' => 'table table-striped responsive-table'],
                'layout' => "{summary}\n{pager}\n{items}\n{pager}"
            ]) ?>
        </div>
    </div>
</div>

<div class="card mb-4" id="drink-section">
    <div class="card-header">
        <h3 class="card-title">
            <?php
            $showAllSales = ($selectedMonth == 0 || $selectedYear == 0);
            if ($showAllSales) {
                echo Yii::t('app', 'Todas las ventas de bebidas');
            } else {
                echo Yii::t('app', 'Drinking sales') . ' - ' . getMonthName($selectedMonth) . ' ' . $selectedYear;
            }
            ?>
        </h3>
        <small class="text-muted"><?= $drinkDataProvider->getTotalCount() ?> recetas encontradas</small>
    </div>
    <div class="card-body ">
        <div class="table-responsive">
            <?= \yii\grid\GridView::widget([
                'dataProvider' => $drinkDataProvider,
                'filterModel' => $drinkSearchModel,
                'columns' => $drinkGridColumns,
                'filterUrl' => Url::current([
                    'month' => $selectedMonth,
                    'year' => $selectedYear
                ], true),
                'options' => ['class' => 'grid-view'],
                'tableOptions' => ['class' => 'table table-striped mb-0 responsive-table'],
                'layout' => "{summary}\n{pager}\n{items}\n{pager}"
            ]) ?>
        </div>
    </div>
</div>

<div class="card mb-4" id="combo-section">
    <div class="card-header">
        <h3 class="card-title">
            <?php
            $showAllSales = ($selectedMonth == 0 || $selectedYear == 0);
            if ($showAllSales) {
                echo Yii::t('app', 'Todas las ventas de combos');
            } else {
                echo Yii::t('app', 'Venta de Combos') . ' - ' . getMonthName($selectedMonth) . ' ' . $selectedYear;
            }
            ?>
        </h3>
        <small class="text-muted"><?= $comboDataProvider->getTotalCount() ?> combos encontrados</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <?= \yii\grid\GridView::widget([
                'dataProvider' => $comboDataProvider,
                'filterModel' => $comboSearchModel,
                'columns' => $comboGridColumns,
                'filterUrl' => Url::current([
                    'month' => $selectedMonth,
                    'year' => $selectedYear
                ], true),
                'options' => ['class' => 'grid-view'],
                'tableOptions' => ['class' => 'table table-striped responsive-table'],
                'layout' => "{summary}\n{pager}\n{items}\n{pager}"
            ]) ?>
        </div>
    </div>
</div>

<div class="d-flex justify-content-end mb-4">
    <!-- Campos ocultos movidos al interior del formulario principal -->
</div>

<!-- Barra flotante con botón de guardar siempre visible -->
<?php if ((int)$selectedMonth != 0 && (int)$selectedYear != 0): ?>
<div class="sticky-save-bar">
    <?= Html::button('Guardar ventas de ' . getMonthName($selectedMonth) . ' ' . $selectedYear, [
        'class' => 'btn btn-primary btn-lg',
        'id' => 'btn-save-sales'
    ]) ?>
</div>
<?php endif; ?>

<?= Html::endForm() ?>

<?php \yii\widgets\Pjax::end(); ?>

<?php
$saveUrl = Url::to(['save-monthly-sales']);
$js = <<< JS
// Variable para rastrear cambios sin guardar
let hasUnsavedChanges = false;

// Función para marcar que hay cambios sin guardar
function markAsUnsaved() {
    hasUnsavedChanges = true;
}

// Función para marcar que los cambios han sido guardados
function markAsSaved() {
    hasUnsavedChanges = false;
    navigationBlocked = false; // Permitir navegación normal
}

// Función para detectar cambios en los inputs de ventas
function setupChangeDetection() {
    // Forzar valores enteros en todos los inputs de ventas al cargar la página
    $('.sales-input').each(function() {
        let value = parseInt($(this).val()) || 0;
        if (value < 0) value = 0;
        $(this).val(value); // Asegura que no se muestren decimales
    });
    
    $('.sales-input').off('input.unsaved').on('input.unsaved', function() {
        // Asegurar que siempre sea un valor entero
        let value = parseInt($(this).val()) || 0;
        if (value < 0) value = 0;
        $(this).val(value);
        
        markAsUnsaved();
        setupNavigationBlock(); // Activar bloqueo de navegación cuando hay cambios
    });
    
    // También verificar cuando el campo pierde el foco
    $('.sales-input').off('blur.integer').on('blur.integer', function() {
        // Asegurar que siempre sea un valor entero
        let value = parseInt($(this).val()) || 0;
        if (value < 0) value = 0;
        $(this).val(value);
    });
}

// Función para mostrar alerta personalizada antes de salir
function showUnsavedChangesAlert(callback) {
    if (hasUnsavedChanges) {
        Swal.fire({
            title: '¿Salir sin guardar?',
            text: 'Tienes cambios sin guardar. ¿Qué deseas hacer?',
            icon: 'warning',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Guardar y salir',
            denyButtonText: 'Salir sin guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
            denyButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Guardar antes de salir
                saveAndExit(callback);
            } else if (result.isDenied) {
                // Salir sin guardar
                markAsSaved(); // Evitar la alerta en el beforeunload
                if (callback) callback();
            }
            // Si es cancel, no hacer nada (permanecer en la página)
        });
        return false; // Prevenir la acción original
    }
    return true; // Permitir la acción si no hay cambios
}

// Función para guardar y salir
function saveAndExit(callback) {
    // Actualizar campos ocultos antes de enviar
    updateHiddenFields();
    
    let formData = $('#sales-form').serialize();
    
    $.ajax({
        url: '$saveUrl',
        type: 'POST',
        data: formData,
        dataType: 'json',
        beforeSend: function() {
            Swal.fire({
                title: 'Guardando...',
                text: 'Por favor espera mientras se guardan los cambios.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        },
        success: function(response) {
            if (response.success) {
                markAsSaved(); // Marcar como guardado
                Swal.fire({
                    title: 'Guardado exitoso',
                    text: 'Los cambios se han guardado correctamente.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    if (callback) callback();
                });
            } else {
                let errorMsg = 'Ocurrió un problema al guardar las ventas';
                if (response.errors && response.errors.length > 0) {
                    errorMsg += ':\\n\\n' + response.errors.join('\\n');
                }
                
                Swal.fire({
                    title: 'Error al guardar',
                    text: errorMsg,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                title: 'Error',
                text: 'Ocurrió un problema al guardar: ' + error,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
}

// Interceptar navegación con beforeunload
window.addEventListener('beforeunload', function(e) {
    if (hasUnsavedChanges) {
        // Mensaje estándar del navegador
        const message = 'Tienes cambios sin guardar. ¿Estás seguro de que quieres salir?';
        e.preventDefault();
        e.returnValue = message;
        return message;
    }
});

// Interceptar navegación del navegador (botón atrás/adelante)
window.addEventListener('popstate', function(e) {
    if (hasUnsavedChanges) {
        e.preventDefault();
        // Empujar el estado actual de vuelta para cancelar la navegación
        history.pushState(null, null, window.location.pathname + window.location.search);
        
        showUnsavedChangesAlert(() => {
            // Si el usuario decide salir, navegamos manualmente
            markAsSaved();
            history.back();
        });
    }
});

// Prevenir navegación del navegador y mostrar modal personalizado
let navigationBlocked = false;
function setupNavigationBlock() {
    // Empujar un estado inicial para detectar navegación
    if (!navigationBlocked) {
        history.pushState(null, null, window.location.pathname + window.location.search);
        navigationBlocked = true;
    }
}

// Interceptar teclas de navegación (F5, Ctrl+R, etc.)
document.addEventListener('keydown', function(e) {
    if (hasUnsavedChanges) {
        // F5 o Ctrl+R (refresh)
        if (e.key === 'F5' || (e.ctrlKey && e.key === 'r')) {
            e.preventDefault();
            showUnsavedChangesAlert(() => {
                markAsSaved();
                location.reload();
            });
            return false;
        }
        
        // Ctrl+W (cerrar pestaña)
        if (e.ctrlKey && e.key === 'w') {
            e.preventDefault();
            showUnsavedChangesAlert(() => {
                markAsSaved();
                window.close();
            });
            return false;
        }
        
        // Alt + Flecha izquierda (atrás)
        if (e.altKey && e.key === 'ArrowLeft') {
            e.preventDefault();
            showUnsavedChangesAlert(() => {
                markAsSaved();
                history.back();
            });
            return false;
        }
        
        // Alt + Flecha derecha (adelante)
        if (e.altKey && e.key === 'ArrowRight') {
            e.preventDefault();
            showUnsavedChangesAlert(() => {
                markAsSaved();
                history.forward();
            });
            return false;
        }
    }
});

// Interceptar clics en enlaces y botones de navegación
$(document).on('click', 'a:not([href^="#"]):not([data-method]):not([data-pjax="0"]):not(.btn-save-related)', function(e) {
    if (hasUnsavedChanges) {
        e.preventDefault();
        const href = $(this).attr('href');
        showUnsavedChangesAlert(() => {
            window.location.href = href;
        });
    }
});

// Interceptar envío de formularios que no sean el de ventas
$(document).on('submit', 'form:not(#sales-form):not(#import-form)', function(e) {
    if (hasUnsavedChanges) {
        e.preventDefault();
        const form = this;
        showUnsavedChangesAlert(() => {
            markAsSaved();
            form.submit();
        });
    }
});

// Función para actualizar campos ocultos
function updateHiddenFields() {
    const month = $('#month-select').val();
    const year = $('#year-select').val();
    const monthName = $('#month-select option:selected').text();
    
    // Actualizar campos ocultos
    $('#month-hidden').val(month);
    $('#year-hidden').val(year);
    
    // Actualizar texto del botón de guardar
    $('#btn-save-sales').text('Guardar ventas');
}

// Función para configurar event listeners
function setupEventListeners() {
    // Al cambiar los selectores, actualizamos los campos ocultos
    $('#month-select, #year-select').off('change.sales').on('change.sales', function() {
        updateHiddenFields();
    });
    
    // Acción del botón guardar ventas
    $('#btn-save-sales').off('click.sales').on('click.sales', function(e) {
        e.preventDefault();
        
        // Actualizar campos ocultos antes de enviar
        updateHiddenFields();
        
        let formData = $('#sales-form').serialize();
        
        $.ajax({
            url: '$saveUrl',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#btn-save-sales').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');
            },
            success: function(response) {
                if (response.success) {
                    markAsSaved(); // Marcar como guardado
                    Swal.fire({
                        title: 'Éxito',
                        text: 'Las ventas se han guardado correctamente.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                } else {
                    // Mostrar errores
                    let errorMsg = 'Ocurrió un problema al guardar las ventas:';
                    if (response.errors && response.errors.length > 0) {
                        errorMsg += '<ul>';
                        response.errors.forEach(function(error) {
                            errorMsg += '<li>' + error + '</li>';
                        });
                        errorMsg += '</ul>';
                    }
                    
                    Swal.fire({
                        title: 'Error',
                        html: errorMsg,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    title: 'Error',
                    text: 'Ocurrió un problema al guardar las ventas: ' + error,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            },
            complete: function() {
                const monthName = $('#month-select option:selected').text();
                const year = $('#year-select').val();
                $('#btn-save-sales').prop('disabled', false).text('Guardar ventas de ' + monthName + ' ' + year);
            }
        });
    });
}

// Configurar event listeners al cargar la página
$(document).ready(function() {
    setupEventListeners();
    updateHiddenFields(); // Sincronizar valores iniciales
    setupSearchFilters(); // Configurar filtros de búsqueda
    scrollToActiveFilter(); // Desplazarse a la sección con filtro activo
    
    // Asegurarse de que se inicialicen correctamente todos los campos numéricos
    setupChangeDetection(); // Configurar detección de cambios
    
    // Configurar el formulario de importación de Excel
    $('#import-form').on('submit', function(e) {
        e.preventDefault(); // Prevenir envío normal del formulario
        
        const fileInput = $('#sales-file');
        if (fileInput.val() === '') {
            Swal.fire({
                title: 'Error',
                text: 'Debe seleccionar un archivo Excel para importar.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return false;
        }
        
        // Mostrar spinner de carga
        $('#btn-import-excel').prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Importando...'
        );
        
        // Enviar formulario por AJAX
        const formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Mostrar el resultado en el modal
                $('#import-result-content').html(response);
                $('#importResultModal').modal('show');
                
                // Mostrar botón de recarga si la importación fue exitosa
                if (response.includes('alert-success') || response.includes('Importación completada')) {
                    $('#reload-page-btn').show();
                } else {
                    $('#reload-page-btn').hide();
                }
                
                // Limpiar el formulario
                $('#sales-file').val('');
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Error al procesar el archivo: ' + error;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                const errorHtml = '<div class="alert alert-danger">' +
                    '<h6><i class="fas fa-exclamation-triangle"></i> Error</h6>' +
                    '<p>' + errorMessage + '</p>' +
                    '</div>';
                
                $('#import-result-content').html(errorHtml);
                $('#importResultModal').modal('show');
            },
            complete: function() {
                // Restaurar botón
                $('#btn-import-excel').prop('disabled', false).text('Importar');
            }
        });
        
        return false;
    });
    
    // Configurar botón de recarga de página
    $('#reload-page-btn').on('click', function() {
        location.reload();
    });
    
    // Ocultar botón de recarga cuando se cierre el modal
    $('#importResultModal').on('hidden.bs.modal', function() {
        $('#reload-page-btn').hide();
    });
});

// Reconfigurar event listeners después de actualizaciones PJAX
$(document).on('pjax:complete', function() {
    setupEventListeners();
    updateHiddenFields(); // Sincronizar valores después de PJAX
    setupSearchFilters(); // Reconfigurar filtros después de PJAX
    scrollToActiveFilter(); // Desplazarse a la sección con filtro activo después de PJAX
    
    // Forzar valores enteros en todos los inputs justo después de completar PJAX
    setTimeout(function() {
        setupChangeDetection(); // Reconfigurar detección de cambios después de PJAX
    }, 100); // Pequeño retraso para asegurar que los elementos del DOM estén listos
});

// Función para desplazarse a la sección con filtro activo
function scrollToActiveFilter() {
    const urlParams = new URLSearchParams(window.location.search);
    let targetSection = null;
    
    if (urlParams.has('food_title') && urlParams.get('food_title').trim() !== '') {
        targetSection = 'food-section';
    } else if (urlParams.has('drink_title') && urlParams.get('drink_title').trim() !== '') {
        targetSection = 'drink-section';
    } else if (urlParams.has('combo_name') && urlParams.get('combo_name').trim() !== '') {
        targetSection = 'combo-section';
    }
    
    if (targetSection) {
        setTimeout(function() {
            const element = document.getElementById(targetSection);
            if (element) {
                element.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start',
                    inline: 'nearest'
                });
            }
        }, 500); // Pequeño delay para asegurar que el contenido esté cargado
    }
}

// Funciones para manejar los filtros de búsqueda
function setupSearchFilters() {
    // Configurar input de búsqueda de alimentos
    const foodInput = $('#food-search-input');
    const foodClearBtn = foodInput.siblings('.clear-search-btn');
    
    if (foodInput.val().trim() !== '') {
        foodClearBtn.show();
    }
    
    foodInput.on('input', function() {
        const clearBtn = $(this).siblings('.clear-search-btn');
        if ($(this).val().trim() !== '') {
            clearBtn.show();
        } else {
            clearBtn.hide();
        }
    });
    
    // Configurar input de búsqueda de bebidas
    const drinkInput = $('#drink-search-input');
    const drinkClearBtn = drinkInput.siblings('.clear-search-btn');
    
    if (drinkInput.val().trim() !== '') {
        drinkClearBtn.show();
    }
    
    drinkInput.on('input', function() {
        const clearBtn = $(this).siblings('.clear-search-btn');
        if ($(this).val().trim() !== '') {
            clearBtn.show();
        } else {
            clearBtn.hide();
        }
    });
    
    // Configurar input de búsqueda de combos
    const comboInput = $('#combo-search-input');
    const comboClearBtn = comboInput.siblings('.clear-search-btn');
    
    if (comboInput.val().trim() !== '') {
        comboClearBtn.show();
    }
    
    comboInput.on('input', function() {
        const clearBtn = $(this).siblings('.clear-search-btn');
        if ($(this).val().trim() !== '') {
            clearBtn.show();
        } else {
            clearBtn.hide();
        }
    });
}
JS;
$this->registerJs($js);

// JavaScript para controlar el colapsable unificado
$collapseJs = <<<JS
$(document).ready(function() {
    // Función para actualizar ícono basado en estado de colapso
    function updateCollapseIcon(collapseEl, iconSelector) {
        const isExpanded = $(collapseEl).hasClass('show');
        $(iconSelector).removeClass('bx-chevron-up bx-chevron-down')
                       .addClass(isExpanded ? 'bx-chevron-up' : 'bx-chevron-down');
    }

    // Configurar eventos para el colapsable unificado
    $('#filterCollapse').on('show.bs.collapse', function() {
        updateCollapseIcon(this, '.filter-icon');
        localStorage.setItem('salesFilterExpanded', 'true');
    });

    $('#filterCollapse').on('hide.bs.collapse', function() {
        updateCollapseIcon(this, '.filter-icon');
        localStorage.setItem('salesFilterExpanded', 'false');
    });

    // Restaurar estado guardado al cargar la página
    const filterExpanded = localStorage.getItem('salesFilterExpanded');

    // Restaurar estado de filtros (por defecto expandido)
    if (filterExpanded === 'false') {
        $('#filterCollapse').removeClass('show');
        updateCollapseIcon('#filterCollapse', '.filter-icon');
    }
});

// Reconfigurar colapsables después de PJAX
$(document).on('pjax:complete', function() {
    // Restaurar estado después de PJAX
    const filterExpanded = localStorage.getItem('salesFilterExpanded');

    if (filterExpanded === 'false') {
        $('#filterCollapse').removeClass('show');
        $('.filter-icon').removeClass('bx-chevron-up').addClass('bx-chevron-down');
    } else {
        $('#filterCollapse').addClass('show');
        $('.filter-icon').removeClass('bx-chevron-down').addClass('bx-chevron-up');
    }
});
JS;
$this->registerJs($collapseJs, \yii\web\View::POS_END);

// Funciones globales para limpiar filtros
$clearFunctionsJs = <<<JS
function filterFoodSearch(value) {
    const currentUrl = new URL(window.location);
    if (value && value.trim() !== '') {
        currentUrl.searchParams.set('food_title', value.trim());
        currentUrl.hash = 'food-section';
    } else {
        currentUrl.searchParams.delete('food_title');
        currentUrl.hash = '';
    }
    window.location.href = currentUrl.toString();
}

function filterDrinkSearch(value) {
    const currentUrl = new URL(window.location);
    if (value && value.trim() !== '') {
        currentUrl.searchParams.set('drink_title', value.trim());
        currentUrl.hash = 'drink-section';
    } else {
        currentUrl.searchParams.delete('drink_title');
        currentUrl.hash = '';
    }
    window.location.href = currentUrl.toString();
}

function filterComboSearch(value) {
    const currentUrl = new URL(window.location);
    if (value && value.trim() !== '') {
        currentUrl.searchParams.set('combo_name', value.trim());
        currentUrl.hash = 'combo-section';
    } else {
        currentUrl.searchParams.delete('combo_name');
        currentUrl.hash = '';
    }
    window.location.href = currentUrl.toString();
}

function clearFoodSearch() {
    // Redirigir a la URL sin el parámetro de filtro
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.delete('food_title');
    currentUrl.hash = 'food-section';
    window.location.href = currentUrl.toString();
}

function clearDrinkSearch() {
    // Redirigir a la URL sin el parámetro de filtro
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.delete('drink_title');
    currentUrl.hash = 'drink-section';
    window.location.href = currentUrl.toString();
}

function clearComboSearch() {
    // Redirigir a la URL sin el parámetro de filtro
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.delete('combo_name');
    currentUrl.hash = 'combo-section';
    window.location.href = currentUrl.toString();
}
JS;
$this->registerJs($clearFunctionsJs, \yii\web\View::POS_HEAD);

// CSS para la barra flotante de guardar, colapsables y tablas responsivas
$css = <<<CSS
.sticky-save-bar {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1050;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    border: 1px solid #e0e0e0;
}

.sticky-save-bar .btn {
    box-shadow: 0 2px 10px rgba(0, 123, 255, 0.3);
}

/* Estilos para elementos colapsables */
.filter-header {
    cursor: pointer;
    user-select: none;
    transition: background-color 0.2s ease;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.filter-header:hover {
    background-color: rgba(0, 0, 0, 0.03);
}

.filter-icon {
    transition: transform 0.3s ease;
    font-size: 1.25rem;
    color: #6c757d;
}

/* Animación para el colapso */
.collapse, .collapsing {
    transition: all 0.35s ease;
}

/* Estilo para hacer más visible que es clickeable */
[data-bs-toggle="collapse"] {
    position: relative;
}

[data-bs-toggle="collapse"]::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
}

/* Estilos para tablas responsivas */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border-radius: 0;
}

.responsive-table {
    table-layout: auto;
    width: 100%;
    min-width: 600px; /* Ancho mínimo para evitar compresión excesiva */
}

.responsive-table th,
.responsive-table td {
    white-space: nowrap;
    vertical-align: middle;
    padding: 0.75rem 0.5rem;
}

/* Ajustes específicos para inputs de ventas en tablas */
.responsive-table .sales-input {
    min-width: 80px;
    max-width: 120px;
    font-size: 0.875rem;
}

/* Ajustes para columnas específicas */
.responsive-table th:first-child,
.responsive-table td:first-child {
    min-width: 50px; /* Columna de número */
}

.responsive-table th:nth-child(2),
.responsive-table td:nth-child(2) {
    min-width: 200px; /* Columna de título */
    max-width: 300px;
    white-space: normal;
    word-wrap: break-word;
}

.responsive-table th:nth-child(3),
.responsive-table td:nth-child(3),
.responsive-table th:nth-child(4),
.responsive-table td:nth-child(4) {
    min-width: 100px; /* Columnas de costo y porcentaje */
}

.responsive-table th:last-child,
.responsive-table td:last-child {
    min-width: 120px; /* Columna de ventas */
}

/* Optimización para dispositivos móviles */
@media (max-width: 768px) {
    .sticky-save-bar {
        left: 20px;
        right: 20px;
        text-align: center;
    }
    
    .filter-header {
        font-size: 0.9rem;
    }
    
    .responsive-table {
        min-width: 500px;
        font-size: 0.8rem;
    }
    
    .responsive-table th,
    .responsive-table td {
        padding: 0.5rem 0.25rem;
    }
    
    .responsive-table .sales-input {
        min-width: 70px;
        max-width: 100px;
        font-size: 0.8rem;
    }
    
    .card-body.p-0 {
        padding: 0 !important;
    }
    
    .table-responsive {
        margin: 0;
        border-radius: 0;
    }
}

/* Estilos para los botones de limpiar filtros */
.clear-search-btn {
    border: none !important;
    background: none !important;
    color: #6c757d !important;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
    transition: color 0.2s ease;
    width: 20px;
    height: 20px;
}

.clear-search-btn:hover {
    color: #dc3545 !important;
    background: none !important;
}

.clear-search-btn:focus {
    outline: none;
    box-shadow: none !important;
}

.position-relative .form-control {
    padding-right: 30px !important;
}

/* Estilos para el modal de resultados de importación */
#importResultModal .modal-dialog {
    max-width: 700px;
}

#importResultModal .modal-body {
    padding: 1.5rem;
}

#importResultModal .alert {
    border: none;
    border-left: 4px solid;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

#importResultModal .alert-success {
    border-left-color: #28a745;
    background-color: #d4f6d4;
    color: #155724;
}

#importResultModal .alert-warning {
    border-left-color: #ffc107;
    background-color: #fff3cd;
    color: #856404;
}

#importResultModal .alert-info {
    border-left-color: #17a2b8;
    background-color: #d1ecf1;
    color: #0c5460;
}

#importResultModal .alert-danger {
    border-left-color: #dc3545;
    background-color: #f8d7da;
    color: #721c24;
}

#importResultModal .alert h6 {
    margin-bottom: 0.5rem;
    font-weight: 600;
}

#importResultModal .alert-content {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 0.875rem;
    line-height: 1.4;
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.75rem;
    background-color: #ffffff;
}

#importResultModal .alert-content::-webkit-scrollbar {
    width: 8px;
}

#importResultModal .alert-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

#importResultModal .alert-content::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

#importResultModal .alert-content::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

#importResultModal .alert-content .small {
    color: #6c757d;
    margin-bottom: 0.25rem;
    padding: 0.25rem 0;
    border-bottom: 1px solid #f8f9fa;
}

#importResultModal .alert-content .small:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

/* Mejoras visuales para el separador */
hr.my-4 {
    border-color: #dee2e6;
    opacity: 0.7;
}

/* Estilo para el ícono de importar */
.bx-upload {
    color: #28a745;
    font-size: 1.1rem;
}
CSS;
$this->registerCss($css);

// Registramos SweetAlert2 si no está incluido
$this->registerJsFile('https://cdn.jsdelivr.net/npm/sweetalert2@11', [
    'position' => \yii\web\View::POS_END,
]);
?>
