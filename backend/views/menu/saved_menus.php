<?php
/** @var $this \yii\web\View */
/** @var $dataProvider \yii\data\ActiveDataProvider */
/** @var $menuBundleProducts array */
/** @var $rentabilidadReal array */

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Menús Guardados');

// Asegurar que Bootstrap Icons esté disponible
$this->registerCssFile("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css");
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4><?= Html::encode($this->title) ?></h4>
        <div class="d-flex gap-2">
        <?= Html::button('<i class="bi bi-graph-up"></i> Ver gráfico', [
            'class' => 'btn btn-success',
            'id' => 'btn-view-chart'
        ]) ?>
        <?= Html::button('<i class="bi bi-bar-chart-fill"></i> Comparar seleccionados', [
            'class' => 'btn btn-primary',
            'id' => 'btn-compare-menus',
            'disabled' => true
        ]) ?>
    </div>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-3" role="alert">
            <i class="bi bi-info-circle"></i> Selecciona <strong>exactamente 2 menús</strong> para comparar.
        </div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'class' => \yii\grid\CheckboxColumn::class,
                    'checkboxOptions' => function ($model, $key, $index, $column) {
                        return [
                            'class' => 'menu-checkbox',
                            'data-menu-id' => $model->id,
                            'data-menu-date' => Yii::$app->formatter->asDate($model->date)
                        ];
                    }
                ],
                'date:date',
                [
                    'label' => 'Rentabilidad Teórica por Categoría',
                    'format' => 'raw',
                    'value' => function ($model) use ($menuBundleProducts) {
                        if (isset($menuBundleProducts[$model->id])) {
                            $html = '<div class="rentabilidad-wrapper">';
                            foreach ($menuBundleProducts[$model->id] as $category => $value) {
                                $formattedValue = floatval($value);
                                $colorClass = $formattedValue <= 25 ? 'text-success' : 
                                             ($formattedValue <= 40 ? 'text-warning' : 'text-danger');
                                
                                $html .= '<div class="rentabilidad-item">';
                                $html .= '<span class="badge bg-secondary me-1">' . Html::encode($category) . '</span> ';
                                $html .= '<span class="' . $colorClass . ' fw-bold">' . 
                                         Html::encode(number_format($formattedValue, 2)) . '%</span>';
                                $html .= '</div>';
                            }
                            
                            $html .= '</div>';
                            return $html;
                        }
                        
                        return '<span class="text-muted">No disponible</span>';
                    }
                ],
                [
                    'label' => 'Rentabilidad Teórica del Menú',
                    'format' => 'raw',
                    'value' => function ($model) use ($menuBundleProducts) {
                        if (isset($menuBundleProducts[$model->id]) && !empty($menuBundleProducts[$model->id])) {
                            // Calcular el promedio de todas las categorías
                            $values = array_values($menuBundleProducts[$model->id]);
                            $average = array_sum(array_map('floatval', $values)) / count($values);
                            
                            // Determinar el color según el valor promedio
                            $colorClass = '';
                            if ($average <= 25) {
                                $colorClass = 'bg-success'; // Verde - Excelente
                            } elseif ($average <= 40) {
                                $colorClass = 'bg-warning'; // Amarillo - Aceptable
                            } else {
                                $colorClass = 'bg-danger'; // Rojo - Preocupante
                            }
                            
                            return '<span class="badge ' . $colorClass . ' fs-6">' . 
                                   number_format($average, 2) . '%</span>';
                        }
                        
                        return '<span class="text-muted">N/A</span>';
                    }
                ],
                [
                    'label' => 'Rentabilidad Real',
                    'format' => 'raw',
                    'value' => function ($model) use ($rentabilidadReal) {
                        if (isset($rentabilidadReal[$model->id]) && !empty($rentabilidadReal[$model->id])) {
                            // Calcular el promedio de todas las categorías
                            $values = array_values($rentabilidadReal[$model->id]);
                            $average = array_sum(array_map('floatval', $values)) / count($values);
                            
                            // Determinar el color según el valor promedio
                            $colorClass = '';
                            if ($average <= 25) {
                                $colorClass = 'bg-success'; // Verde - Excelente
                            } elseif ($average <= 40) {
                                $colorClass = 'bg-warning'; // Amarillo - Aceptable
                            } else {
                                $colorClass = 'bg-danger'; // Rojo - Preocupante
                            }
                            
                            return '<span class="badge ' . $colorClass . ' fs-6">' . 
                                   number_format($average, 2) . '%</span>';
                        }
                        
                        return '<span class="text-muted">N/A</span>';
                    }
                ],
            ],
            'options' => [
                'id' => 'menu-grid'
            ],
            'tableOptions' => ['class' => 'table table-striped table-hover'],
            'summary' => '<div class="summary-info">Mostrando <b>{begin}-{end}</b> de <b>{totalCount}</b> elementos</div>',
        ]) ?>
    </div>
