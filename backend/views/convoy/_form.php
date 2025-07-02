<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Convoy */
/* @var $form yii\widgets\ActiveForm */

$this->registerJsFile(Yii::getAlias("@web/js/business-number-formatter.js"), [
    'depends' => [\yii\web\YiiAsset::class]
]);
$this->registerJsFile(Yii::getAlias("@web/js/convoy/form.js"), [
    'depends' => [\yii\web\YiiAsset::class]
]);

// Registrar configuración del formateador
$this->registerJsVar('businessFormatConfig', [
    'decimalSeparator' => $business->decimal_separator ?? '.',
    'thousandSeparator' => $business->thousand_separator ?? ',',
    'currencySymbol' => $business->currency_symbol ?? '$',
    'currencyCode' => $business->currency_code ?? 'USD'
]);
$isClass = \common\models\IngredientStock::class;
$ingredients = \common\models\IngredientStock::find()
    ->andWhere(['ingredient_stock.business_id' => $model->business_id])
    ->all();

$srClass = \common\models\StandardRecipe::class;
$recipes = \common\models\StandardRecipe::find()
    ->andWhere(['standard_recipe.business_id' => $model->business_id])
    ->andWhere(['type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB])
    ->andWhere(['in_construction' => false])
    ->all();

$ingredients = array_map(function ($ingredient) {
    $ingredient->id = "ingredient_" . $ingredient->id;
    return $ingredient;
}, $ingredients);

$recipes = array_map(function ($recipe) {
    $recipe->id = "recipe_" . $recipe->id;
    return $recipe;
}, $recipes);

$data = \yii\helpers\ArrayHelper::map(array_merge($ingredients, $recipes), 'id', function($i){
    return sprintf("%s (%s)", $i->name, ($i instanceof \common\models\IngredientStock) ? $i->portion_um : $i->yield_um);
});

$businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$business = \common\models\Business::findOne(['id' => $businessData['id']])
?>

<div class="convoy-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-convoy',
        'enableAjaxValidation' => true
    ]); ?>
    <div class="card">
        <div class="card-body">

            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'type')->hiddenInput(['value' => $model::TYPE_FAMILY])->label(false) ?>
<!--            --><?php //= $form->field($model, 'um')->dropDownList(\yii\helpers\ArrayHelper::map(\common\models\UnitOfMeasurement::find()->all(), 'name', 'name')) ?>
            <?= $form->field($model, 'observations')->textarea() ?>
            <?php if (!$model->isNewRecord): ?>
                <?php \yii\widgets\Pjax::begin(['id' => 'pjax-ingredients', 'timeout' => false]) ?>
                <?= $form->field($model, 'plates')->textInput(['type' => 'number']) ?>

                <?= \yii\bootstrap5\Html::label(Yii::t('app', "Cost")) ?>                <?= \yii\bootstrap5\Html::tag('span', formatPrice($model->amount), [
                    'class' => 'form-control'
                ]) ?>
                <div class="table-responsive mt-3">
                    <table class="table overflow-hidden">
                        <thead>
                        <th><?= Yii::t('app', "Description") ?></th>
                        <th><?= Yii::t('app', "Quantity") ?></th>
                        <th><?= Yii::t('app', "Amount") ?></th>
                        <th><?= \yii\bootstrap5\Html::button(Yii::t('app', "Add"), [
                                'class' => 'btn btn-sm btn-success',
                                'data-bs-toggle' => 'modal',
                                'data-bs-target' => "#modal-add-ingredient"
                            ]) ?></th>
                        </thead>
                        <tbody>                        
                            <?php foreach ($model->convoyIngredients as $convoyIngredient): ?>
                            <tr>
                                <td>
                                    <?= $convoyIngredient->model->name ?>
                                    <?php if ($convoyIngredient->model instanceof \common\models\IngredientStock): ?>
                                        (<?= $convoyIngredient->model->portion_um ?>)
                                    <?php elseif ($convoyIngredient->model instanceof \common\models\StandardRecipe): ?>
                                        (<?= $convoyIngredient->model->yield_um ?>)
                                    <?php endif; ?>
                                </td>
                                <td><?= number_format($convoyIngredient->quantity, 2, $business->decimal_separator ?? '.', $business->thousand_separator ?? ',') ?></td>
                                <td><?= formatPrice($convoyIngredient->amount) ?></td>
                                <td>

                                    <?= \yii\bootstrap5\Html::button(Yii::t('app', "Remove"), [
                                        'class' => 'btn btn-danger btn-sm remove',
                                        'data-url' => \yii\helpers\Url::to(['convoy/remove-ingredient', 'id' => $model->id, 'ingredientId' => $convoyIngredient->id]),
                                        'data-message' => Yii::t('app', "Are you sure you want to delete this item?")
                                    ]) ?>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php \yii\widgets\Pjax::end(); ?>
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <div class="form-group">
                <?php if ($model->isNewRecord): ?>
                    <?= Html::submitButton(Yii::t('app', 'Save and Add ingredients'), ['class' => 'btn btn-success']) ?>
                <?php else: ?>
                    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
                <?php endif; ?>
                <?= Html::a(Yii::t('app', 'Cancel'), 
                    ['convoy/index'], 
                    ['class' => 'btn btn-outline-secondary']
                ) ?>
            </div>
        </div>
    </div>


    <?php ActiveForm::end(); ?>

