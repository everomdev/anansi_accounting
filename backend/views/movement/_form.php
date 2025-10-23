<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Movement */
/* @var $form yii\widgets\ActiveForm */


$this->registerJsVar('movementTypeInput', \common\models\Movement::TYPE_INPUT);
$this->registerJsVar('movementTypeOutput', \common\models\Movement::TYPE_OUTPUT);
$this->registerJsVar('movementTypeOrder', \common\models\Movement::TYPE_ORDER);
$this->registerJsVar('getProviderPaymentTypesUrl', \yii\helpers\Url::to(['movement/get-provider-payment-types']));

$this->registerJsFile(Yii::getAlias("@web/js/movement/form.js"), [
    'depends' => \yii\web\YiiAsset::class,
    ['position' => $this::POS_END]
]);

$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']]);
$currencySymbol = \Symfony\Component\Intl\Currencies::getSymbol(strtoupper($business->currency_code));
$stock = (new \yii\db\Query())
    ->select([
        "ingredient_stock.*",
        "CONCAT(
            COALESCE(ingredient_stock.ingredient, ''),
            CASE 
                WHEN ingredient_stock.brand IS NOT NULL AND TRIM(ingredient_stock.brand) != '' 
                THEN CONCAT('  ', ingredient_stock.brand) 
                ELSE '' 
            END,
            CASE 
                WHEN ingredient_stock.presentation IS NOT NULL AND TRIM(ingredient_stock.presentation) != '' 
                THEN CONCAT('  ', ingredient_stock.presentation) 
                ELSE '' 
            END,
            CASE 
            WHEN " . ($model->type == \common\models\Movement::TYPE_OUTPUT ? "ingredient_stock.portion_um" : "ingredient_stock.um") . " IS NOT NULL 
                 AND TRIM(" . ($model->type == \common\models\Movement::TYPE_OUTPUT ? "ingredient_stock.portion_um" : "ingredient_stock.um") . ") != '' 
            THEN CONCAT(' (', " . ($model->type == \common\models\Movement::TYPE_OUTPUT ? "ingredient_stock.portion_um" : "ingredient_stock.um") . ", ')') 
            ELSE '' 
            END
        ) as label"
    ])
    ->from('ingredient_stock')
    ->where(['business_id' => $business->id])
    ->orderBy('ingredient_stock.ingredient ASC')
    ->all();
$providerNames = \common\models\Provider::find()
    ->select(['business_name'])
    ->where(['business_id' => $businessData['id']])
    ->asArray(true)
    ->all();
$providerNames = array_values(
    array_unique(
        \yii\helpers\ArrayHelper::getColumn($providerNames, 'business_name')
    )
);
?>