</div>

<!-- Botón flotante para ir arriba -->
<button id="btn-back-to-top" class="btn-back-to-top" title="Volver arriba">
    <i class="bi bi-arrow-up"></i>
</button>

<?php
// Modal para confirmar la comparación
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-confirm-compare',
    'title' => Yii::t('app', "Comparar menús"),
    'size' => \yii\bootstrap5\Modal::SIZE_SMALL,
]);
?>
<p>¿Deseas comparar los 2 menús seleccionados?</p>
<div id="selected-menus-list" class="mb-3 alert alert-info">
    <!-- La lista de menús seleccionados se mostrará aquí dinámicamente -->
</div>
<div class="d-flex justify-content-end gap-3">
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Cancelar'), [
        'class' => 'btn btn-secondary',
        'data-bs-dismiss' => 'modal'
    ]) ?>
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Comparar'), [
        'class' => 'btn btn-primary',
        'id' => 'confirm-compare-button'
    ]) ?>
</div>
<?php
\yii\bootstrap5\Modal::end();
?>

<?php
// Modal grande para mostrar la comparación de menús
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-menu-comparison',
    'title' => Yii::t('app', "Comparación de menús"),
    'size' => \yii\bootstrap5\Modal::SIZE_EXTRA_LARGE,
    'bodyOptions' => ['id' => 'menu-comparison-content', 'style' => 'max-height: 80vh; overflow-y: auto;'],
]);
?>
<div id="comparison-loading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
    <p class="mt-2">Cargando comparación...</p>
</div>
<div id="comparison-content" class="d-none">
    <!-- El contenido de la comparación se cargará aquí -->
</div>
<?php
\yii\bootstrap5\Modal::end();
?>

<?php
$js = <<<JS
$(document).ready(function() {
    // Variable para almacenar los menús seleccionados
    let selectedMenus = [];
    
    // Función para actualizar el estado del botón de comparación
    function updateCompareButton() {
        selectedMenus = [];
        
        // Recopilar información de los menús seleccionados
        $('.menu-checkbox:checked').each(function() {
            selectedMenus.push({
                id: $(this).data('menu-id'),
                date: $(this).data('menu-date')
            });
        });
        
        // Habilitar el botón solo si hay exactamente 2 menús seleccionados
        if (selectedMenus.length === 2) {
            $('#btn-compare-menus').prop('disabled', false);
        } else {
            $('#btn-compare-menus').prop('disabled', true);
        }
    }
    
    // Escuchar cambios en los checkboxes
    $(document).on('change', '.menu-checkbox', function() {
        // Limitar a seleccionar máximo 2 menús
        if ($('.menu-checkbox:checked').length > 2) {
            $(this).prop('checked', false);
            alert('Debes seleccionar exactamente 2 menús para comparar');
        }
        
        updateCompareButton();
    });
    
    // Al hacer clic en el botón de comparar
    $('#btn-compare-menus').on('click', function() {
        // Verificar que tenemos exactamente 2 menús seleccionados
        if (selectedMenus.length !== 2) {
            alert('Debes seleccionar exactamente 2 menús para comparar');
            return;
        }
        
        // Actualizar el modal de confirmación
        $('#selected-menus-count').text(selectedMenus.length);
        
        // Construir la lista de menús seleccionados
        let menuList = '';
        selectedMenus.forEach(function(menu) {
            menuList += '<div><i class="bi bi-calendar-event me-2"></i>' + menu.date + '</div>';
        });
        $('#selected-menus-list').html(menuList);
        
        // Mostrar el modal de confirmación
        $('#modal-confirm-compare').modal('show');
    });
    
    // Al confirmar la comparación
    $('#confirm-compare-button').on('click', function() {
        // Deshabilitar el botón para evitar múltiples clics
        $(this).prop('disabled', true)
               .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
        
        // Cerrar el modal de confirmación
        $('#modal-confirm-compare').modal('hide');
        
        // Mostrar el modal de comparación con el spinner de carga
        $('#modal-menu-comparison').modal('show');
        $('#comparison-loading').removeClass('d-none');
        $('#comparison-content').addClass('d-none');
        
        // Preparar los IDs para la solicitud AJAX
        const menuIds = selectedMenus.map(menu => menu.id);
        
        // Realizar la solicitud AJAX para obtener la comparación
        $.ajax({
            url: '/menu/saved-menus',
            type: 'POST',
            data: { menuIds: menuIds },
            dataType: 'json',
            success: function(response) {
                // Ocultar el spinner de carga
                $('#comparison-loading').addClass('d-none');
                
                if (response.success) {
                    // Mostrar los datos comparativos
                    $('#comparison-content').removeClass('d-none').html(response.html);
                } else {
                    // Mostrar mensaje de error
                    $('#comparison-content').removeClass('d-none')
                        .html('<div class="alert alert-danger">' + (response.message || 'Error al cargar la comparación.') + '</div>');
                }
                
                // Restaurar el botón
                $('#confirm-compare-button').prop('disabled', false)
                                            .html('Comparar');
            },
            error: function() {
                // Ocultar el spinner de carga
                $('#comparison-loading').addClass('d-none');
                
                // Mostrar mensaje de error
                $('#comparison-content').removeClass('d-none')
                    .html('<div class="alert alert-danger">Error al cargar la comparación. Inténtalo de nuevo.</div>');
                
                // Restaurar el botón
                $('#confirm-compare-button').prop('disabled', false)
                                            .html('Comparar');
            }
        });
    });
    
    // Control del botón flotante para volver arriba
    const backToTopBtn = $('#btn-back-to-top');
    
    // Mostrar/ocultar el botón según la posición del scroll
    $(window).on('scroll', function() {
        if ($(window).scrollTop() > 300) {
            backToTopBtn.addClass('show');
        } else {
            backToTopBtn.removeClass('show');
        }
    });
    
    // Funcionalidad del botón - volver arriba con animación suave
    backToTopBtn.on('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'instant'
        });
        return false;
    });
    
    // Verificar posición inicial del scroll
    if ($(window).scrollTop() > 300) {
        backToTopBtn.addClass('show');
    }
});
JS;

