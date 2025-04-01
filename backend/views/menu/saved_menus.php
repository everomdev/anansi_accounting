<?php
/** @var $this \yii\web\View */
/** @var $dataProvider \yii\data\ActiveDataProvider */
/** @var $menuBundleProducts array */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;

$this->title = "Menús guardados";
?>
<?php //die(var_dump($rentabilidadReal)); ?>
<div class="card">
    <div class="card-header">
        <h4><?= Html::encode($this->title) ?></h4>
    </div>
    <div class="card-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => \yii\grid\SerialColumn::class],
                'date:date',
                [
                    'label' => 'Rentabilidad Teórica',
                    'format' => 'raw',
                    'value' => function ($model) use ($menuBundleProducts) {
                        if (isset($menuBundleProducts[$model->id])) {
                            $html = '<div class="rentabilidad-wrapper">';
                            
                            foreach ($menuBundleProducts[$model->id] as $category => $value) {
                                // Mostrar la categoría y el valor con formato de porcentaje
                                $colorClass = '';
                                $formattedValue = floatval($value);
                                
                                // Determinar el color según el valor de rentabilidad
                                if ($formattedValue <= 25) {
                                    $colorClass = 'text-success'; // Verde - Excelente
                                } elseif ($formattedValue <= 40) {
                                    $colorClass = 'text-warning'; // Amarillo - Aceptable
                                } else {
                                    $colorClass = 'text-danger'; // Rojo - Preocupante
                                }
                                
                                $html .= '<div class="rentabilidad-item mb-1">';
                                $html .= '<span class="badge bg-secondary me-2">' . Html::encode($category) . '</span>';
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
                        if (isset($rentabilidadReal[$model->id])) {
                            $html = '<div class="rentabilidad-wrapper">';
                            
                            foreach ($rentabilidadReal[$model->id] as $category => $value) {
                                // Mostrar la categoría y el valor con formato de porcentaje
                                $colorClass = '';
                                $formattedValue = floatval($value);
                                
                                // Determinar el color según el valor de rentabilidad
                                if ($formattedValue <= 25) {
                                    $colorClass = 'text-success'; // Verde - Excelente
                                } elseif ($formattedValue <= 40) {
                                    $colorClass = 'text-warning'; // Amarillo - Aceptable
                                } else {
                                    $colorClass = 'text-danger'; // Rojo - Preocupante
                                }
                                
                                $html .= '<div class="rentabilidad-item mb-1">';
                                $html .= '<span class="badge bg-secondary me-2">' . Html::encode($category) . '</span>';
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
                    'label' => 'Rentabilidad Real del Menú',
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
                [
                    'class' => \yii\grid\ActionColumn::class,
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model, $key) {
                            return Html::a('<i class="bi bi-eye"></i> Ver', 
                                ['/standard-recipe/menu-recipes', 'bundle' => $model->id], 
                                ['class' => 'btn btn-primary btn-sm']);
                        }
                    ]
                ]
            ],
            'options' => [
                'id' => 'menu-grid'
            ],
            'tableOptions' => ['class' => 'table table-striped table-hover'],
            'summary' => '<div class="summary-info">Mostrando <b>{begin}-{end}</b> de <b>{totalCount}</b> elementos</div>',
        ]) ?>
    </div>
</div>

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

<div class="card mt-4">
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
CSS;
$this->registerCss($css);
?>