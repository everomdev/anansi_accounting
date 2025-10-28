<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel common\models\IngredientStockSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Storage');
$this->params['breadcrumbs'][] = $this->title;
$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
?>
<div class="ingredient-stock-index">

    <!-- Selector de elementos por página y filtros mejorados -->
    <div class="row mb-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><?= Yii::t('app', 'Mostrar') ?></span>
                <select id="per-page-selector" class="form-select form-select-sm" style="width: auto; max-width: 78px;">
                    <?php foreach ([10, 25, 50, 100] as $value): ?>
                    <option value="<?= $value ?>" <?= $dataProvider->pagination->pageSize == $value ? 'selected' : '' ?>><?= $value ?></option>
                    <?php endforeach; ?>
                </select>
                <span class="input-group-text bg-light"><?= Yii::t('app', 'insumos por página') ?></span>
            </div>
        </div>
    </div>

    <?php Pjax::begin(['id' => 'ingredient-stock-storage-pjax']); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'id' => 'ingredient-stock-storage-grid',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'formatter' => $business->getFormatter(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'key',
            [
                'attribute' => 'ingredient',
                'label' => 'Insumo',
                'value' => function ($data) {
                    $parts = [];
                    
                    // Agregar el nombre del insumo
                    $parts[] = $data->ingredient;
                    
                    // Agregar marca si existe
                    if (!empty($data->brand)) {
                        $parts[] = $data->brand;
                    }
                    
                    // Agregar presentación si existe
                    if (!empty($data->presentation)) {
                        $parts[] = $data->presentation;
                    }
                    
                    // Agregar unidad de medida
                    //$parts[] = $data->um;
                    
                    return implode('  ', $parts);
                },
            ],
            'quantity',
            [
                'label' => Yii::t('app', "Value"),
                'value' => function ($data) {
                    return formatPrice($data->valueInMoney);
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => "{priceTrend} {update} {delete}",
                'buttons' => [
                    'priceTrend' => function ($url, $model, $key) {
                        return \yii\bootstrap5\Html::a(
                            \yii\bootstrap5\Html::tag('i', '', ['class' => 'bx bx-chart text-primary']),
                            \yii\helpers\Url::to(['ingredient-stock/price-trend', 'ingredientId' => $model->id])
                        );
                    }
                ],
                'visible' => false
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
<?php
$this->registerJs("
// Detector de cambio en elementos por página
document.getElementById('per-page-selector').addEventListener('change', function() {
    const pageSize = this.value;
    
    // Crear URL con nuevo tamaño de página
    let url = new URL(window.location);
    url.searchParams.set('per-page', pageSize);
    
    // Recargar con el nuevo tamaño de página
    $.pjax.reload({
        container: '#ingredient-stock-storage-pjax',
        url: url.toString(),
        timeout: 10000
    });
});
");
?>
<script>
    // Función para guardar elementos por página en localStorage
    function savePerPageToStorage(pageSize) {
        localStorage.setItem('ingredient-stock-storage-per-page', pageSize);
    }
    
    // Función para obtener elementos por página del localStorage
    function getPerPageFromStorage() {
        const saved = localStorage.getItem('ingredient-stock-storage-per-page');
        return saved || '10'; // Default 10 si no hay valor guardado
    }
    
    // Aplicar configuración guardada al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const perPageSelector = document.getElementById('per-page-selector');
        const savedPerPage = getPerPageFromStorage();
        
        // Establecer el valor guardado en el selector
        perPageSelector.value = savedPerPage;
        
        // Si el valor actual es diferente al guardado, aplicar el guardado
        const currentPageSize = '<?= $dataProvider->pagination->pageSize ?>';
        if (currentPageSize != savedPerPage) {
            // Crear URL con el valor guardado y recargar
            let url = new URL(window.location);
            url.searchParams.set('per-page', savedPerPage);
            
            $.pjax.reload({
                container: '#ingredient-stock-storage-pjax',
                url: url.toString(),
                timeout: 10000
            });
        }
    });
    
    // Detector de cambio en elementos por página
    document.getElementById('per-page-selector').addEventListener('change', function() {
        const pageSize = this.value;
        
        // Guardar en localStorage
        savePerPageToStorage(pageSize);
        
        // Crear URL con nuevo tamaño de página
        let url = new URL(window.location);
        url.searchParams.set('per-page', pageSize);
        
        // Recargar con el nuevo tamaño de página
        $.pjax.reload({
            container: '#ingredient-stock-storage-pjax',
            url: url.toString(),
            timeout: 10000
        });
    });
</script>
