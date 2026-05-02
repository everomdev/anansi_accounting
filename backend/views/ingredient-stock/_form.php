<style>
.ingredient-stock-form label .asterisk,
.ingredient-stock-form label .required {
    color: #dc3545 !important;
    font-weight: bold;
}

#unit-change-warning-modal .modal-body .alert { margin-bottom: 0; border: none; background-color: #fff3cd; }
#unit-change-warning-modal .modal-title { color: #f39c12; }
#unit-change-warning-modal .btn-warning { background-color: #f39c12; border-color: #e08e0b; }
#unit-change-warning-modal .btn-warning:hover { background-color: #e08e0b; border-color: #ca7a0b; }

#kitchen-unit-change-warning-modal .modal-body .alert { margin-bottom: 0; border: none; background-color: #fff3cd; }
#kitchen-unit-change-warning-modal .modal-title { color: #f39c12; }
#kitchen-unit-change-warning-modal .btn-warning { background-color: #f39c12; border-color: #e08e0b; }
#kitchen-unit-change-warning-modal .btn-warning:hover { background-color: #e08e0b; border-color: #ca7a0b; }

/* Checkbox al lado del texto del label */
.pending-checkbox {
    display: inline-block !important;
    position: static !important;
    transform: none !important;
    width: 15px;
    height: 15px;
    margin-left: 8px;
    vertical-align: middle;
    cursor: pointer;
    accent-color: #007bff;
    border: 2px solid #007bff !important;
    box-shadow: none !important;
}