<div class="movement-form">

    <?php $form = ActiveForm::begin([
        'id' => 'movement-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => true
    ]); ?>
    <div class="card">
        <div class="card-header">
            <?= \yii\bootstrap5\Html::a(
                Yii::t('app', "Can't find the input? add it"),
                \yii\helpers\Url::to(['ingredient-stock/create', 'returnUrl' => Yii::$app->request->url]),
                [
                    'class' => 'btn btn-sm btn-success'
                ]
            ) ?>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- Primera fila: Insumo y Fecha -->
                <div class="col-sm-12 col-md-8 col-lg-8 col-xl-8">
                    <?= $form->field($model, 'ingredient_id')->widget(\kartik\select2\Select2::class, [
                        'data' => \yii\helpers\ArrayHelper::map($stock, 'id', 'label'),
                        'options' => [
                            'data-setting' => 'all',
                        ],
                        'pluginOptions' => [
                            'width' => '60%',
                        ]
                    ]) ?>
                </div>

                <!-- Campo para seleccionar fecha de creación -->
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                    <?= $form->field($model, 'created_at')->widget(\kartik\date\DatePicker::class, [
                        'options' => [
                            'placeholder' => 'Seleccionar fecha...',
                            'data-setting' => 'all'
                        ],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd',
                            'todayHighlight' => true,
                            'orientation' => 'bottom left'
                        ]
                    ])->label('Fecha del movimiento') ?>
                </div>

                <!-- Segunda fila: Proveedor/Centro de Consumo, Tipo de pago y Cantidad -->
                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                    <?php if ($model->type == \common\models\Movement::TYPE_OUTPUT): ?>
                        <?= $form->field($model, 'consumption_center_id')->dropDownList(
                            \yii\helpers\ArrayHelper::map(
                                \common\models\ConsumptionCenter::find()->where(['business_id' => $businessData['id']])->andWhere(['!=', 'name', 'Almacén'])->all(), 
                                'id', 
                                'name'
                            ), 
                            [
                                'prompt' => 'Seleccionar centro de consumo',
                                'class' => 'form-control'
                            ]
                        )->label(Yii::t('app', "Centro de Consumo")) ?>
                    <?php else: ?>
                        <?php 
                            // Obtener todos los proveedores del negocio
                            // IMPORTANTE: Desde esta refactorización, el campo 'provider' almacena el business_name
                            // ya que es obligatorio, mientras que 'name' es opcional y puede estar vacío
                            $providers = \common\models\Provider::find()->where(['business_id' => $businessData['id']])->all();
                            $providerOptions = [];
                            $providerIdMapping = [];
                            $keyToBusinessNameMapping = [];
                            $selectedKey = null;
                              foreach ($providers as $provider) {
                                // Usar business_name como display y como valor de referencia
                                $displayName = $provider->business_name;
                                $businessName = $provider->business_name;
                                
                                // Crear clave única usando business_name + id para evitar duplicados
                                $uniqueKey = $businessName . '_' . $provider->id;
                                $providerOptions[$uniqueKey] = $displayName;
                                $providerIdMapping[$uniqueKey] = $provider->id;
                                $keyToBusinessNameMapping[$uniqueKey] = $businessName;
                                  // Si estamos editando, encontrar la clave que corresponde al proveedor actual
                                // Buscar por business_name ya que es lo que se debe guardar (desde la refactorización)
                                // También buscar por name para compatibilidad hacia atrás
                                if (!empty($model->provider)) {
                                    if ($model->provider === $businessName) {
                                        $selectedKey = $uniqueKey;
                                    } elseif ($model->provider === $provider->name) {
                                        // Compatibilidad hacia atrás: si el valor almacenado es 'name'
                                        $selectedKey = $uniqueKey;
                                    }
                                }
                            }
                        ?>                        
                        <div class="form-group field-movement-provider">
                            <?= Html::activeLabel($model, 'provider', ['class' => 'form-label']) ?>
                            <?= Html::dropDownList('movement-provider-temp', $selectedKey, $providerOptions, [
                                'id' => 'movement-provider',
                                'prompt' => Yii::t('app', 'Seleccionar proveedor'),
                                'data-providers' => json_encode($providerIdMapping),
                                'class' => 'form-control'
                            ]) ?>
                            <?php if (!empty($model->provider)): ?>
                                <!-- Campo oculto inicial para edición -->
                                <?= Html::hiddenInput('Movement[provider]', $model->provider, ['id' => 'movement-provider-real']) ?>
                            <?php endif; ?>
                        </div>
    <style>
    .provider-warning {
        background: #fff3cd;
        border: 1px solid #ffeeba;
        color: #856404;
        padding: 16px;
        border-radius: 6px;
        margin-bottom: 16px;
        display: none;
    }
    .provider-warning .btn {
        margin-right: 8px;
    }
    
    /* Ajustar el espaciado del campo proveedor para que coincida con los demás campos */
    .field-movement-provider {
        margin-bottom: 1rem;
    }
    .field-movement-provider .form-label {
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--bs-body-color);
    }
    </style>
    <div id="provider-warning" class="provider-warning">
        <strong>Para mayor control, elija un proveedor.</strong><br>
        <div class="mt-2 d-flex flex-row gap-2">
            <a href="<?= \yii\helpers\Url::to(['provider/create']) ?>" target="_blank" class="btn btn-warning btn-sm">Crear proveedor</a>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="continue-without-provider">Continuar sin proveedor</button>
        </div>
    </div>
