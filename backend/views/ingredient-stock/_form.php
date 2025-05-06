<?php

use common\models\Category;
use common\models\Provider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\IngredientStock */
/* @var $form yii\widgets\ActiveForm */

$ingredients = (new \yii\db\Query())
    ->select("*")
    ->from('ingredient')
    ->all();

$autocompleteName = \yii\helpers\ArrayHelper::getColumn($ingredients, 'name');
$autocompleteUm = array_values(array_unique(\yii\helpers\ArrayHelper::getColumn($ingredients, 'um')));
$autocompletePortions = array_values(array_unique(\yii\helpers\ArrayHelper::getColumn($ingredients, 'portions_per_unit')));
$autocompletePortionsUm = array_values(array_unique(\yii\helpers\ArrayHelper::getColumn($ingredients, 'portion_um')));

$autocompleteCategories = array_values(array_unique(\yii\helpers\ArrayHelper::getColumn(Category::all(), 'name')));

$this->registerJsFile(Yii::getAlias("@web/js/ingredient-stock/form.js"), [
    'depends' => \yii\web\YiiAsset::class,
    'position' => $this::POS_END
]);

$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$ums = \common\models\UnitOfMeasurement::findAll(['business_id' => $business['id']]);
$currencySymbol = \Symfony\Component\Intl\Currencies::getSymbol(strtoupper($business['currency_code']));
$providers = \yii\helpers\ArrayHelper::map(Provider::find()->where(['business_id' => $business['id']])->all(), 'id', 'name');
?>
<div class="ingredient-stock-form">
    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => true
    ]); ?>

    <div class="card">
        <div class="card-body">
            <?= $form->field($model, 'final_quantity')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'quantity')->hiddenInput()->label(false) ?>

            <div class="row mb-3">
                <h5 class="card-title mb-3">Información del Insumo</h5>
                
                <div class="col-sm-12 col-md-6 col-lg-4 col-xl-4 mb-3">
                    <?= $form->field($model, 'ingredient')->widget(\kartik\typeahead\Typeahead::class, [
                        'scrollable' => true,
                        'defaultSuggestions' => $autocompleteName,
                        'dataset' => [
                            [
                                'local' => $autocompleteName,
                                'limit' => 10,
                            ]
                        ],
                    ])->label('Nombre del Insumo*') ?>
                </div>
                
                <div class="col-sm-12 col-md-6 col-lg-4 col-xl-4 mb-3">
                    <?= $form->field($model, 'category_id')->dropDownList(
                        \yii\helpers\ArrayHelper::map(Category::all(), 'id', 'name'), 
                        ['prompt' => '-- Seleccione una categoría --', 'data-url' => Url::to(['ingredient-stock/generate-key'])]
                    )->label('Categoría*') ?>
                </div>
                
                <div class="col-sm-12 col-md-4 col-lg-2 col-xl-2 mb-3">
                    <?= $form->field($model, 'key')->textInput(['placeholder' => 'Ej: FRT-001'])->label('Clave*') ?>
                </div>
                
                <div class="col-sm-12 col-md-4 col-lg-2 col-xl-2 mb-3">
                    <?= $form->field($model, 'um')->dropDownList(
                        \yii\helpers\ArrayHelper::map($ums, 'name', 'name'),
                        ['prompt' => '-- Seleccione --']
                    )->label('Unidad de Compra*') ?>
                </div>
            </div>
            
            <div class="row mb-3">
                <h5 class="card-title mb-3">Porciones</h5>
                
                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3 mb-3">
                    <?= $form->field($model, 'portions_per_unit')->widget(\kartik\typeahead\Typeahead::class, [
                        'scrollable' => true,
                        'defaultSuggestions' => $autocompletePortions,
                        'dataset' => [
                            [
                                'local' => $autocompletePortions,
                                'limit' => 10,
                            ]
                        ],
                    ])->label('Equivalencia a unidades de cocina') ?>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3 mb-3">
                    <?= $form->field($model, 'portion_um')->dropDownList(
                        \yii\helpers\ArrayHelper::map($ums, 'name', 'name'),
                        ['prompt' => '-- Seleccione --']
                    )->label('Unidades de cocina') ?>
                </div>
            </div>
            
            <div class="row mb-3">
                <h5 class="card-title mb-3">Precios y Rendimiento</h5>
                
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3 workflow-step mb-3">
                    <?= $form->field($model, 'price', [
                        'template' => "{label}<div class='input-group'><span class='input-group-text'>${currencySymbol}</span>{input}</div>{hint}{error}",
                        'inputOptions' => ['class' => 'form-control', 'id' => 'ingredientstock-price', 'required' => true, 'placeholder' => 'Ej: 500.00']
                    ])->textInput()->label("Precio de compra*") ?>
                    <div class="form-text">Ingrese el precio de compra del insumo</div>
                </div>
                
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3 workflow-step mb-3">
                    <?= $form->field($model, 'yield', [
                        'template' => "{label}<div class='input-group'>{input}<span class='input-group-text'>%</span><button type='button' class='btn btn-outline-primary' id='compute-yield'><i class='bi bi-calculator'></i> Calcular</button></div>{hint}{error}",
                        'inputOptions' => ['class' => 'form-control', 'id' => 'ingredientstock-yield', 'required' => true, 'placeholder' => 'Ej: 85']
                    ])->textInput()->label("Factor de rendimiento*") ?>
                    <div class="form-text">Entre 1% y 100%</div>
                </div>
                
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3 workflow-step mb-3">
                    <?= $form->field($model, 'adjustedPrice', [
                        'template' => "{label}<div class='input-group'><span class='input-group-text'>${currencySymbol}</span>{input}</div>{hint}{error}",
                        'inputOptions' => ['class' => 'form-control bg-light', 'id' => 'ingredientstock-adjustedprice', 'readonly' => true]
                    ])->textInput()->label("Precio ajustado*") ?>
                    <div class="form-text">Calculado: Precio ÷ Factor</div>
                </div>
            </div>
            
            <div class="row mb-3">
                <h5 class="card-title mb-3">Información Adicional</h5>
                
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 mb-3">
                    <?= $form->field($model, 'providers')->widget(Select2::class, [
                        'data' => \yii\helpers\ArrayHelper::map(
                            Provider::find()->where(['business_id' => $business['id']])->all(), 
                            'id', 
                            function($provider) {
                                return $provider->business_name ?? $provider->getBusiness()->one()->name ?? $provider->name;
                            }
                        ),
                        'options' => ['placeholder' => 'Selecciona proveedores...', 'multiple' => true],
                        'pluginOptions' => [
                            'maximumSelectionLength' => 5,
                            'allowClear' => true
                        ],
                    ])->label("Proveedores") ?>
                </div>
                
                <div class="col-12 mb-3">
                    <?= $form->field($model, 'observations')->textarea([
                        'rows' => 4, 
                        'placeholder' => 'Añada aquí cualquier nota relevante sobre el insumo...'
                    ])->label('Observaciones') ?>
                </div>
            </div>
        </div>
        
        <div class="card-footer">
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
                <?= Html::a(Yii::t('app', "Cancel"), ['ingredient-stock/index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-yield',
]);

