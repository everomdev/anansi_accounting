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
            ingredient_stock.ingredient,
            CASE WHEN ingredient_stock.brand IS NOT NULL AND ingredient_stock.brand != '' THEN CONCAT('  ', ingredient_stock.brand) ELSE '' END,
            CASE WHEN ingredient_stock.presentation IS NOT NULL AND ingredient_stock.presentation != '' THEN CONCAT('  ', ingredient_stock.presentation) ELSE '' END
        ) as label"
    ])
    ->from('ingredient_stock')
    ->where(['business_id' => $business->id])
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
                \yii\helpers\Url::to(['ingredient-stock/create']),
                [
                    'class' => 'btn btn-sm btn-success'
                ]
            ) ?>
        </div>
        <div class="card-body">
            <div class="row gap-1">

                <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                    <?= $form->field($model, 'ingredient_id')->widget(\kartik\select2\Select2::class, [
                        'data' => \yii\helpers\ArrayHelper::map($stock, 'id', 'label'),
                        'options' => [
                            'data-setting' => 'all'
                        ]
                    ]) ?>
                </div>                
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                    <?php if ($model->type == \common\models\Movement::TYPE_OUTPUT): ?>
                        <?= $form->field($model, 'provider')->dropDownList(\yii\helpers\ArrayHelper::map(\common\models\ConsumptionCenter::find()->all(), 'name', 'name'))->label(Yii::t('app', "Consumption Center")) ?>                    <?php else: ?>                        <?php 
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
                        ?>                        <div class="form-group">
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
    </style>
    <div id="provider-warning" class="provider-warning">
        <strong>Para mejor control debe elegir un proveedor.</strong><br>
        <div class="mt-2 d-flex flex-row gap-2">
            <a href="<?= \yii\helpers\Url::to(['provider/create']) ?>" target="_blank" class="btn btn-warning btn-sm">Crear proveedor</a>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="continue-without-provider">Continuar sin proveedor</button>
        </div>
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
    }
    function isProviderEmpty() {
        return !providerSelect.value || providerSelect.value === '' || providerSelect.value === null || typeof providerSelect.value === 'undefined';
    }
    function showProviderWarning() {
        if (providerWarning) providerWarning.style.display = 'block';
    }
    function hideProviderWarning() {
        if (providerWarning) providerWarning.style.display = 'none';
    }
    if (continueBtn) {
        continueBtn.addEventListener('click', function() {
            hideProviderWarning();
            allowSubmitWithoutProvider = true;
            if (providerSelect) {
                providerSelect.value = '';
            }
        });
    }
    if (movementForm) {
        movementForm.addEventListener('submit', function(e) {
            if (isProviderEmpty() && !allowSubmitWithoutProvider) {
                e.preventDefault();
                showProviderWarning();
            }
            allowSubmitWithoutProvider = false;
        });
    }
    if (providerSelect && paymentTypeSelect) {
        // Mostrar advertencia si no hay proveedor al cargar
        if (isProviderEmpty()) {
            showProviderWarning();
        } else {
            hideProviderWarning();
        }
        providerSelect.addEventListener('change', function() {
            if (isProviderEmpty()) {
                paymentTypeSelect.innerHTML = '';
                paymentTypeSelect.appendChild(defaultOption.cloneNode(true));
                paymentTypeSelect.value = '';
                showProviderWarning();
            } else {
                hideProviderWarning();
                paymentTypeSelect.innerHTML = '';
                var loadingOption = document.createElement('option');
                loadingOption.value = '';
                loadingOption.text = 'Cargando...';
                paymentTypeSelect.appendChild(loadingOption);
                paymentTypeSelect.value = '';
                fetch(window.getProviderPaymentTypesUrl + '?provider=' + encodeURIComponent(providerSelect.value))
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        paymentTypeSelect.innerHTML = '';
                        paymentTypeSelect.appendChild(defaultOption.cloneNode(true));
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(function(item, idx) {
                                var opt = document.createElement('option');
                                opt.value = item.value;
                                opt.text = item.label;
                                paymentTypeSelect.appendChild(opt);
                            });
                            paymentTypeSelect.selectedIndex = 1;
                        } else {
                            paymentTypeSelect.value = '';
                        }
                    })
                    .catch(function() {
                        paymentTypeSelect.innerHTML = '';
                        paymentTypeSelect.appendChild(defaultOption.cloneNode(true));
                        paymentTypeSelect.value = '';
                    });
            }
        });
    }
});
                        </script>
                    <?php endif; ?>
                </div>                <?php if ($model->type != $model::TYPE_OUTPUT): ?>
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
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
                <?php if ($model->type != $model::TYPE_OUTPUT): ?>
                    <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                        <?= $form->field($model, 'invoice')->textInput(['maxlength' => true, 'data-setting' => 'input']) ?>
                    </div>
                <?php endif; ?>
                <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                    <?= $form->field($model, 'quantity')->textInput(['data-setting' => 'all']) ?>
                </div>
                <?php if ($model->type != $model::TYPE_OUTPUT): ?>
                    <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                        <?= $form->field($model, 'amount',
                            [
                                'template' => "{label}<br><div class='input-group'><span class='input-group-text'>${currencySymbol}</span>{input} </div>"
                            ]
                        )->textInput(['data-setting' => 'input']) ?>
                    </div>
                <?php endif; ?>
                <?php if ($model->type != $model::TYPE_OUTPUT): ?>
                    <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                        <?= $form->field($model, 'tax')->textInput(['data-setting' => 'input'])->label('Impuesto') ?>
                    </div>
                <?php endif; ?>
                <!--                <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">-->
                <!--                    --><?php //= $form->field($model, 'retention')->textInput(['data-setting' => 'input']) ?>
                <!--                </div>-->
                <?php if ($model->type != $model::TYPE_OUTPUT): ?>
                    <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                        <?= $form->field($model, 'unit_price', [
                            'template' => "{label}<br><div class='input-group'><span class='input-group-text'>${currencySymbol}</span>{input} </div>"
                        ])->textInput(['data-setting' => 'input']) ?>
                    </div>
                <?php endif; ?>
                <?php if ($model->type != $model::TYPE_OUTPUT): ?>
                    <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                        <?= $form->field($model, 'total', [
                            'template' => "{label}<br><div class='input-group'><span class='input-group-text'>${currencySymbol}</span>{input} </div>"
                        ])->textInput(['data-setting' => 'input']) ?>
                    </div>
                <?php endif; ?>

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