</div>

<?php
if (!$model->isNewRecord) {
    $convoyIngredient = new \common\models\ConvoyIngredient([
        'convoy_id' => $model->id,
        'quantity' => 1
    ]);
    \yii\bootstrap5\Modal::begin([
        'id' => 'modal-add-ingredient',
        'title' => Yii::t('app', "Add ingredient")
    ]);

    $form = \yii\bootstrap5\ActiveForm::begin([
        'id' => 'form-add-ingredient',
        'enableAjaxValidation' => true,
        'action' => \yii\helpers\Url::to(['convoy/add-ingredient', 'id' => $model->id])
    ]);    echo $form->field($convoyIngredient, 'selectedEntity')->widget(\kartik\select2\Select2::class, [
        'data' => $data,
        'pluginOptions' => [
            'dropdownParent' => '#modal-add-ingredient'
        ]
    ])->label(Yii::t('app', 'Ingrediente/Subreceta'));echo $form->field($convoyIngredient, 'quantity')->textInput([
        'class' => 'form-control',
        'data-format' => 'number',
        'data-decimals' => '2',
        'step' => '0.01'
    ]);

    echo \yii\bootstrap5\Html::submitButton(Yii::t('app', "Add"), [
        'class' => 'btn btn-success btn-sm',
    ]);

    \yii\bootstrap5\ActiveForm::end();    \yii\bootstrap5\Modal::end();
}

// JavaScript para inicializar el formateador de números
$js = <<<JS
$(document).ready(function() {
    // Función específica para formatear el campo de cantidad
    function formatQuantityField() {
        var quantityInput = $('#convoyingredient-quantity');
        if (quantityInput.length) {
            // Configurar eventos específicos para el campo cantidad
            quantityInput.off('blur.quantity').on('blur.quantity', function() {
                var value = parseFloat(this.value) || 0;
                this.value = value.toFixed(2);
            });
            
            // Formatear valor inicial si existe
            if (quantityInput.val()) {
                var initialValue = parseFloat(quantityInput.val()) || 0;
                quantityInput.val(initialValue.toFixed(2));
            }
        }
    }
    
    // Inicializar el formateador de números cuando se abra el modal
    $('#modal-add-ingredient').on('shown.bs.modal', function() {
        if (window.BusinessNumberFormatter) {
            window.BusinessNumberFormatter.setupAutoFormatInputs();
        }
        // Aplicar formato específico al campo cantidad
        formatQuantityField();
    });
    
    // También inicializar inmediatamente si el modal ya está visible
    if (window.BusinessNumberFormatter) {
        window.BusinessNumberFormatter.setupAutoFormatInputs();
    }
    formatQuantityField();
});
JS;

$this->registerJs($js);
?>
