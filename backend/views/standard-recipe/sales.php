<?php
/** @var $this \yii\web\View */
/** @var $dataProvider \yii\data\ActiveDataProvider */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;

$this->title = Yii::t('app', "Sales");

// Preparamos las columnas para las tablas
$gridColumns = [
    ['class' => \yii\grid\SerialColumn::class],
    'title',
    [
        'attribute' => 'cost',
        'format' => 'currency',
        'label' => "Costo"
    ],
    'costPercent:percent',
    [
        'label' => Yii::t('app', "Sales"),
        'format' => 'raw',
        'value' => function ($data) use ($selectedMonth, $selectedYear) {
            // Establecemos un nombre único basado en el tipo de modelo (comida, bebida o combo)
            $inputName = ($data instanceof \common\models\Menu) 
                ? 'combo[' . $data->id . ']' 
                : (($data->is_food) ? 'food[' . $data->id . ']' : 'drink[' . $data->id . ']');
                
            return Html::input('number', $inputName, $data->sales, [
                'class' => 'form-control sales-input',
                'data-id' => $data->id,
                'data-type' => ($data instanceof \common\models\Menu) ? 'menu' : 'recipe',
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
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0"><?= Yii::t('app', 'Filtro por fechas') ?></h3>
        <div class="d-flex gap-2">
            <?= Html::beginForm(['sales'], 'get', ['data-pjax' => 1]) ?>
            <div class="d-flex align-items-center gap-2">
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
                
                <?= Html::dropDownList('year',
                    $selectedYear ?? date('Y'),
                    $years,
                    ['class' => 'form-select', 'id' => 'year-select']
                ) ?>
                
                <?= Html::submitButton('Filtrar', ['class' => 'btn btn-primary']) ?>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<?= Html::beginForm(['save-monthly-sales'], 'post', ['id' => 'sales-form']) ?>

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title"><?= Yii::t('app', 'Food sales') ?></h3>
    </div>
    <div class="card-body">
        <?= \yii\grid\GridView::widget([
            'dataProvider' => $foodDataProvider,
            'columns' => $gridColumns
        ]) ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title"><?= Yii::t('app', 'Drinking sales') ?></h3>
    </div>
    <div class="card-body">
        <?= \yii\grid\GridView::widget([
            'dataProvider' => $drinkDataProvider,
            'columns' => $gridColumns
        ]) ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title"><?= Yii::t('app', 'Venta de Combos') ?></h3>
    </div>
    <div class="card-body">
        <?= \yii\grid\GridView::widget([
            'dataProvider' => $comboDataProvider,
            'columns' => $gridColumns
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
        },        success: function(response) {
            if (response.success) {
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