/* Asegurar que el label quede en línea con el checkbox */
.pending-mode .pending-field-group > .field-ingredientstock-ingredient label,
.pending-mode .pending-field-group label {
    display: inline-flex !important;
    align-items: center !important;
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

$this->registerMetaTag(['name' => 'csrf-token', 'content' => Yii::$app->request->getCsrfToken()]);

$formatConfig = \common\helpers\NumberFormatter::getJsConfig();
$this->registerJsVar('userFormatConfig', $formatConfig);

$allUms = \common\models\UnitOfMeasurement::find()->where(['business_id' => $business['id']])->all();
$purchaseUms = array_filter($allUms, function($um) { return ($um->is_purchase ?? 0) == 1; });
$kitchenUms  = array_filter($allUms, function($um) { return ($um->is_kitchen  ?? 0) == 1; });

$providers = \yii\helpers\ArrayHelper::map(Provider::find()->where(['business_id' => $business['id']])->all(), 'id', 'name');
?>

<div class="ingredient-stock-form">
    <div class="d-flex justify-content-end mb-2">
        <button type="button" id="btn-toggle-pending" class="btn btn-warning">
            <i class="bi bi-exclamation-circle"></i> Pendientes
        </button>
    </div>
    <?php $form = ActiveForm::begin(['enableAjaxValidation' => true]); ?>

    <div class="card">
        <div class="card-body">
            <?= $form->field($model, 'final_quantity')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'quantity')->hiddenInput()->label(false) ?>

            <div class="row mb-3">
                <h5 class="card-title mb-3">Información del Insumo</h5>

                <div class="col-sm-12 col-md-6 col-lg-4 col-xl-4 mb-3 pending-field-group" data-field="ingredient">
                    <?= $form->field($model, 'ingredient', [
    'enableAjaxValidation' => false,  // <-- agregar esto
])->widget(\kartik\typeahead\Typeahead::class, [
                        'scrollable' => true,
                        'defaultSuggestions' => $autocompleteName,
                        'dataset' => [['local' => $autocompleteName, 'limit' => 10]],
                    ])->label('Nombre del Insumo <span class="asterisk">*</span>') ?>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-4 col-xl-4 mb-3 pending-field-group" data-field="category_id">
                    <?= $form->field($model, 'category_id')->dropDownList(
                        \yii\helpers\ArrayHelper::map(Category::all(), 'id', 'name'),
                        ['prompt' => '-- Seleccione una familia de insumos --', 'data-url' => Url::to(['ingredient-stock/generate-key'])]
                    )->label('Familia de insumos <span class="asterisk">*</span>') ?>
                </div>

                <div class="col-sm-12 col-md-4 col-lg-2 col-xl-2 mb-3 pending-field-group" data-field="key">
                    <?= $form->field($model, 'key')->textInput(['placeholder' => 'Ej: FRT-001'])->label('Clave <span class="asterisk">*</span>') ?>
                </div>

                <div class="col-sm-12 col-md-4 col-lg-2 col-xl-2 mb-3 pending-field-group" data-field="um">
                    <?= $form->field($model, 'um')->dropDownList(
                        \yii\helpers\ArrayHelper::map($purchaseUms, 'name', 'name'),
                        ['prompt' => '-- Seleccione --', 'id' => 'ingredientstock-um', 'data-original-value' => $model->um ?? '']
                    )->label('Unidad de Compra <span class="asterisk">*</span>') ?>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3 mb-3 pending-field-group" data-field="brand">
                    <?= $form->field($model, 'brand')->textInput(['placeholder' => 'Ej: Nestlé, Kirkland'])->label('Marca') ?>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3 mb-3 pending-field-group" data-field="presentation">
                    <?= $form->field($model, 'presentation')->textInput(['placeholder' => 'Ej: Bolsa 1kg, Botella 500ml'])->label('Presentación') ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3 mb-3 pending-field-group" data-field="portion_um">
                    <?= $form->field($model, 'portion_um')->dropDownList(
                        \yii\helpers\ArrayHelper::map($kitchenUms, 'name', 'name'),
                        ['prompt' => '-- Seleccione --', 'id' => 'ingredientstock-portion_um', 'data-original-value' => $model->portion_um ?? '']
                    )->label('Unidades de uso <span class="asterisk">*</span>') ?>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-3 mb-3 pending-field-group" data-field="portions_per_unit">
                    <?php
                    $portionField = $form->field($model, 'portions_per_unit')->widget(\kartik\typeahead\Typeahead::class, [
                        'scrollable' => true,
                        'defaultSuggestions' => $autocompletePortions,
                        'dataset' => [['local' => $autocompletePortions, 'limit' => 10]],
                    ])->label('Equivalencia a unidades de uso <span class="asterisk">*</span>');
                    echo preg_replace('/(<\/div>\s*)$/', '<div class="form-text" id="question-portion-um"></div>$1', $portionField);
                    ?>
                </div>
            </div>

            <div class="row mb-3">
                <h5 class="card-title mb-3">Precios y Rendimiento</h5>

                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3 workflow-step mb-3 pending-field-group" data-field="price">
                    <?= $form->field($model, 'price')->textInput([
                        'class' => 'form-control format-price', 'id' => 'ingredientstock-price', 'required' => true,
                        'placeholder' => formatPrice(500.00, 2, false), 'data-format' => 'price', 'data-decimals' => '2',
                        'value' => $model->price ? formatPrice($model->price, 2, false) : ''
                    ])->label("Precio de compra <span class='asterisk'>*</span>") ?>
                    <div class="form-text">Ingrese el precio de compra del insumo</div>
                </div>

                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3 workflow-step mb-3 pending-field-group" data-field="yield">
                    <?= $form->field($model, 'yield', [
                        'template' => "{label}<div class='input-group'>{input}<span class='input-group-text'>%</span><button type='button' class='btn btn-outline-primary' id='compute-yield'><i class='bi bi-calculator'></i> Calcular</button></div>{hint}{error}",
                        'inputOptions' => [
                            'class' => 'form-control format-percentage', 'id' => 'ingredientstock-yield', 'required' => true,
                            'placeholder' => '85', 'data-format' => 'percentage',
                            'value' => $model->yield ? formatNumber($model->yield, 0) : ''
                        ]
                    ])->textInput()->label("Factor de rendimiento <span class='asterisk'>*</span>") ?>
                    <div class="form-text">Entre 1% y 100%</div>
                </div>

                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3 workflow-step mb-3">
                    <?= $form->field($model, 'adjustedPrice', [
                        'template' => "{label}<div class='input-group'><span class='input-group-text'>" . \common\helpers\NumberFormatter::getFormatConfig()['currency_symbol'] . "</span>{input}</div>{hint}{error}",
                        'inputOptions' => [
                            'class' => 'form-control bg-light format-price', 'id' => 'ingredientstock-adjustedprice', 'readonly' => true,
                            'data-format' => 'price',
                            'value' => $model->adjustedPrice ? formatPrice($model->adjustedPrice, 2, false) : ''
                        ]
                    ])->textInput()->label("Precio ajustado") ?>
                </div>
            </div>

            <div class="row mb-3">
                <h5 class="card-title mb-3">Información Adicional</h5>

                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 mb-3 pending-field-group" data-field="providers">
                    <?= $form->field($model, 'providers')->widget(Select2::class, [
                        'data' => \yii\helpers\ArrayHelper::map(
                            Provider::find()->where(['business_id' => $business['id']])->all(), 'id',
                            function($p) { return $p->business_name ?? $p->getBusiness()->one()->name ?? $p->name; }
                        ),
                        'options' => ['placeholder' => 'Selecciona proveedores...', 'multiple' => true],
                        'pluginOptions' => ['maximumSelectionLength' => 5, 'allowClear' => true],
                    ])->label("Proveedores") ?>
                </div>

                <div class="col-12 mb-3 pending-field-group" data-field="observations">
                    <?= $form->field($model, 'observations')->textarea([
                        'rows' => 4, 'placeholder' => 'Añada aquí cualquier nota relevante sobre el insumo...'
                    ])->label('Observaciones') ?>
                </div>
            </div>

            <div class="row mb-3">
                <h5 class="card-title mb-3">Control de Inventario</h5>

                <div class="col-sm-12 col-md-6 col-lg-4 col-xl-4 mb-3 pending-field-group" data-field="min_stock">
                    <?= $form->field($model, 'min_stock')->textInput([
                        'class' => 'form-control format-number', 'id' => 'ingredientstock-min_stock',
                        'placeholder' => formatNumber(10, 2, false), 'data-format' => 'number', 'data-decimals' => '2',
                        'value' => $model->min_stock ? formatNumber($model->min_stock, 2, false) : ''
                    ])->label("Stock Mínimo") ?>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-4 col-xl-4 mb-3 pending-field-group" data-field="max_stock">
                    <?= $form->field($model, 'max_stock')->textInput([
                        'class' => 'form-control format-number', 'id' => 'ingredientstock-max_stock',
                        'placeholder' => formatNumber(100, 2, false), 'data-format' => 'number', 'data-decimals' => '2',
                        'value' => $model->max_stock ? formatNumber($model->max_stock, 2, false) : ''
                    ])->label("Stock Máximo") ?>
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

    <input type="hidden" name="pending_fields" id="pending-fields-input" value="">
    <?php ActiveForm::end(); ?>