$this->registerJs($js);
?>
<?php
// Recopilar datos para el gráfico
$chartData = [
    'labels' => [], // Fechas de los menús
    'teorica' => [], // Rentabilidad teórica promedio por menú
    'real' => [], // Rentabilidad real promedio por menú
    'categorias' => [] // Para el gráfico detallado por categorías
];

// Ordenar menús por fecha (más antiguo primero)
$models = $dataProvider->getModels();
usort($models, function($a, $b) {
    return strtotime($a->date) - strtotime($b->date);
});

// Procesar cada menú para obtener datos del gráfico
foreach ($models as $model) {
    // Verificar que tengamos datos para este menú
    if ((isset($menuBundleProducts[$model->id]) && !empty($menuBundleProducts[$model->id])) ||
        (isset($rentabilidadReal[$model->id]) && !empty($rentabilidadReal[$model->id]))) {
        
        // Añadir etiqueta (fecha formateada)
        $chartData['labels'][] = Yii::$app->formatter->asDate($model->date, 'php:d M Y');
        
        // Calcular promedio de rentabilidad teórica para este menú
        $teoricaAvg = 0;
        if (isset($menuBundleProducts[$model->id]) && !empty($menuBundleProducts[$model->id])) {
            $values = array_values($menuBundleProducts[$model->id]);
            $teoricaAvg = array_sum(array_map('floatval', $values)) / count($values);
        }
        $chartData['teorica'][] = round($teoricaAvg, 2);
        
        // Calcular promedio de rentabilidad real para este menú
        $realAvg = 0;
        if (isset($rentabilidadReal[$model->id]) && !empty($rentabilidadReal[$model->id])) {
            $values = array_values($rentabilidadReal[$model->id]);
            $realAvg = array_sum(array_map('floatval', $values)) / count($values);
        }
        $chartData['real'][] = round($realAvg, 2);
        
        // Preparar datos por categoría para este menú
        $categoriaData = [];
        
        // Procesar rentabilidad teórica por categoría
        if (isset($menuBundleProducts[$model->id])) {
            foreach ($menuBundleProducts[$model->id] as $category => $value) {
                if (!isset($categoriaData[$category])) {
                    $categoriaData[$category] = [
                        'teorica' => 0,
                        'real' => 0
                    ];
                }
                $categoriaData[$category]['teorica'] = floatval($value);
            }
        }
        
        // Procesar rentabilidad real por categoría
        if (isset($rentabilidadReal[$model->id])) {
            foreach ($rentabilidadReal[$model->id] as $category => $value) {
                if (!isset($categoriaData[$category])) {
                    $categoriaData[$category] = [
                        'teorica' => 0,
                        'real' => 0
                    ];
                }
                $categoriaData[$category]['real'] = floatval($value);
            }
        }
        
        $chartData['categorias'][] = $categoriaData;
    }
}