<script>
window.providerKeyToBusinessName = <?= json_encode($keyToBusinessNameMapping) ?>;
document.addEventListener('DOMContentLoaded', function() {
    var providerSelect = document.getElementById('movement-provider');
    var paymentTypeSelect = document.getElementById('movement-payment-type');
    var providerWarning = document.getElementById('provider-warning');
    var continueBtn = document.getElementById('continue-without-provider');
    var movementForm = document.getElementById('movement-form');
    var allowSubmitWithoutProvider = false;
    var defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.text = 'Por definir';
    // Limpiar y dejar solo 'Por definir' al cargar
    if (paymentTypeSelect) {
        paymentTypeSelect.innerHTML = '';
        paymentTypeSelect.appendChild(defaultOption.cloneNode(true));
        paymentTypeSelect.value = '';
        console.log('[INIT] paymentTypeSelect solo Por definir');
    }
    function isProviderEmpty() {
        return !providerSelect.value || providerSelect.value === '' || providerSelect.value === null || typeof providerSelect.value === 'undefined';
    }
    function logPaymentTypeSelectState(context) {
        if (paymentTypeSelect) {
            var opts = [];
            for (var i = 0; i < paymentTypeSelect.options.length; i++) {
                opts.push(paymentTypeSelect.options[i].text + ':' + paymentTypeSelect.options[i].value);
            }
            console.log(`[${context}] Opciones paymentTypeSelect:`, opts);
            console.log(`[${context}] paymentTypeSelect.disabled:`, paymentTypeSelect.disabled);
            console.log(`[${context}] paymentTypeSelect.value:`, paymentTypeSelect.value);
        } else {
            console.log(`[${context}] paymentTypeSelect NO ENCONTRADO`);
        }
    }
    function resetPaymentTypeToDefault() {
        logPaymentTypeSelectState('ANTES resetPaymentTypeToDefault');
        if (paymentTypeSelect) {
            // Si es Select2, usar su API
            if ($(paymentTypeSelect).data('select2')) {
                $(paymentTypeSelect).empty();
                $(paymentTypeSelect).append('<option value="">Por definir</option>');
                $(paymentTypeSelect).val('').trigger('change');
                $(paymentTypeSelect).prop('disabled', true);
                console.log('[resetPaymentTypeToDefault] paymentTypeSelect (Select2) solo Por definir y deshabilitado');
            } else {
                // Select normal
                while (paymentTypeSelect.options.length > 0) {
                    paymentTypeSelect.remove(0);
                }
                var defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.text = 'Por definir';
                paymentTypeSelect.appendChild(defaultOption);
                paymentTypeSelect.value = '';
                paymentTypeSelect.selectedIndex = 0;
                paymentTypeSelect.disabled = true;
                console.log('[resetPaymentTypeToDefault] paymentTypeSelect solo Por definir y deshabilitado');
            }
            logPaymentTypeSelectState('DESPUES resetPaymentTypeToDefault');
        }
        // Si hay input oculto, también limpiarlo
        var paymentTypeHidden = document.querySelector('input[name="Movement[payment_type]"]');
        if (paymentTypeHidden) {
            paymentTypeHidden.value = '';
            console.log('[resetPaymentTypeToDefault] input oculto Movement[payment_type] limpiado');
        }
    }
    function showProviderWarning() {
        if (providerWarning) providerWarning.style.display = 'block';
        console.log('[showProviderWarning] Mostrando advertencia');
        resetPaymentTypeToDefault();
    }
    function hideProviderWarning() {
        if (providerWarning) providerWarning.style.display = 'none';
        console.log('[hideProviderWarning] Ocultando advertencia');
    }
    if (continueBtn) {
        continueBtn.addEventListener('click', function() {
            hideProviderWarning();
            allowSubmitWithoutProvider = true;
            if (providerSelect) {
                providerSelect.value = '';
                console.log('[continueBtn] Se presionó continuar sin proveedor, providerSelect.value = ""');
            }
            resetPaymentTypeToDefault();
        });
    }
    if (movementForm) {
        movementForm.addEventListener('submit', function(e) {
            if (isProviderEmpty() && !allowSubmitWithoutProvider) {
                e.preventDefault();
                showProviderWarning();
                console.log('[movementForm submit] No hay proveedor, mostrando advertencia y bloqueando submit');
            }
            allowSubmitWithoutProvider = false;
        });
    }
    if (providerSelect && paymentTypeSelect) {
        // Mostrar advertencia si no hay proveedor al cargar
        logPaymentTypeSelectState('LOAD INICIAL');
        if (isProviderEmpty()) {
            showProviderWarning();
            console.log('[LOAD] No hay proveedor al cargar, mostrando advertencia');
        } else {
            hideProviderWarning();
            console.log('[LOAD] Hay proveedor al cargar, ocultando advertencia');
        }
        providerSelect.addEventListener('change', function() {
            console.log('[providerSelect change] Nuevo valor:', providerSelect.value);
            logPaymentTypeSelectState('ANTES providerSelect change');
            if (isProviderEmpty()) {
                showProviderWarning();
                resetPaymentTypeToDefault();
                console.log('[providerSelect change] No hay proveedor, mostrando advertencia y limpiando paymentTypeSelect');
            } else {
                hideProviderWarning();
                // Si selecciona proveedor, limpiar y dejar solo el prompt
                if ($(paymentTypeSelect).data('select2')) {
                    $(paymentTypeSelect).empty();
                    $(paymentTypeSelect).append('<option value="">Seleccionar tipo de pago</option>');
                    $(paymentTypeSelect).val('').trigger('change');
                    $(paymentTypeSelect).prop('disabled', false);
                    console.log('[providerSelect change] Hay proveedor, mostrando solo prompt y habilitando paymentTypeSelect (Select2)');
                } else {
                    while (paymentTypeSelect.options.length > 0) {
                        paymentTypeSelect.remove(0);
                    }
                    var promptOption = document.createElement('option');
                    promptOption.value = '';
                    promptOption.text = 'Seleccionar tipo de pago';
                    paymentTypeSelect.appendChild(promptOption);
                    paymentTypeSelect.value = '';
                    paymentTypeSelect.selectedIndex = 0;
                    paymentTypeSelect.disabled = false;
                    console.log('[providerSelect change] Hay proveedor, mostrando solo prompt y habilitando paymentTypeSelect');
                }
                // Limpiar input oculto
                var paymentTypeHidden = document.querySelector('input[name="Movement[payment_type]"]');
                if (paymentTypeHidden) {
                    paymentTypeHidden.value = '';
                    console.log('[providerSelect change] input oculto Movement[payment_type] limpiado');
                }
            }
            logPaymentTypeSelectState('DESPUES providerSelect change');
        });
    }
});
                        </script>
                    <?php endif; ?>
                </div>

                <?php if ($model->type != $model::TYPE_OUTPUT): ?>
                    <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                        <?= $form->field($model, 'payment_type')->dropDownList(
                            \common\models\Movement::getFormattedPaymentTypes(),
                            [
                                'id' => 'movement-payment-type',
                                'data-setting' => 'input',
                                'prompt' => Yii::t('app', 'Seleccionar tipo de pago')
                            ]
                        ) ?>
                    </div>
                <?php endif; ?>

                <div class="col-sm-12 col-md-4 col-lg-4 col-xl-4">
                    <?= $form->field($model, 'quantity')->textInput(['data-setting' => 'all']) ?>
                </div>

                <!-- Tercera fila: Los campos financieros (5 elementos) -->  
                <?php if ($model->type != $model::TYPE_OUTPUT): ?>
                    <div class="col-sm-12 col-md-6 col-lg-4 col-xl-2">
                        <?= $form->field($model, 'invoice')->textInput(['maxlength' => true, 'data-setting' => 'input']) ?>
                    </div>
                    
                    <div class="col-sm-12 col-md-6 col-lg-4 col-xl-2">
                        <?= $form->field($model, 'amount',
                            [
                                'template' => "{label}<br><div class='input-group'><span class='input-group-text'>${currencySymbol}</span>{input} </div>"
                            ]
                        )->textInput(['data-setting' => 'input']) ?>
                    </div>
                    
                    <div class="col-sm-12 col-md-6 col-lg-4 col-xl-2">
                        <?= $form->field($model, 'tax')->textInput(['data-setting' => 'input'])->label('Impuesto') ?>
                    </div>
                    
                    <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3">
                        <?= $form->field($model, 'unit_price', [
                            'template' => "{label}<br><div class='input-group'><span class='input-group-text'>${currencySymbol}</span>{input} </div>"
                        ])->textInput(['data-setting' => 'input']) ?>
                    </div>
                    
                    <div class="col-sm-12 col-md-6 col-lg-4 col-xl-3">
                        <?= $form->field($model, 'total', [
                            'template' => "{label}<br><div class='input-group'><span class='input-group-text'>${currencySymbol}</span>{input} </div>"
                        ])->textInput(['data-setting' => 'input']) ?>
                    </div>
                <?php endif; ?>

                <!-- Observaciones al final -->
                <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                    <?= $form->field($model, 'observations')->textarea(['data-setting' => 'all']) ?>
                </div>
            </div>


        </div>        <div class="card-footer">
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
                <?= Html::a(Yii::t('app', 'Cancel'), ['movement/index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>


    <?php ActiveForm::end(); ?>

</div>