</div>

<?php
\yii\bootstrap5\Modal::begin(['id' => 'modal-yield']);
echo \yii\bootstrap5\Html::label(Yii::t('app', 'Initial Quantity'), 'initial-quantity');
echo \yii\bootstrap5\Html::input('number', 'initial-quantity', '', ['class' => 'form-control', 'id' => 'initial-quantity']);
echo \yii\bootstrap5\Html::label(Yii::t('app', 'Final Quantity'), 'final-quantity');
echo \yii\bootstrap5\Html::input('number', 'final-quantity', '', ['class' => 'form-control', 'id' => 'final-quantity']);
echo \yii\bootstrap5\Html::label(Yii::t('app', 'Factor de rendimiento'), 'yield-result');
echo "<span class='form-control' id='yield-result'>0 %</span>";
echo \yii\bootstrap5\Html::button(Yii::t('app', 'Aceptar'), ['class' => 'btn btn-outline-primary mt-3', 'id' => 'btn-compute-yield']);
\yii\bootstrap5\Modal::end();
?>

<?php \yii\bootstrap5\Modal::begin([
    'id' => 'unit-change-warning-modal',
    'title' => '<i class="fas fa-exclamation-triangle text-warning"></i> Advertencia de cambio de unidad',
    'size' => \yii\bootstrap5\Modal::SIZE_DEFAULT,
    'options' => ['data-bs-backdrop' => 'static', 'data-bs-keyboard' => 'false']
]); ?>
<div class="modal-body">
    <div class="alert alert-warning d-flex align-items-center" role="alert">
        <div>
            <strong>⚠️ Cambiar la unidad de compra de este insumo modificará todas las subrecetas y recetas que lo utilizan.</strong>
            <br><br>Esta acción puede generar inconsistencias en costos e inventarios.<br><br>
            <strong>¿Desea continuar?</strong>
        </div>
    </div>
