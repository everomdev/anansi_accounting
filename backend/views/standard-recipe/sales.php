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

$this->title = Yii::t('app', "Sales");

// Preparamos las columnas para las tablas de alimentos
$foodGridColumns = [
    ['class' => \yii\grid\SerialColumn::class],
    [
        'attribute' => 'title',
        'filter' => true // Habilitar filtro automático
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
        'value' => function ($data) {
            return formatPercentage($data->costPercent);
        },
        'filter' => false
    ],
    [
        'label' => Yii::t('app', "Sales"),
        'format' => 'raw',
        'filter' => false,
        'value' => function ($data) use ($selectedMonth, $selectedYear) {
            $inputName = 'food[' . $data->id . ']';
            return Html::input('number', $inputName, $data->sales, [
                'class' => 'form-control sales-input',
                'data-id' => $data->id,
                'data-type' => 'recipe',
            ]);
        },
    ]
];

// Preparamos las columnas para las tablas de bebidas
$drinkGridColumns = [
    ['class' => \yii\grid\SerialColumn::class],
    [
        'attribute' => 'title',
        'filter' => Html::textInput('drink_title', 
            Yii::$app->request->get('drink_title', ''), 
            [
                'class' => 'form-control',
                'placeholder' => 'Buscar bebida...',
                'onchange' => 'this.form.submit()'
            ]
        )
    ],    [
        'attribute' => 'cost',
        'label' => "Costo",
        'value' => function ($data) {
            return formatCost($data->cost);
        },
        'filter' => false
    ],    [
        'attribute' => 'costPercent',
        'value' => function ($data) {
            return formatPercentage($data->costPercent);
        },
        'filter' => false
    ],
    [
        'label' => Yii::t('app', "Sales"),
        'format' => 'raw',
        'filter' => false,
        'value' => function ($data) use ($selectedMonth, $selectedYear) {
            $inputName = 'drink[' . $data->id . ']';
            return Html::input('number', $inputName, $data->sales, [
                'class' => 'form-control sales-input',
                'data-id' => $data->id,
                'data-type' => 'recipe',
            ]);
        },
    ]
];

// Preparamos las columnas para las tablas de combos
$comboGridColumns = [
    ['class' => \yii\grid\SerialColumn::class],
    [
        'attribute' => 'name',
        'label' => 'Título',
        'filter' => true // Habilitar filtro automático
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
        'label' => 'Costo %',
        'value' => function ($data) {
            return formatPercentage($data->cost_precent);
        },
        'filter' => false
    ],
    [
        'label' => Yii::t('app', "Sales"),
        'format' => 'raw',
        'filter' => false,
        'value' => function ($data) use ($selectedMonth, $selectedYear) {
            $inputName = 'combo[' . $data->id . ']';
            return Html::input('number', $inputName, $data->sales, [
                'class' => 'form-control sales-input',
                'data-id' => $data->id,
                'data-type' => 'menu',
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
    <div class="card-header">
        <h3 class="card-title mb-0"><?= Yii::t('app', 'Filtrar por mes y año') ?></h3>
    </div>
    <div class="card-body">
        <?= Html::beginForm(['sales'], 'get', ['data-pjax' => 1]) ?>
        <div class="row g-3">
            <div class="col-md-4">
                <label for="month-select" class="form-label">Mes</label>
                <?= Html::dropDownList('month',
                    $selectedMonth ?? date('n'), 
                    [
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
                    ],
                    ['class' => 'form-select', 'id' => 'month-select']
                ) ?>
            </div>
            
            <div class="col-md-3">
                <label for="year-select" class="form-label">Año</label>
                <?= Html::dropDownList('year',
                    $selectedYear ?? date('Y'),
                    $years,
                    ['class' => 'form-select', 'id' => 'year-select']
                ) ?>
            </div>
            
            <div class="col-md-2 d-flex align-items-end">
                <?= Html::submitButton('Filtrar', ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
        <?= Html::endForm() ?>
    </div>
</div>

<?= Html::beginForm(['save-monthly-sales'], 'post', ['id' => 'sales-form']) ?>

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title"><?= Yii::t('app', 'Food sales') ?></h3>
        <small class="text-muted"><?= $foodDataProvider->getTotalCount() ?> recetas encontradas</small>
    </div>
    <div class="card-body">
        <?= \yii\grid\GridView::widget([
            'dataProvider' => $foodDataProvider,
            'filterModel' => $foodSearchModel,
            'columns' => $foodGridColumns,
            'filterUrl' => Url::current([
                'month' => $selectedMonth,
                'year' => $selectedYear
            ], true)
        ]) ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title"><?= Yii::t('app', 'Drinking sales') ?></h3>
        <small class="text-muted"><?= $drinkDataProvider->getTotalCount() ?> recetas encontradas</small>
    </div>    <div class="card-body">
        <?php $form = Html::beginForm(['sales'], 'get', ['data-pjax' => 1]); ?>
        <?= Html::hiddenInput('month', $selectedMonth) ?>
        <?= Html::hiddenInput('year', $selectedYear) ?>
        <?= \yii\grid\GridView::widget([
            'dataProvider' => $drinkDataProvider,
            'columns' => $drinkGridColumns,
        ]) ?>
        <?= Html::endForm() ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title"><?= Yii::t('app', 'Venta de Combos') ?></h3>
        <small class="text-muted"><?= $comboDataProvider->getTotalCount() ?> combos encontrados</small>
    </div>
    <div class="card-body">
        <?= \yii\grid\GridView::widget([
            'dataProvider' => $comboDataProvider,
            'filterModel' => $comboSearchModel,
            'columns' => $comboGridColumns,
            'filterUrl' => Url::current([
                'month' => $selectedMonth,
                'year' => $selectedYear
            ], true)
        ]) ?>
    </div>
</div>

<div class="d-flex justify-content-end mb-4">
    <?= Html::hiddenInput('month', $selectedMonth, ['id' => 'month-hidden']) ?>
    <?= Html::hiddenInput('year', $selectedYear, ['id' => 'year-hidden']) ?>
    <?= Html::button('Guardar ventas', [
        'class' => 'btn btn-primary btn-lg',
        'id' => 'btn-save-sales'
    ]) ?>
</div>

<?= Html::endForm() ?>

<?php \yii\widgets\Pjax::end(); ?>

<?php
$saveUrl = Url::to(['save-monthly-sales']);
$js = <<< JS
// Al cambiar los selectores, actualizamos los campos ocultos
$('#month-select, #year-select').on('change', function() {
    $('#month-hidden').val($('#month-select').val());
    $('#year-hidden').val($('#year-select').val());
});

// Acción del botón guardar ventas
$('#btn-save-sales').on('click', function(e) {
    e.preventDefault();
    
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
            $('#btn-save-sales').prop('disabled', false).text('Guardar ventas');
        }
    });
});
JS;
$this->registerJs($js);

// Registramos SweetAlert2 si no está incluido
$this->registerJsFile('https://cdn.jsdelivr.net/npm/sweetalert2@11', [
    'position' => \yii\web\View::POS_END,
]);
?>
