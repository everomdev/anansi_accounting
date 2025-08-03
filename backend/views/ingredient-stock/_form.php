
<style>
/* Solo el asterisco de campos obligatorios en rojo */
.ingredient-stock-form label .asterisk,
.ingredient-stock-form label .required {
    color: #dc3545 !important;
    font-weight: bold;
}
</style>
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

// Use global number formatter configuration
$formatConfig = \common\helpers\NumberFormatter::getJsConfig();
$this->registerJsVar('userFormatConfig', $formatConfig);
$ums = \common\models\UnitOfMeasurement::findAll(['business_id' => $business['id']]);
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
                    ])->label('Nombre del Insumo <span class="asterisk">*</span>') ?>
                </div>
                
                <div class="col-sm-12 col-md-6 col-lg-4 col-xl-4 mb-3">
                    <?= $form->field($model, 'category_id')->dropDownList(
                        \yii\helpers\ArrayHelper::map(Category::all(), 'id', 'name'), 
                        ['prompt' => '-- Seleccione una categoría --', 'data-url' => Url::to(['ingredient-stock/generate-key'])]
                    )->label('Categoría <span class="asterisk">*</span>') ?>
                </div>
                
                <div class="col-sm-12 col-md-4 col-lg-2 col-xl-2 mb-3">
                    <?= $form->field($model, 'key')->textInput(['placeholder' => 'Ej: FRT-001'])->label('Clave <span class="asterisk">*</span>') ?>
                </div>
                
                <div class="col-sm-12 col-md-4 col-lg-2 col-xl-2 mb-3">
                    <?= $form->field($model, 'um')->dropDownList(
                        \yii\helpers\ArrayHelper::map($ums, 'name', 'name'),
                        ['prompt' => '-- Seleccione --']
                    )->label('Unidad de Compra <span class="asterisk">*</span>') ?>
                </div>
                
                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3 mb-3">
                    <?= $form->field($model, 'brand')->textInput([
                        'placeholder' => 'Ej: Nestlé, Kirkland'
                    ])->label('Marca') ?>
                </div>
                
                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3 mb-3">
                    <?= $form->field($model, 'presentation')->textInput([
                        'placeholder' => 'Ej: Bolsa 1kg, Botella 500ml'
                    ])->label('Presentación') ?>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3 mb-3">
                    <?= $form->field($model, 'portion_um')->dropDownList(
                        \yii\helpers\ArrayHelper::map($ums, 'name', 'name'),
                        ['prompt' => '-- Seleccione --']
                    )->label('Unidades de cocina <span class="asterisk">*</span>') ?>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3 mb-3">
                    <?php
                    $portionField = $form->field($model, 'portions_per_unit')->widget(\kartik\typeahead\Typeahead::class, [
                        'scrollable' => true,
                        'defaultSuggestions' => $autocompletePortions,
                        'dataset' => [
                            [
                                'local' => $autocompletePortions,
                                'limit' => 10,
                            ]
                        ],
                    ])->label('Equivalencia a unidades de cocina <span class="asterisk">*</span>');
                    echo preg_replace('/(<\/div>\s*)$/', '<div class="form-text" id="question-portion-um"></div>$1', $portionField);
                    ?>
                </div>
                
            </div>
            
            <div class="row mb-3">
                <h5 class="card-title mb-3">Precios y Rendimiento</h5>                
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3 workflow-step mb-3">                    
                    <?= $form->field($model, 'price')->textInput([
                        'class' => 'form-control format-price',
                        'id' => 'ingredientstock-price',
                        'required' => true,
                        'placeholder' => formatPrice(500.00, 2, false),
                        'data-format' => 'price',
                        'data-decimals' => '2',
                        'value' => $model->price ? formatPrice($model->price, 2, false) : ''
                    ])->label("Precio de compra <span class='asterisk'>*</span>") ?>
                    <div class="form-text">Ingrese el precio de compra del insumo</div>
                </div>
                  <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3 workflow-step mb-3">
                    <?= $form->field($model, 'yield', [
                        'template' => "{label}<div class='input-group'>{input}<span class='input-group-text'>%</span><button type='button' class='btn btn-outline-primary' id='compute-yield'><i class='bi bi-calculator'></i> Calcular</button></div>{hint}{error}",
                        'inputOptions' => [
                            'class' => 'form-control format-percentage', 
                            'id' => 'ingredientstock-yield', 
                            'required' => true, 
                            'placeholder' => '85',
                            'data-format' => 'percentage',
                            'value' => $model->yield ? formatNumber($model->yield, 0) : ''
                        ]
                    ])->textInput()->label("Factor de rendimiento <span class='asterisk'>*</span>") ?>
                    <div class="form-text">Entre 1% y 100%</div>
                </div>
                  <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3 workflow-step mb-3">
                    <?= $form->field($model, 'adjustedPrice', [
                        'template' => "{label}<div class='input-group'><span class='input-group-text'>" . \common\helpers\NumberFormatter::getFormatConfig()['currency_symbol'] . "</span>{input}</div>{hint}{error}",
                        'inputOptions' => [
                            'class' => 'form-control bg-light format-price', 
                            'id' => 'ingredientstock-adjustedprice', 
                            'readonly' => true,
                            'data-format' => 'price',
                            'value' => $model->adjustedPrice ? formatPrice($model->adjustedPrice, 2, false) : ''
                        ]
                    ])->textInput()->label("Precio ajustado") ?>
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
// Pregunta dinámica de equivalencia de unidades
// Esperar a que jQuery esté disponible antes de ejecutar el script
(function waitForJQuery() {
    if (typeof window.jQuery === 'undefined') {
        setTimeout(waitForJQuery, 50);
        return;
    }
    var $ = window.jQuery;
    function updatePortionUmQuestion() {
        var portionUm = $('#ingredientstock-portion_um').val();
        var um = $('#ingredientstock-um').val();
        if (!portionUm || portionUm === '') portionUm = 'unidad de cocina';
        if (!um || um === '') um = 'unidad de compra';
        var pregunta = `¿Cuántos <b>${portionUm}</b> hay en un <b>${um}</b>?`;
        $('#question-portion-um').html(pregunta);
    }
    $(function() {
        updatePortionUmQuestion();
        $('#ingredientstock-portion_um, #ingredientstock-um').on('change', updatePortionUmQuestion);
        // Si usan select2, escuchar el evento select2:select
        $('#ingredientstock-portion_um, #ingredientstock-um').on('select2:select', updatePortionUmQuestion);
    });
})();
// Funcionalidad adicional para el formateo de números
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el formateador automático para todos los campos
    if (window.BusinessNumberFormatter) {
        window.BusinessNumberFormatter.setupAutoFormatInputs();
    }
    
    // Referencias a los campos
    const priceField = document.getElementById('ingredientstock-price');
    const yieldField = document.getElementById('ingredientstock-yield');
    const adjustedPriceField = document.getElementById('ingredientstock-adjustedprice');
    
    // Configuración de formato (desde el sistema global o default)
    const formatConfig = window.userFormatConfig || window.businessFormatConfig || {
        decimalSeparator: '.',
        thousandSeparator: ',',
        currencySymbol: '$'
    };
    
    // Función para validar y formatear el precio según la configuración del usuario
    function validateAndFormatPrice(input, forceFormat = false) {
        const decimalSeparator = formatConfig.decimalSeparator;
        const thousandSeparator = formatConfig.thousandSeparator;

        // Guardar posición del cursor
        const cursorPosition = input.selectionStart;

        // Obtener valor
        let value = input.value;

        // Eliminar caracteres no válidos (excepto el separador decimal configurado)
        const validChars = new RegExp(`[^0-9\\${decimalSeparator}]`, 'g');
        value = value.replace(validChars, '');

        // Reemplazar múltiples separadores decimales por uno solo
        const decimalCount = (value.match(new RegExp(`\\${decimalSeparator}`, 'g')) || []).length;
        if (decimalCount > 1) {
            const parts = value.split(decimalSeparator);
            value = parts[0] + decimalSeparator + parts.slice(1).join('');
        }

        // Si está escribiendo, no formatear aún (excepto para limitar decimales)
        if (!forceFormat && document.activeElement === input) {
            // Limitar a 2 decimales si ya hay separador
            const parts = value.split(decimalSeparator);
            if (parts.length > 1) {
                parts[1] = parts[1].slice(0, 2);
                value = parts.join(decimalSeparator);
            }
            input.value = value;
            input.setAttribute('data-raw-value', value.replace(decimalSeparator, '.'));

            // Restaurar posición del cursor
            setTimeout(() => {
                input.setSelectionRange(cursorPosition, cursorPosition);
            }, 0);
            return;
        }

        // Formato completo al perder el foco
        let parts = value.split(decimalSeparator);
        let wholePart = parts[0].replace(/\D/g, '') || '0'; // Solo dígitos
        let decimalPart = parts.length > 1 ? parts[1].replace(/\D/g, '').slice(0, 2) : '00';

        // Agregar separadores de miles solo al final
        if (wholePart.length > 3) {
            wholePart = wholePart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);
        }

        // Construir valor formateado
        let formattedValue = wholePart;
        if (decimalPart.length > 0) {
            formattedValue += decimalSeparator + decimalPart;
        } else if (forceFormat) {
            formattedValue += decimalSeparator + '00';
        }

        // Actualizar campo
        input.value = formattedValue;
        input.setAttribute('data-raw-value',
            formattedValue.replace(new RegExp(`\\${thousandSeparator}`, 'g'), '')
            .replace(decimalSeparator, '.')
        );
    }
      // Configurar eventos para el campo de precio
    if (priceField) {
        priceField.addEventListener('focus', function() {
            const rawValue = this.getAttribute('data-raw-value') || '';
            this.value = rawValue.replace('.', formatConfig.decimalSeparator);
        });

        priceField.addEventListener('blur', function() {
            const self = this;
            
            // Primero actualizar con valor raw para validación AJAX
            const rawValue = this.getAttribute('data-raw-value') || '';
            this.value = rawValue;
            
            // Esperar a que termine la validación AJAX y luego formatear
            setTimeout(function() {
                validateAndFormatPrice(self, true);
                setTimeout(calculateAdjustedPrice, 10);
            }, 100);
        });

        priceField.addEventListener('keydown', function(e) {
            const decimalSeparator = formatConfig.decimalSeparator;
            const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End', 'Enter'];

            if (allowedKeys.includes(e.key)) return;

            if (!/^[0-9]$/.test(e.key) && e.key !== decimalSeparator) {
                e.preventDefault();
            }
        });

        priceField.addEventListener('input', function() {
            validateAndFormatPrice(this, false);
            setTimeout(calculateAdjustedPrice, 10);
        });
    }
    
    // Función para calcular el precio ajustado
    function calculateAdjustedPrice() {
        if (!priceField || !yieldField || !adjustedPriceField) return;
        
        // Obtener referencias a los campos necesarios
        const portionsField = document.getElementById('ingredientstock-portions_per_unit');
        
        // Usar el valor raw guardado del precio
        let price = parseFloat(priceField.getAttribute('data-raw-value')) || 0;
        let yieldValue = parseFloat(yieldField.value);
        let portionsPerUnit = parseFloat(portionsField ? portionsField.value : 1) || 1;
        
        if (!isNaN(price) && !isNaN(yieldValue) && !isNaN(portionsPerUnit) && yieldValue > 0 && price > 0 && portionsPerUnit > 0) {
            // Convertir porcentaje a decimal (100% = 1.0)
            yieldValue = yieldValue / 100;
            
            // Nueva fórmula: (Precio ÷ Equivalencias) ÷ Factor de rendimiento
            const pricePerPortion = price / portionsPerUnit;
            const adjustedPrice = pricePerPortion / yieldValue;
            
            // Crear un input temporal para formatear el resultado
            const tempInput = document.createElement('input');
            tempInput.value = adjustedPrice.toString();
            tempInput.setAttribute('data-raw-value', adjustedPrice.toString());
            validateAndFormatPrice(tempInput, true);
            
            adjustedPriceField.value = tempInput.value;
            adjustedPriceField.setAttribute('data-raw-value', adjustedPrice);
        } else {
            adjustedPriceField.value = '';
            adjustedPriceField.removeAttribute('data-raw-value');
        }
    }
    
    // Validar que el factor de rendimiento esté entre 1 y 100
    function validateYield() {
        if (!yieldField) return;
        
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
    
    if (yieldField) {
        yieldField.addEventListener('input', validateYield);
        yieldField.addEventListener('change', validateYield);
    }
    
    // Agregar evento para recalcular cuando cambien las equivalencias
    const portionsField = document.getElementById('ingredientstock-portions_per_unit');
    if (portionsField) {
        portionsField.addEventListener('input', function() {
            setTimeout(calculateAdjustedPrice, 10);
        });
        portionsField.addEventListener('change', function() {
            setTimeout(calculateAdjustedPrice, 10);
        });
    }
    
    // Verificar al cargar la página
    if (priceField && yieldField && adjustedPriceField) {
        // Si ya hay valores, formatear y calcular precio ajustado
        if (priceField.value) {
            // Formatear valor inicial
            validateAndFormatPrice(priceField, true);
        }
          if (priceField.value && yieldField.value) {
            setTimeout(calculateAdjustedPrice, 100);
        }
    }
    
    // Asegurar que se envíen los valores sin formatear al servidor
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Convertir valores formateados a números antes del envío
            if (priceField && priceField.getAttribute('data-raw-value')) {
                priceField.value = priceField.getAttribute('data-raw-value');
            }
            if (adjustedPriceField && adjustedPriceField.getAttribute('data-raw-value')) {
                adjustedPriceField.value = adjustedPriceField.getAttribute('data-raw-value');
            }
        });
    }
});

// Configuración por defecto
window.userFormatConfig = window.userFormatConfig || {
    decimalSeparator: '.',
    thousandSeparator: ',',
    currencySymbol: '$',
    decimalPlaces: 2
};
</script>