</div>
<div class="modal-footer">
    <?= Html::button('Cancelar', ['class' => 'btn btn-warning', 'id' => 'cancel-unit-change', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::button('Aceptar y cambiar la unidad', ['class' => 'btn btn-secondary', 'id' => 'confirm-unit-change']) ?>
</div>
<?php \yii\bootstrap5\Modal::end(); ?>

<?php \yii\bootstrap5\Modal::begin([
    'id' => 'kitchen-unit-change-warning-modal',
    'title' => '<i class="fas fa-exclamation-triangle text-warning"></i> Advertencia de cambio de unidad de uso',
    'size' => \yii\bootstrap5\Modal::SIZE_DEFAULT,
    'options' => ['data-bs-backdrop' => 'static', 'data-bs-keyboard' => 'false']
]); ?>
<div class="modal-body">
    <div class="alert alert-warning d-flex align-items-center" role="alert">
        <div>
            <strong>⚠️ Cambiar la unidad de uso de este insumo modificará todas las subrecetas y recetas que lo utilizan.</strong>
            <br><br>Esta acción puede generar inconsistencias en las equivalencias y proporciones de las recetas.<br><br>
            <strong>¿Desea continuar?</strong>
        </div>
    </div>
</div>
<div class="modal-footer">
    <?= Html::button('Cancelar', ['class' => 'btn btn-warning', 'id' => 'cancel-kitchen-unit-change', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::button('Aceptar y cambiar la unidad', ['class' => 'btn btn-secondary', 'id' => 'confirm-kitchen-unit-change']) ?>
</div>
<?php \yii\bootstrap5\Modal::end(); ?>

<script>
(function() {
    let originalUmValue = '', pendingUmChange = '', isExistingIngredient = false;
    let originalKitchenUmValue = '', pendingKitchenUmChange = '';

    document.addEventListener('DOMContentLoaded', function() {
        const umField = document.getElementById('ingredientstock-um');
        const ingredientId = <?= $model->isNewRecord ? 'null' : $model->id ?>;

        if (umField) {
            originalUmValue = umField.getAttribute('data-original-value') || '';
            isExistingIngredient = ingredientId !== null;
            if (isExistingIngredient) {
                umField.addEventListener('change', function(e) {
                    const nv = e.target.value;
                    if (originalUmValue && nv && nv !== originalUmValue)
                        checkIngredientUsage(ingredientId, nv, e.target, 'purchase');
                });
            }
        }

        const kitchenUmField = document.getElementById('ingredientstock-portion_um');
        if (kitchenUmField) {
            originalKitchenUmValue = kitchenUmField.getAttribute('data-original-value') || '';
            if (isExistingIngredient) {
                kitchenUmField.addEventListener('change', function(e) {
                    const nv = e.target.value;
                    if (originalKitchenUmValue && nv && nv !== originalKitchenUmValue)
                        checkIngredientUsage(ingredientId, nv, e.target, 'kitchen');
                });
            }
        }

        document.getElementById('cancel-unit-change')?.addEventListener('click', function() {
            const f = document.getElementById('ingredientstock-um');
            if (f) { f.value = originalUmValue; pendingUmChange = ''; }
        });
        document.getElementById('confirm-unit-change')?.addEventListener('click', function() {
            const f = document.getElementById('ingredientstock-um');
            if (f && pendingUmChange) { f.value = pendingUmChange; originalUmValue = pendingUmChange; pendingUmChange = ''; }
            bootstrap.Modal.getInstance(document.getElementById('unit-change-warning-modal'))?.hide();
        });
        document.getElementById('cancel-kitchen-unit-change')?.addEventListener('click', function() {
            const f = document.getElementById('ingredientstock-portion_um');
            if (f) { f.value = originalKitchenUmValue; pendingKitchenUmChange = ''; }
        });
        document.getElementById('confirm-kitchen-unit-change')?.addEventListener('click', function() {
            const f = document.getElementById('ingredientstock-portion_um');
            if (f && pendingKitchenUmChange) { f.value = pendingKitchenUmChange; originalKitchenUmValue = pendingKitchenUmChange; pendingKitchenUmChange = ''; }
            bootstrap.Modal.getInstance(document.getElementById('kitchen-unit-change-warning-modal'))?.hide();
        });
    });

    function checkIngredientUsage(ingredientId, newUmValue, selectElement, unitType) {
        fetch('<?= \yii\helpers\Url::to(['ingredient-stock/check-usage']) ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
            body: JSON.stringify({ ingredientId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.isUsed) {
                if (unitType === 'purchase') showUnitChangeWarning(newUmValue, selectElement, data);
                else showKitchenUnitChangeWarning(newUmValue, selectElement, data);
            } else {
                if (unitType === 'purchase') originalUmValue = newUmValue;
                else originalKitchenUmValue = newUmValue;
            }
        })
        .catch(() => {
            if (unitType === 'purchase') originalUmValue = newUmValue;
            else originalKitchenUmValue = newUmValue;
        });
    }

    function showUnitChangeWarning(newUmValue, selectElement, usageData) {
        updateModalMessage('unit-change-warning-modal', usageData);
        pendingUmChange = newUmValue;
        selectElement.value = originalUmValue;
        new bootstrap.Modal(document.getElementById('unit-change-warning-modal')).show();
    }

    function showKitchenUnitChangeWarning(newUmValue, selectElement, usageData) {
        updateModalMessage('kitchen-unit-change-warning-modal', usageData);
        pendingKitchenUmChange = newUmValue;
        selectElement.value = originalKitchenUmValue;
        new bootstrap.Modal(document.getElementById('kitchen-unit-change-warning-modal')).show();
    }

    function updateModalMessage(modalId, usageData) {
        const modal = document.getElementById(modalId);
        const alertDiv = modal?.querySelector('.alert div');
        if (!alertDiv) return;
        const details = [];
        if (usageData.recipesCount > 0) details.push(`${usageData.recipesCount} receta${usageData.recipesCount > 1 ? 's' : ''}`);
        if (usageData.subRecipesCount > 0) details.push(`${usageData.subRecipesCount} subreceta${usageData.subRecipesCount > 1 ? 's' : ''}`);
        const tipo = modalId.includes('kitchen') ? 'unidad de uso' : 'unidad de compra';
        alertDiv.innerHTML = `<strong>⚠️ Cambiar la ${tipo} de este insumo modificará ${details.join(' y ')} que lo utilizan.</strong><br><br>Esta acción puede generar inconsistencias en costos e inventarios.<br><br><strong>¿Desea continuar?</strong>`;
    }
})();

// Pregunta dinámica de equivalencia
(function waitForJQuery() {
    if (typeof window.jQuery === 'undefined') { setTimeout(waitForJQuery, 50); return; }
    var $ = window.jQuery;
    function updatePortionUmQuestion() {
        var portionUm = $('#ingredientstock-portion_um').val() || 'unidad de uso';
        var um = $('#ingredientstock-um').val() || 'unidad de compra';
        $('#question-portion-um').html(`¿Cuántos <b>${portionUm}</b> hay en un <b>${um}</b>?`);
    }
    $(function() {
        updatePortionUmQuestion();
        $('#ingredientstock-portion_um, #ingredientstock-um').on('change select2:select', updatePortionUmQuestion);
    });
})();

// Formateo de números
document.addEventListener('DOMContentLoaded', function() {
    if (window.BusinessNumberFormatter) window.BusinessNumberFormatter.setupAutoFormatInputs();

    const priceField = document.getElementById('ingredientstock-price');
    const yieldField = document.getElementById('ingredientstock-yield');
    const adjustedPriceField = document.getElementById('ingredientstock-adjustedprice');
    const fc = window.userFormatConfig || { decimalSeparator: '.', thousandSeparator: ',', currencySymbol: '$' };

    function fmt(input, force = false) {
        const ds = fc.decimalSeparator, ts = fc.thousandSeparator;
        const cur = input.selectionStart;
        let v = input.value.replace(new RegExp(`[^0-9\\${ds}]`, 'g'), '');
        const dc = (v.match(new RegExp(`\\${ds}`, 'g')) || []).length;
        if (dc > 1) { const p = v.split(ds); v = p[0] + ds + p.slice(1).join(''); }
        if (!force && document.activeElement === input) {
            const p = v.split(ds);
            if (p.length > 1) { p[1] = p[1].slice(0, 2); v = p.join(ds); }
            input.value = v;
            input.setAttribute('data-raw-value', v.replace(ds, '.'));
            setTimeout(() => input.setSelectionRange(cur, cur), 0);
            return;
        }
        let p = v.split(ds);
        let whole = p[0].replace(/\D/g, '') || '0';
        let dec = p.length > 1 ? p[1].replace(/\D/g, '').slice(0, 2) : '';
        if (force && dec === '') dec = '00'; else if (dec.length === 1) dec += '0';
        if (whole.length > 3) whole = whole.replace(/\B(?=(\d{3})+(?!\d))/g, ts);
        const fv = whole + ds + dec;
        input.value = fv;
        input.setAttribute('data-raw-value', fv.replace(new RegExp(`\\${ts}`, 'g'), '').replace(ds, '.'));
    }

    if (priceField) {
        priceField.addEventListener('focus', function() { this.value = (this.getAttribute('data-raw-value') || '').replace('.', fc.decimalSeparator); });
        priceField.addEventListener('blur', function() {
            const self = this; this.value = this.getAttribute('data-raw-value') || '';
            setTimeout(function() { fmt(self, true); setTimeout(calcAdjusted, 10); }, 100);
        });
        priceField.addEventListener('keydown', function(e) {
            const ok = ['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Home','End','Enter'];
            if (ok.includes(e.key)) return;
            if (!/^[0-9]$/.test(e.key) && e.key !== fc.decimalSeparator) e.preventDefault();
        });
        priceField.addEventListener('input', function() { fmt(this, false); setTimeout(calcAdjusted, 10); });
    }

    function calcAdjusted() {
        if (!priceField || !yieldField || !adjustedPriceField) return;
        const pf = document.getElementById('ingredientstock-portions_per_unit');
        let price = parseFloat(priceField.getAttribute('data-raw-value')) || 0;
        let yv = parseFloat(yieldField.value);
        let portions = parseFloat(pf ? pf.value : 1) || 1;
        if (!isNaN(price) && !isNaN(yv) && yv > 0 && price > 0 && portions > 0) {
            const adj = (price / portions) / (yv / 100);
            const tmp = document.createElement('input');
            tmp.value = adj.toString(); tmp.setAttribute('data-raw-value', adj.toString());
            fmt(tmp, true);
            adjustedPriceField.value = tmp.value;
            adjustedPriceField.setAttribute('data-raw-value', adj);
        } else { adjustedPriceField.value = ''; adjustedPriceField.removeAttribute('data-raw-value'); }
    }

    function validateYield() {
        if (!yieldField) return;
        let v = parseFloat(yieldField.value);
        if (isNaN(v)) return;
        if (v < 1) yieldField.value = 1; else if (v > 100) yieldField.value = 100;
        calcAdjusted();
    }
    if (yieldField) { yieldField.addEventListener('input', validateYield); yieldField.addEventListener('change', validateYield); }

    const pf2 = document.getElementById('ingredientstock-portions_per_unit');
    if (pf2) { pf2.addEventListener('input', () => setTimeout(calcAdjusted, 10)); pf2.addEventListener('change', () => setTimeout(calcAdjusted, 10)); }

    if (priceField?.value) fmt(priceField, true);
    if (priceField?.value && yieldField?.value) setTimeout(calcAdjusted, 100);

    document.querySelector('form')?.addEventListener('submit', function() {
        if (priceField?.getAttribute('data-raw-value')) priceField.value = priceField.getAttribute('data-raw-value');
        if (adjustedPriceField?.getAttribute('data-raw-value')) adjustedPriceField.value = adjustedPriceField.getAttribute('data-raw-value');
    });
});

window.userFormatConfig = window.userFormatConfig || { decimalSeparator: '.', thousandSeparator: ',', currencySymbol: '$', decimalPlaces: 2 };
</script>

<?php
$pendingFields = isset($model) && method_exists($model, 'getPendingFields') ? $model->getPendingFields() : [];
$pendingFieldsJson = json_encode($pendingFields);
$js = <<<JS
(function(\$) {
    var pendingMode = false;
    var pendingFields = $pendingFieldsJson;

    function updatePendingCheckboxes() {
        \$('.pending-field-group').each(function() {
            var field = \$(this).data('field');
            var \$existing = \$(this).find('.pending-checkbox');

            if (pendingMode) {
                if (\$existing.length === 0) {
                    var checked = pendingFields && pendingFields.includes(field) ? 'checked' : '';
                    var \$label = \$(this).find('label').first();
                    var cbHtml = ' <input type="checkbox" class="form-check-input pending-checkbox" data-field="' + field + '" ' + checked + ' title="Marcar como pendiente">';
                    if (\$label.length) {
                        \$label.append(cbHtml);
                    } else {
                        \$(this).prepend(cbHtml);
                    }
                }
            } else {
                \$existing.remove();
            }
        });
    }

    \$('#btn-toggle-pending').on('click', function() {
        pendingMode = !pendingMode;
        \$('.ingredient-stock-form').toggleClass('pending-mode', pendingMode);
        updatePendingCheckboxes();
    });

    \$('.ingredient-stock-form form').on('submit', function() {
        var fields = [];
        \$('.pending-checkbox:checked').each(function() {
            fields.push(\$(this).data('field'));
        });
        \$('#pending-fields-input').val(JSON.stringify(fields));
        // pendingMode = false;
        // \$('.ingredient-stock-form').removeClass('pending-mode');
        // updatePendingCheckboxes();
    });

    if (pendingFields && pendingFields.length > 0) {
        \$('#btn-toggle-pending').addClass('btn-danger').removeClass('btn-warning');
    }
})(jQuery);
JS;
$this->registerJs($js);
?>
