<?php
/** @var $this \yii\web\View */

use common\models\StandardRecipe;

$this->title = Yii::t('app', "Menu improvements");
$business = \backend\helpers\RedisKeys::getBusiness();

// Obtener los datos del análisis del menú
$menuAnalysisData = Yii::$app->session->get('menuAnalysisData', []);
$sortByCostPercent = Yii::$app->session->get('sortByCostPercent', []);
$sortByPopularity = Yii::$app->session->get('sortByPopularity', []);
$sortBySales = Yii::$app->session->get('sortBySales', []);
$paretoCategories = Yii::$app->session->get('paretoCategories', []);

// Categorizar recetas según análisis
$excellentRecipes = [];       // Receta Diamante
$magnetRecipes = [];          // Receta Imán
$mysteryRecipes = [];         // Receta Misteriosa
$latentRecipes = [];          // Receta Latente
$focusRecipes = [];           // Receta Foco Rojo
$fragileRecipes = [];         // Receta Llamativa pero Frágil
$nicheRecipes = [];           // Receta de Nicho
$toxicRecipes = [];           // Receta Tóxica

// Total de recetas para calcular percentiles
$total = count($menuAnalysisData);

if ($total > 0) {
    foreach ($menuAnalysisData as $item) {
        $itemKey = sprintf("%s_%s", $item['type'], $item['id']);
        
        // Obtener posiciones en cada categoría
        $costPercentPosition = array_search($itemKey, $sortByCostPercent) + 1;
        $popularityPosition = array_search($itemKey, $sortByPopularity) + 1;
        $salesPosition = array_search($itemKey, $sortBySales) + 1;
        
        // Calcular si cada posición está en verde, amarillo o rojo
        $costPercentPercentile = $costPercentPosition / $total;
        $isCostGreen = $costPercentPercentile <= 0.2;
        $isCostYellow = $costPercentPercentile > 0.2 && $costPercentPercentile <= 0.5;
        $isCostRed = $costPercentPercentile > 0.5;
        
        $isPopularGreen = $popularityPosition <= ceil($total * 0.2);
        $isPopularYellow = $popularityPosition > ceil($total * 0.2) && $popularityPosition <= ceil($total * 0.5);
        $isPopularRed = $popularityPosition > ceil($total * 0.5);
        
        $isSalesGreen = $salesPosition <= ceil($total * 0.2);
        $isSalesYellow = $salesPosition > ceil($total * 0.2) && $salesPosition <= ceil($total * 0.5);
        $isSalesRed = $salesPosition > ceil($total * 0.5);
        
        // Categorizar las recetas según las reglas
        
        if ($isCostGreen && $isPopularGreen && $isSalesGreen) {
            $excellentRecipes[] = $item['name']; // Receta Diamante
        } 
        elseif ($isCostGreen && $isPopularGreen && $isSalesRed) {
            $magnetRecipes[] = $item['name']; // Receta Imán
        }
        elseif ($isCostGreen && $isPopularRed && $isSalesGreen) {
            $mysteryRecipes[] = $item['name']; // Receta Misteriosa
        }
        elseif ($isCostGreen && $isPopularRed && $isSalesRed) {
            $latentRecipes[] = $item['name']; // Receta Latente
        }
        elseif ($isCostRed && $isPopularGreen && $isSalesGreen) {
            $focusRecipes[] = $item['name']; // Receta Foco Rojo
        }
        elseif ($isCostRed && $isPopularGreen && $isSalesRed) {
            $fragileRecipes[] = $item['name']; // Receta Llamativa pero Frágil
        }
        elseif ($isCostRed && $isPopularRed && $isSalesGreen) {
            $nicheRecipes[] = $item['name']; // Receta de Nicho
        }
        elseif ($isCostRed && $isPopularRed && $isSalesRed) {
            $toxicRecipes[] = $item['name']; // Receta Tóxica
        }
    }
}

