<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $data array */
/** @var $sortByCostPercent array */
/** @var $sortByPopularity array */
/** @var $sortBySales array */
/** @var $family string */
/** @var $paretoCategories array */
/** @var $totalSales float */
/** @var $currentSort string */
/** @var $currentDirection string */
/** @var $selectedYear int */
/** @var $selectedMonth int|null */

$business = \backend\helpers\RedisKeys::getBusiness();
$total = count($data);

// Generar opciones de años (últimos 5 años y próximos 2)
$currentYear = date('Y');
$yearOptions = [];
for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
    $yearOptions[$i] = $i;
}

// Generar opciones de meses
$monthOptions = [
    '' => Yii::t('app', 'Todo el año'),
    1 => Yii::t('app', 'Enero'),
    2 => Yii::t('app', 'Febrero'), 
    3 => Yii::t('app', 'Marzo'),
    4 => Yii::t('app', 'Abril'),
    5 => Yii::t('app', 'Mayo'),
    6 => Yii::t('app', 'Junio'),
    7 => Yii::t('app', 'Julio'),
    8 => Yii::t('app', 'Agosto'),
    9 => Yii::t('app', 'Septiembre'),
    10 => Yii::t('app', 'Octubre'),
    11 => Yii::t('app', 'Noviembre'),
    12 => Yii::t('app', 'Diciembre')
];