echo \yii\bootstrap5\Html::label(Yii::t('app', 'Initial Quantity'), 'initial-quantity');
echo \yii\bootstrap5\Html::input(
    'number',
    'initial-quantity',
    '',
    ['class' => 'form-control', 'id' => 'initial-quantity']
);
echo \yii\bootstrap5\Html::label(Yii::t('app', 'Final Quantity'), 'final-quantity');
echo \yii\bootstrap5\Html::input(
    'number',
    'final-quantity',
    '',
    ['class' => 'form-control', 'id' => 'final-quantity']
);

echo \yii\bootstrap5\Html::label(Yii::t('app', 'Factor de rendimiento'), 'yield-result');
echo "<span class='form-control' id='yield-result'>0 %</span>";


echo \yii\bootstrap5\Html::button(Yii::t('app', 'Aceptar'), [
    'class' => 'btn btn-outline-primary mt-3',
    'id' => 'btn-compute-yield'
]);

\yii\bootstrap5\Modal::end();

?>
<script>
    // Agregar al archivo form.js
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a los campos
    const priceField = document.getElementById('ingredientstock-price');
    const yieldField = document.getElementById('ingredientstock-yield');
    const adjustedPriceField = document.getElementById('ingredientstock-adjustedprice');
    
    // Función para calcular el precio ajustado
    function calculateAdjustedPrice() {
        const price = parseFloat(priceField.value);
        let yieldValue = parseFloat(yieldField.value);
        
        if (!isNaN(price) && !isNaN(yieldValue) && yieldValue > 0) {
            // Convertir porcentaje a decimal (100% = 1.0)
            yieldValue = yieldValue / 100;
            
            // Calcular precio ajustado: precio ÷ factor de rendimiento
            const adjustedPrice = price / yieldValue;
            
            // Formatear a 2 decimales y actualizar el campo
            adjustedPriceField.value = adjustedPrice.toFixed(2);
        } else {
            adjustedPriceField.value = '';
        }
    }
    
    // Validar que el factor de rendimiento esté entre 1 y 100
    function validateYield() {
        let yieldValue = parseFloat(yieldField.value);
        
        if (isNaN(yieldValue)) {
            return;
        }
        
        if (yieldValue < 1) {
            yieldValue = 1;
            yieldField.value = 1;
        } else if (yieldValue > 100) {
            yieldValue = 100;
            yieldField.value = 100;
        }
        
        calculateAdjustedPrice();
    }
    
    // Vincular eventos
    if (priceField) {
        priceField.addEventListener('input', calculateAdjustedPrice);
    }
    
    if (yieldField) {
        yieldField.addEventListener('input', validateYield);
        yieldField.addEventListener('change', validateYield);
    }
    
    // Verificar al cargar la página
    if (priceField && yieldField && adjustedPriceField) {
        // Si ya hay valores, calcular precio ajustado
        if (priceField.value && yieldField.value) {
            calculateAdjustedPrice();
        }
    }
    
    // Código existente para el botón de calcular rendimiento
    const computeYieldBtn = document.getElementById('compute-yield');
    if (computeYieldBtn) {
        computeYieldBtn.addEventListener('click', function() {
            $('#modal-yield').modal('show');
        });
    }
    
    // Botón dentro del modal para calcular rendimiento
    const btnComputeYield = document.getElementById('btn-compute-yield');
    if (btnComputeYield) {
        btnComputeYield.addEventListener('click', function() {
            const initialQuantity = parseFloat(document.getElementById('initial-quantity').value);
            const finalQuantity = parseFloat(document.getElementById('final-quantity').value);
            
            if (!isNaN(initialQuantity) && !isNaN(finalQuantity) && initialQuantity > 0) {
                // Calcular factor de rendimiento como porcentaje
                const yieldFactor = (finalQuantity / initialQuantity) * 100;
                
                // Actualizar el campo y cerrar el modal
                document.getElementById('yield-result').textContent = yieldFactor.toFixed(2) + ' %';
                document.getElementById('ingredientstock-yield').value = yieldFactor.toFixed(2);
                
                // Recalcular precio ajustado
                calculateAdjustedPrice();
                
                // Opcional: Cerrar modal después de aceptar
                $('#modal-yield').modal('hide');
            }
        });
    }
});
</script>