?>
<?php \yii\widgets\Pjax::begin(['id' => 'pjax-menu-improvement']); ?>
<?php
$sum = array_sum(\yii\helpers\ArrayHelper::getColumn($data, function ($item) {
    return $item->getCostPercent(true);
}));

$countData = count($data);

$yield = $countData == 0 ? 0 : round($sum / $countData , 2);
?>
<div class="card">
<div class="card-header d-flex justify-content-between align-items-center">        <h4 class="mb-0">
            <?= Yii::t('app', "New profitability of menu: {yield}", [
                'yield' => formatPercentage($yield)
            ]) ?>
        </h4>
        <a href="#recommendations-section" class="btn btn-primary btn-sm">
            <i class="fas fa-lightbulb me-1"></i> <?= Yii::t('app', "Ver Recomendaciones") ?>
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                <th><?= Yii::t('app', "Recipe") ?></th>
                <th><?= Yii::t('app', "Cost") ?></th>
                <th><?= Yii::t('app', "Price") ?></th>
                <th><?= Yii::t('app', "Cost percent") ?></th>
                </thead>
                <tbody>
                <?php foreach ($data as $item): ?>
                    <tr>
                        <td><?= $item->name ?></td>
                        <td>
                            <?= \yii\bootstrap5\Html::activeInput('text', $item, 'custom_cost', [
                                'class' => 'form-control modify-custom-field',
                                'value' => $formatter->asDecimal($item->custom_cost,2),
                                'data-url' => get_class($item) == StandardRecipe::class ? 
                                    \yii\helpers\Url::to(['standard-recipe/save-sales', 'id' => $item->id]) : 
                                    \yii\helpers\Url::to(['menu/save-sales', 'id' => $item->id])
                            ]) ?>
                        </td>
                        <td>
                            <?= \yii\bootstrap5\Html::activeInput('text', $item, 'custom_price', [
                                'class' => 'form-control modify-custom-field',
                                'value' => $formatter->asDecimal($item->custom_price,2),
                                'data-url' => get_class($item) == StandardRecipe::class ? 
                                    \yii\helpers\Url::to(['standard-recipe/save-sales', 'id' => $item->id]) : 
                                    \yii\helpers\Url::to(['menu/save-sales', 'id' => $item->id])
                            ]) ?>
                        </td>
                        <td><?= formatPercentage($item->getCostPercent(true)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Nueva sección para las recomendaciones de mejora -->
<div class="card mt-4" id="recommendations-section">
    <div class="card-header">   
        <h4><?= Yii::t('app', "Recomendaciones de Mejora del Menú") ?></h4>
        <p class="text-muted small">
            <?= Yii::t('app', "Basado en el análisis matricial de rentabilidad, popularidad y monto de venta") ?>
        </p>
    </div>
    <div class="card-body">
        <?php if (!$menuAnalysisData): ?>
            <div class="alert alert-info">
                <?= Yii::t('app', "Por favor, visite la página de Análisis del Menú y después regrese a esta página para obtener las recomendaciones.") ?>
                <?= \yii\bootstrap5\Html::a(
                    Yii::t('app', "Ir a Análisis del Menú"),
                    ['standard-recipe/analytics'],
                    ['class' => 'btn btn-primary btn-sm ms-3']
                ) ?>
            </div>
        <?php else: ?>
            <!-- 1. Recetas Diamante (Excelencia) -->
            <div class="recommendation-section mb-4">
                <h5 class="text-success">
                    <i class="fas fa-gem me-2"></i>
                    <?= Yii::t('app', "Recetas Diamante") ?>
                </h5>
                <p class="text-muted">
                    <?= Yii::t('app', "Alta rentabilidad + Alta popularidad + Alto monto de venta. Son tus joyas: rentables, queridas y altamente productivas.") ?>
                </p>
                <?php if ($excellentRecipes): ?>
                    <div class="recipe-list">
                        <?php foreach ($excellentRecipes as $recipe): ?>
                            <span class="badge bg-success me-2 mb-2"><?= $recipe ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <h6><?= Yii::t('app', "Estrategias recomendadas:") ?></h6>
                        <ul>
                            <li>Promover en redes y menú como especialidad de la casa</li>
                            <li>Capacitar al equipo para sugerirlas proactivamente</li>
                            <li>Asegurar disponibilidad con buen stock de insumos</li>
                            <li>Analizar si puedes replicar su estilo para nuevas recetas</li>
                        </ul>
                    </div>
                <?php else: ?>
                    <p class="fst-italic"><?= Yii::t('app', "No hay recetas en esta categoría.") ?></p>
                <?php endif; ?>
            </div>

            <!-- 2. Recetas Imán -->
            <div class="recommendation-section mb-4">
                <h5 class="text-info">
                    <i class="fas fa-magnet me-2"></i>
                    <?= Yii::t('app', "Recetas Imán") ?>
                </h5>
                <p class="text-muted">
                    <?= Yii::t('app', "Alta rentabilidad + Alta popularidad + Bajo monto de venta. Gustan mucho y dejan buen margen, aunque el ingreso por unidad es bajo.") ?>
                </p>
                <?php if ($magnetRecipes): ?>
                    <div class="recipe-list">
                        <?php foreach ($magnetRecipes as $recipe): ?>
                            <span class="badge bg-info me-2 mb-2"><?= $recipe ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <h6><?= Yii::t('app', "Estrategias recomendadas:") ?></h6>
                        <ul>
                            <li>Usar para atraer comensales y complementar con platillos más caros</li>
                            <li>Incluir como acompañamiento o entrada para elevar el ticket</li>
                            <li>Destacar como extra en platillos más caros</li>
                            <li>Evaluar ajuste de precio si el mercado lo permite</li>
                        </ul>
                    </div>
                <?php else: ?>
                    <p class="fst-italic"><?= Yii::t('app', "No hay recetas en esta categoría.") ?></p>
                <?php endif; ?>
            </div>

            <!-- 3. Recetas Misteriosas -->
            <div class="recommendation-section mb-4">
                <h5 class="text-warning">
                    <i class="fas fa-question-circle me-2"></i>
                    <?= Yii::t('app', "Recetas Misteriosas") ?>
                </h5>
                <p class="text-muted">
                    <?= Yii::t('app', "Alta rentabilidad + Baja popularidad + Alto monto de venta. Tienen buen margen pero no se venden mucho. ¡Puede haber oro escondido!") ?>
                </p>
                <?php if ($mysteryRecipes): ?>
                    <div class="recipe-list">
                        <?php foreach ($mysteryRecipes as $recipe): ?>
                            <span class="badge bg-warning me-2 mb-2"><?= $recipe ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <h6><?= Yii::t('app', "Estrategias recomendadas:") ?></h6>
                        <ul>
                            <li>Analizar si los ingredientes, nombre o presentación no conectan</li>
                            <li>Verificar ubicación en el menú y recomendación del personal</li>
                            <li>Ofrecer degustaciones o promociones para medir aceptación</li>
                            <li>Relanzar con nuevo nombre o presentación si tiene potencial</li>
                        </ul>
                    </div>
                <?php else: ?>
                    <p class="fst-italic"><?= Yii::t('app', "No hay recetas en esta categoría.") ?></p>
                <?php endif; ?>
            </div>

            <!-- 4. Recetas Latentes -->
            <div class="recommendation-section mb-4">
                <h5 class="text-primary">
                    <i class="fas fa-seedling me-2"></i>
                    <?= Yii::t('app', "Recetas Latentes") ?>
                </h5>
                <p class="text-muted">
                    <?= Yii::t('app', "Alta rentabilidad + Baja popularidad + Bajo monto de venta. No se venden ni dejan mucho ingreso, pero son rentables. Pueden tener potencial oculto.") ?>
                </p>
                <?php if ($latentRecipes): ?>
                    <div class="recipe-list">
                        <?php foreach ($latentRecipes as $recipe): ?>
                            <span class="badge bg-primary me-2 mb-2"><?= $recipe ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <h6><?= Yii::t('app', "Estrategias recomendadas:") ?></h6>
                        <ul>
                            <li>Reformular o relanzar antes de decidir eliminarlas</li>
                            <li>Evaluar si puede transformarse en guarnición o parte de otro platillo</li>
                            <li>Investigar si los ingredientes no gustan o si el precio es desfavorable</li>
                            <li>Probar promociones puntuales para medir su potencial</li>
                        </ul>
                    </div>
                <?php else: ?>
                    <p class="fst-italic"><?= Yii::t('app', "No hay recetas en esta categoría.") ?></p>
                <?php endif; ?>
            </div>

            <!-- 5. Recetas Foco Rojo -->
            <div class="recommendation-section mb-4">
                <h5 class="text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= Yii::t('app', "Recetas Foco Rojo") ?>
                </h5>
                <p class="text-muted">
                    <?= Yii::t('app', "Baja rentabilidad + Alta popularidad + Alto monto de venta. Se venden mucho pero drenan tus ganancias.") ?>
                </p>
                <?php if ($focusRecipes): ?>
                    <div class="recipe-list">
                        <?php foreach ($focusRecipes as $recipe): ?>
                            <span class="badge bg-danger me-2 mb-2"><?= $recipe ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <h6><?= Yii::t('app', "Estrategias recomendadas:") ?></h6>
                        <ul>
                            <li>Ajustar precio para mejorar margen sin afectar volumen</li>
                            <li>Sustituir ingredientes costosos sin perder calidad</li>
                            <li>Reducir ligeramente la porción si no afecta percepción</li>
                            <li>Usar como platillo ancla para atraer clientes</li>
                        </ul>
                    </div>
                <?php else: ?>
                    <p class="fst-italic"><?= Yii::t('app', "No hay recetas en esta categoría.") ?></p>
                <?php endif; ?>
            </div>

            <!-- 6. Recetas Llamativas pero Frágiles -->
            <div class="recommendation-section mb-4">
                <h5 class="text-pink">
                    <i class="fas fa-heart-broken me-2"></i>
                    <?= Yii::t('app', "Recetas Llamativas pero Frágiles") ?>
                </h5>
                <p class="text-muted">
                    <?= Yii::t('app', "Baja rentabilidad + Alta popularidad + Bajo monto de venta. Son populares pero te dejan muy poco. Cuidado con sostenerlas mucho tiempo.") ?>
                </p>
                <?php if ($fragileRecipes): ?>
                    <div class="recipe-list">
                        <?php foreach ($fragileRecipes as $recipe): ?>
                            <span class="badge bg-pink me-2 mb-2"><?= $recipe ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <h6><?= Yii::t('app', "Estrategias recomendadas:") ?></h6>
                        <ul>
                            <li>Evaluar reformulación de ingredientes, presentación o porciones</li>
                            <li>Ofrecer en horarios específicos y limitar su presencia</li>
                            <li>Aumentar ligeramente el precio o combinar con productos más rentables</li>
                            <li>Considerar reemplazo con receta más eficiente si no mejora</li>
                        </ul>
                    </div>
                <?php else: ?>
                    <p class="fst-italic"><?= Yii::t('app', "No hay recetas en esta categoría.") ?></p>
                <?php endif; ?>
            </div>

            <!-- 7. Recetas de Nicho -->
            <div class="recommendation-section mb-4">
                <h5 class="text-purple">
                    <i class="fas fa-search-dollar me-2"></i>
                    <?= Yii::t('app', "Recetas de Nicho") ?>
                </h5>
                <p class="text-muted">
                    <?= Yii::t('app', "Baja rentabilidad + Baja popularidad + Alto monto de venta. No se venden mucho ni son rentables, pero cuando se venden dejan buena ganancia.") ?>
                </p>
                <?php if ($nicheRecipes): ?>
                    <div class="recipe-list">
                        <?php foreach ($nicheRecipes as $recipe): ?>
                            <span class="badge bg-purple me-2 mb-2"><?= $recipe ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <h6><?= Yii::t('app', "Estrategias recomendadas:") ?></h6>
                        <ul>
                            <li>Usar en fechas especiales, para grupos o bajo pedido</li>
                            <li>Convertir en platillo de temporada o para eventos especiales</li>
                            <li>Analizar si se puede ajustar la receta para subir su margen</li>
                            <li>Ofrecer como exclusiva para reservas o experiencias premium</li>
                        </ul>
                    </div>
                <?php else: ?>
                    <p class="fst-italic"><?= Yii::t('app', "No hay recetas en esta categoría.") ?></p>
                <?php endif; ?>
            </div>

            <!-- 8. Recetas Tóxicas -->
            <div class="recommendation-section mb-4">
                <h5 class="text-secondary">
                    <i class="fas fa-skull-crossbones me-2"></i>
                    <?= Yii::t('app', "Recetas Tóxicas") ?>
                </h5>
                <p class="text-muted">
                    <?= Yii::t('app', "Baja rentabilidad + Baja popularidad + Bajo monto de venta. Consumen recursos sin retorno.") ?>
                </p>
                <?php if ($toxicRecipes): ?>
                    <div class="recipe-list">
                        <?php foreach ($toxicRecipes as $recipe): ?>
                            <span class="badge bg-secondary me-2 mb-2"><?= $recipe ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3">
                        <h6><?= Yii::t('app', "Estrategias recomendadas:") ?></h6>
                        <ul>
                            <li>Eliminar del menú sin dudar</li>
                            <li>Analizar aprendizajes de su falla</li>
                            <li>Sustituir con recetas de mayor potencial</li>
                            <li>Usar su espacio para destacar nuevas opciones</li>
                        </ul>
                    </div>
                <?php else: ?>
                    <p class="fst-italic"><?= Yii::t('app', "No hay recetas en esta categoría.") ?></p>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>
</div>
<div class="back-to-top-floating">
    <a href="#" class="btn btn-outline-secondary btn-floating">
        <i class="fas fa-arrow-up"></i>
    </a>
</div>
<?php \yii\widgets\Pjax::end(); ?>
<?php
$js = <<< JS
$(document).on('change', ".modify-custom-field", function(event){
    event.preventDefault();
    let _this = $(this);
    let value = _this.val();
    let url = _this.data('url');
    let fieldName = _this.attr('name');
    let data = {};
    data[fieldName] = value;
    $.ajax({
        url: url,
        type: 'post',
        data: data
    }).done(function(response){
        $.pjax.reload({container: "#pjax-menu-improvement"});
    })
    return false;
});
// Suavizar el desplazamiento a las secciones
$('a[href="#recommendations-section"]').on('click', function(e) {
    e.preventDefault();
    $('html, body').animate(
        {
            scrollTop: $('#recommendations-section').offset().top,
        },
        500,
        'linear'
    );
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

// Estilos para la sección de recomendaciones
$css = <<< CSS
.recipe-list {
    line-height: 2;
}
.recommendation-section {
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}
.recommendation-section:last-child {
    border-bottom: none;
}
.badge {
    font-size: 0.9rem;
    padding: 8px 12px;
}

/* Estilos para el header con botón al lado */
.card-header.d-flex {
    flex-wrap: wrap;
    gap: 10px;
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
.bg-pink {
    background-color: #e83e8c !important;
}
.bg-purple {
    background-color: #6f42c1 !important;
}
.text-pink {
    color: #e83e8c !important;
}
.text-purple {
    color: #6f42c1 !important;
}
CSS;
$this->registerCss($css);
?>