$this->title = Yii::t('app', "Menu Analysis");
?>
<div class="card">    <div class="card-header">
        <div class="row">
            <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 mb-2">
                <?= \yii\bootstrap5\Html::dropDownList('', $family, \yii\helpers\ArrayHelper::map($business->recipeCategoriesMain, 'name', 'name'), [
                    'prompt' => Yii::t('app', 'All'),
                    'class' => 'form-control',
                    'data-url' => \yii\helpers\Url::to(['standard-recipe/analytics']),
                    'id' => 'family-selector'
                ]) ?>
            </div>
            <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 mb-2">
                <?= \yii\bootstrap5\Html::dropDownList('year', $selectedYear, $yearOptions, [
                    'class' => 'form-control',
                    'id' => 'year-selector'
                ]) ?>
            </div>
            <div class="col-sm-12 col-md-2 col-lg-2 col-xl-2 mb-2">
                <?= \yii\bootstrap5\Html::dropDownList('month', $selectedMonth, $monthOptions, [
                    'class' => 'form-control',
                    'id' => 'month-selector'
                ]) ?>
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3">
            </div>
            <div class="col-sm-12 col-md-3 col-lg-3 col-xl-3 text-md-end mb-2">
                <?= \yii\bootstrap5\Html::a(
                    Yii::t('app', 'Ver recomendaciones'),
                    ['standard-recipe/menu-improvement', '#' => 'recommendations-section'],
                    ['class' => 'btn btn-primary']
                ) ?>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>                        
                        <?= Yii::t('app', "Recipes") ?>
                        <span class="float-end">
                            <?= \yii\bootstrap5\Html::a(
                                ($currentSort === 'name' ? '<i class="fas fa-sort-' . ($currentDirection === 'asc' ? 'up' : 'down') . '"></i>' : '<i class="fas fa-sort"></i>'),
                                ['standard-recipe/analytics', 
                                    'family' => $family, 
                                    'year' => $selectedYear,
                                    'month' => $selectedMonth,
                                    'sort' => 'name', 
                                    'direction' => ($currentSort === 'name' && $currentDirection === 'asc' ? 'desc' : 'asc')
                                ],
                                ['class' => 'text-decoration-none']
                            ) ?>
                        </span>
                    </th>
                    <th>                        
                        <?= Yii::t('app', "Rentabilidad") ?>
                        <span class="float-end">
                            <?= \yii\bootstrap5\Html::a(
                                ($currentSort === 'cost-percent' ? '<i class="fas fa-sort-' . ($currentDirection === 'asc' ? 'up' : 'down') . '"></i>' : '<i class="fas fa-sort"></i>'),
                                ['standard-recipe/analytics', 
                                    'family' => $family, 
                                    'year' => $selectedYear,
                                    'month' => $selectedMonth,
                                    'sort' => 'cost-percent', 
                                    'direction' => ($currentSort === 'cost-percent' && $currentDirection === 'asc' ? 'desc' : 'asc')
                                ],
                                ['class' => 'text-decoration-none']
                            ) ?>
                        </span>
                    </th>
                    <th><?= Yii::t('app', "Position") ?></th>
                    <th>                        
                    <?= Yii::t('app', "Popularity") ?>
                        <span class="float-end">
                            <?= \yii\bootstrap5\Html::a(
                                ($currentSort === 'popularity' ? '<i class="fas fa-sort-' . ($currentDirection === 'asc' ? 'up' : 'down') . '"></i>' : '<i class="fas fa-sort"></i>'),
                                ['standard-recipe/analytics', 
                                    'family' => $family, 
                                    'year' => $selectedYear,
                                    'month' => $selectedMonth,
                                    'sort' => 'popularity', 
                                    'direction' => ($currentSort === 'popularity' && $currentDirection === 'asc' ? 'desc' : 'asc')
                                ],
                                ['class' => 'text-decoration-none']
                            ) ?>
                        </span>
                    </th>
                    <th><?= Yii::t('app', "Position") ?></th>
                    <th>                        
                        <?= Yii::t('app', "Sales") ?>
                        <span class="float-end">
                            <?= \yii\bootstrap5\Html::a(
                                ($currentSort === 'sales' ? '<i class="fas fa-sort-' . ($currentDirection === 'asc' ? 'up' : 'down') . '"></i>' : '<i class="fas fa-sort"></i>'),
                                ['standard-recipe/analytics', 
                                    'family' => $family, 
                                    'year' => $selectedYear,
                                    'month' => $selectedMonth,
                                    'sort' => 'sales', 
                                    'direction' => ($currentSort === 'sales' && $currentDirection === 'asc' ? 'desc' : 'asc')
                                ],
                                ['class' => 'text-decoration-none']
                            ) ?>
                        </span>
                    </th>
                    <th><?= Yii::t('app', "Position") ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data as $item): 
                    $itemKey = sprintf("%s_%s", $item['type'], $item['id']);
                    $costPercentPosition = array_search($itemKey, $sortByCostPercent) + 1;
                    $popularityPosition = array_search($itemKey, $sortByPopularity) + 1;
                    $salesPosition = array_search($itemKey, $sortBySales) + 1;
                    
                    // Asignar colores según la clasificación de Pareto que viene del controlador
                    $colorPareto = "#28a745"; // Verde por defecto
                    
                    if (isset($paretoCategories[$itemKey])) {
                        if ($paretoCategories[$itemKey] === 'amarillo') {
                            $colorPareto = "#ffc107";
                        } elseif ($paretoCategories[$itemKey] === 'rojo') {
                            $colorPareto = "#dc3545";
                        } elseif ($paretoCategories[$itemKey] === 'gris') {
                            $colorPareto = "#6c757d";
                        }
                    }
                    
                    // Calcular percentiles para colorear otras columnas (invertido porque ahora posición 1 = peor, posición alta = mejor)
                    $costPercentPercentile = $costPercentPosition / $total;
                    $colorCostPercent = $costPercentPercentile >= 0.8 ? "#28a745" : ($costPercentPercentile >= 0.5 ? "#ffc107" : "#dc3545");
                    
                    // Para popularidad y ventas, usar ranking directo (mejor = verde, ahora posiciones altas = mejores)
                    $colorPopularity = $popularityPosition >= ceil($total * 0.8) ? "#28a745" : 
                                      ($popularityPosition >= ceil($total * 0.5) ? "#ffc107" : "#dc3545");
                    
                    $colorSales = $salesPosition >= ceil($total * 0.8) ? "#28a745" : 
                                 ($salesPosition >= ceil($total * 0.5) ? "#ffc107" : "#dc3545");
                ?>                <tr>
                    <td><?= $item['name'] ?></td>
                    <td><?= formatPercentage($item['cost_percent'] * 100) ?></td>
                    <td style="background-color: <?= $colorCostPercent ?>"><strong class="text-white"><?= $costPercentPosition ?></strong></td>
                    <td><?= number_format($item['sales'], 0) ?></td>
                    <td style="background-color: <?= $colorPopularity ?>"><strong class="text-white"><?= $popularityPosition ?></strong></td>
                    <td><?= formatPrice($item['price'] * $item['sales']) ?></td>
                    <td style="background-color: <?= $colorSales ?>"><strong class="text-white"><?= $salesPosition ?></strong></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="back-to-top-floating">
    <a href="#" class="btn btn-outline-secondary btn-floating">
        <i class="fas fa-arrow-up"></i>
    </a>