// Si hay datos para mostrar, crear el gráfico
if (!empty($chartData['labels'])) {
?>

<div class="card mt-4" id="chart-section">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Evolución de Rentabilidad</h4>
    </div>
    <div class="card-body">
        <div id="general-chart-container">
            <canvas id="rentabilidadChart" style="width:100%; height:300px;"></canvas>
        </div>
        <div id="category-chart-container" style="display:none;">
            <div class="mb-3">
                <label for="categorySelector" class="form-label">Seleccionar Categoría:</label>
                <select id="categorySelector" class="form-select">
                    <?php 
                    // Obtener todas las categorías únicas
                    $allCategories = [];
                    foreach ($chartData['categorias'] as $menuCategories) {
                        foreach (array_keys($menuCategories) as $category) {
                            $allCategories[$category] = true;
                        }
                    }
                    
                    // Generar opciones para el selector
                    foreach (array_keys($allCategories) as $category) {
                        echo "<option value=\"" . Html::encode($category) . "\">" . Html::encode($category) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <canvas id="categoryChart" style="width:100%; height:300px;"></canvas>
        </div>
    </div>
</div>

<?php
// Registrar JavaScript para los gráficos
$chartLabels = json_encode($chartData['labels']);
$chartTeorica = json_encode($chartData['teorica']);
$chartReal = json_encode($chartData['real']);
$chartCategorias = json_encode($chartData['categorias']);

// Sustituir variables en el JavaScript
$js = <<<JS
// Función para determinar color según el valor
function getColorForValue(value) {
    if (value <= 25) return 'rgba(40, 167, 69, 0.8)'; // verde
    if (value <= 40) return 'rgba(255, 193, 7, 0.8)'; // amarillo
    return 'rgba(220, 53, 69, 0.8)'; // rojo
}

// Configuración común para los gráficos
const commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
        tooltip: {
            mode: 'index',
            intersect: false,
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.parsed.y !== null) {
                        label += context.parsed.y.toFixed(2) + '%';
                    }
                    return label;
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: false,
            min: 0,
            max: Math.max(70, Math.ceil(Math.max(...$chartTeorica, ...$chartReal)/10)*10),
            title: {
                display: true,
                text: 'Rentabilidad (%)'
            },
            ticks: {
                callback: function(value) {
                    return value + '%';
                }
            }
        },
        x: {
            title: {
                display: true,
                text: 'Fecha del Menú'
            }
        }
    }
};

// Inicializar gráfico general
const ctxGeneral = document.getElementById('rentabilidadChart').getContext('2d');
const generalChart = new Chart(ctxGeneral, {
    type: 'line',
    data: {
        labels: $chartLabels,
        datasets: [
            {
                label: 'Rentabilidad Teórica',
                data: $chartTeorica,
                borderColor: 'rgba(0, 123, 255, 1)',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: $chartTeorica.map(value => getColorForValue(value)),
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 5
            },
            {
                label: 'Rentabilidad Real',
                data: $chartReal,
                borderColor: 'rgba(220, 53, 69, 1)',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: $chartReal.map(value => getColorForValue(value)),
                pointBorderColor: 'white',
                pointBorderWidth: 2,
                pointRadius: 5
            }
        ]
    },
    options: {
        ...commonOptions,
        plugins: {
            ...commonOptions.plugins,
            title: {
                display: true,
                text: 'Evolución de Rentabilidad General',
                font: {
                    size: 16,
                    weight: 'bold'
                },
                padding: {
                    top: 10,
                    bottom: 20
                }
            }
        }
    }
});
// Manejar clic en el botón "Ver gráfico"
$('#btn-view-chart').on('click', function() {
    const chartSection = document.getElementById('chart-section');
    if (chartSection) {
        // Desplazamiento suave hacia la sección del gráfico
        chartSection.scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
        
        // Agregar y luego quitar una clase de resaltado
        $(chartSection).addClass('highlight-section');
        $(chartSection).removeClass('highlight-section');
        
    } else {
        // Si no hay gráfico, mostrar un mensaje
        alert('No hay datos de gráfico disponibles en este momento.');
    }
});
JS;

$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js');
$this->registerJs($js);
}
?>

<?php
$css = <<<CSS
    .rentabilidad-wrapper {
        max-width: 250px;
    }
    .rentabilidad-item {
        display: flex;
        align-items: center;
    }
    .summary-info {
        padding: 10px 0;
    }
    .form-select {
        max-width: 300px;
    }
    
    /* Estilos para el botón flotante */
    .btn-back-to-top {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #28a745;
        color: white;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        cursor: pointer;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 1000;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 20px;
    }
    
    .btn-back-to-top.show {
        opacity: 1;
        visibility: visible;
    }
    
    .btn-back-to-top:hover {
        background-color: #218838;
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    
    /* Estilos para resaltar la sección del gráfico */
    .highlight-section {
        animation: highlightAnimation 2s ease-in-out;
    }
    
    @keyframes highlightAnimation {
        0% { box-shadow: 0 0 0 0 rgba(0,123,255,.5); }
        50% { box-shadow: 0 0 20px 10px rgba(0,123,255,.5); }
        100% { box-shadow: 0 0 0 0 rgba(0,123,255,.5); }
    }
CSS;
$this->registerCss($css);
?>