</div>

<?php
$css = <<< CSS
th a {
    color: inherit;
}
th a:hover {
    color: #0d6efd;
}
.fa-sort {
    opacity: 0.5;
}
/* Estilos para el botón flotante */
.back-to-top-floating {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1000;
    display: none;
}

.btn-floating {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    transition: all 0.3s;
}

.btn-floating:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.3);
}
CSS;
$this->registerCss($css);

$js = <<< JS
// Función para construir URL con todos los parámetros actuales
function buildUrl(baseUrl, newParams = {}) {
    const searchParams = new URLSearchParams(window.location.search);
    
    // Agregar nuevos parámetros
    Object.keys(newParams).forEach(key => {
        if (newParams[key] !== null && newParams[key] !== '') {
            searchParams.set(key, newParams[key]);
        } else {
            searchParams.delete(key);
        }
    });
    
    // Mantener parámetros de ordenamiento si no se están cambiando
    if (!newParams.hasOwnProperty('sort') && searchParams.has('sort')) {
        // Los parámetros de ordenamiento ya están en searchParams
    }
    if (!newParams.hasOwnProperty('direction') && searchParams.has('direction')) {
        // Los parámetros de ordenamiento ya están en searchParams
    }
    
    const queryString = searchParams.toString();
    return baseUrl + (queryString ? '?' + queryString : '');
}

// Manejar cambio en selector de familia
$(document).on('change', "#family-selector", function(event){
    event.preventDefault();
    let family = $(this).val();
    let year = $('#year-selector').val();
    let month = $('#month-selector').val();
    let url = $(this).data('url');
    if(family.length === 0){
        family = 'all';
    }
    
    const newUrl = buildUrl(url, { family: family, year: year, month: month });
    window.location.href = newUrl;
    return false;
});

// Manejar cambio en selector de año
$(document).on('change', "#year-selector", function(event){
    event.preventDefault();
    let year = $(this).val();
    let month = $('#month-selector').val();
    let family = $('#family-selector').val() || 'all';
    let url = $('#family-selector').data('url');
    
    const newUrl = buildUrl(url, { year: year, month: month, family: family });
    window.location.href = newUrl;
    return false;
});

// Manejar cambio en selector de mes
$(document).on('change', "#month-selector", function(event){
    event.preventDefault();
    let month = $(this).val();
    let year = $('#year-selector').val();
    let family = $('#family-selector').val() || 'all';
    let url = $('#family-selector').data('url');
    
    const newUrl = buildUrl(url, { year: year, month: month, family: family });
    window.location.href = newUrl;
    return false;
});

// Mostrar/ocultar botón flotante al hacer scroll
$(window).scroll(function() {
    if ($(this).scrollTop() > 200) {
        $('.back-to-top-floating').fadeIn();
    } else {
        $('.back-to-top-floating').fadeOut();
    }
});

// Botón volver arriba flotante
$('.back-to-top-floating a').on('click', function(e) {
    e.preventDefault();
    $('html, body').animate(
        {
            scrollTop: 0,
        },
        500,
        'linear'
    );
});
JS;
$this->registerJs($js);
?>