<?php
/** @var $this \yii\web\View */
/** @var $recipe \common\models\StandardRecipe */

use kartik\typeahead\Typeahead;
$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
if(empty($recipe)){
    $stock = \yii\helpers\ArrayHelper::map(
        (new \yii\db\Query())
            ->select(['i.*', "CONCAT(i.key, ' - ',i.ingredient, ' (', i.portion_um,')') as label"])
            ->from('ingredient_stock i')
            ->leftJoin('ingredient_standard_recipe isr', 'i.id=isr.ingredient_id')
            ->leftJoin('standard_recipe sr', 'isr.standard_recipe_id = sr.id')
            ->andWhere(['i.business_id' => $business['id']])
            ->all(),
        'id', 'label'
    );
}else {
    $stock = \yii\helpers\ArrayHelper::map(
        (new \yii\db\Query())
            ->select(['i.*', "CONCAT(i.key, ' - ',i.ingredient, ' (', i.portion_um,')') as label"])
            ->from('ingredient_stock i')
            ->leftJoin('ingredient_standard_recipe isr', 'i.id=isr.ingredient_id')
            ->leftJoin('standard_recipe sr', 'isr.standard_recipe_id = sr.id')
            ->where(['or', ['sr.id' => null], ['<>', 'sr.id', $recipe->id]])
            ->andWhere(['i.business_id' => $business['id']])
            ->all(),
        'id', 'label'
    );

}
$subRecipes = \yii\helpers\ArrayHelper::map(
    (new \yii\db\Query())
        ->select(["id", "sr.title as label", "um"])
        ->from("standard_recipe sr")
        ->where([
            'sr.business_id' => $business['id'],
            'sr.type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB
        ])
        ->andFilterWhere(['not in', 'sr.id', $recipe ? $recipe->getAncestorIds() : []])
        ->all(),
    'id', function($sr){
        return sprintf("%s (%s)", $sr['label'], $sr['um']);
}
);
?>

    <div class="row gap-3">
        <?php $form = \yii\bootstrap5\ActiveForm::begin([
            'id' => 'form_ingredient',
            'enableClientValidation' => true,
            'enableAjaxValidation' => true,
            'action' => \yii\helpers\Url::to(['standard-recipe/select-ingredients', 'id' => $recipe == null ? null : $recipe->id]),
            'method' => 'post'
        ]) ?>
        <div class="col-12">
            <?= $form->field($model, 'ingredientId')->widget(\kartik\select2\Select2::class, [
                'id' => \yii\bootstrap5\Html::getInputId($model, 'ingredientId'),
                'data' => $stock,
                'model' => $model,
                'attribute' => 'ingredientId',
                'theme' => \kartik\select2\Select2::THEME_KRAJEE_BS5,
                'pluginOptions' => [
                    'dropdownParent' => '#modal-add-ingredient',
                    'allowClear' => true
                ],
                'options' => [
                    'placeholder' => Yii::t('app', "--------")
                ]
            ]) ?>
        </div>
        <div class="col-12">
            <?= $form->field($model, 'subRecipeId')->widget(\kartik\select2\Select2::class, [
                'id' => \yii\bootstrap5\Html::getInputId($model, 'subRecipeId'),
                'data' => $subRecipes,
                'model' => $model,
                'attribute' => 'subRecipeId',
                'theme' => \kartik\select2\Select2::THEME_KRAJEE_BS5,
                'pluginOptions' => [
                    'dropdownParent' => '#modal-add-ingredient',
                    'allowClear' => true
                ],
                'options' => [
                    'placeholder' => Yii::t('app', "--------")
                ]
            ]) ?>
        </div>
        <div class="col-12">
            <?= $form->field($model, 'quantity')->input('number', [
                'class' => 'form-control quantity-input',
                'step' => 'any',
                'id' => 'ingredient-quantity-input'
            ]) ?>
            <div id="quantity-warning" class="warning-message" style="display: none;"></div>
        </div>
        <div class="col-12">
            <?= \yii\bootstrap5\Html::submitButton(Yii::t('app', 'Add'), [
                'class' => 'btn btn-success'
            ]) ?>
        </div>
        <?php \yii\bootstrap5\ActiveForm::end(); ?>
    </div>
<?php
$this->registerJsVar('emptySelectorAlert', Yii::t('app', "You must select an ingredient or a sub recipe"));

$js = <<< JS

$(document).on("select2:select", "#standardrecipeingredientform-ingredientid", function (event){
    $("#standardrecipeingredientform-subrecipeid").val(null).trigger('change');
})
$(document).on("select2:select", "#standardrecipeingredientform-subrecipeid", function (event){
    $("#standardrecipeingredientform-ingredientid").val(null).trigger('change');
})

// Nueva funcionalidad para validar cantidades de unidades discretas
$(document).ready(function() {
    // Función para obtener el tipo de unidad seleccionada
    function getSelectedUnitType() {
        const ingredientId = $("#standardrecipeingredientform-ingredientid").val();
        const subRecipeId = $("#standardrecipeingredientform-subrecipeid").val();
        
        // Verificar el tipo de unidad para un ingrediente
        if (ingredientId) {
            // Extraer la unidad de medida del texto del Select2
            const selectedText = $("#select2-standardrecipeingredientform-ingredientid-container").text();
            const unitMatch = selectedText.match(/\(([^)]+)\)$/); // Buscar texto entre paréntesis al final
            
            if (unitMatch && unitMatch[1]) {
                return unitMatch[1].trim();
            }
        }
        
        // Verificar el tipo de unidad para una subreceta
        if (subRecipeId) {
            const selectedText = $("#select2-standardrecipeingredientform-subrecipeid-container").text();
            const unitMatch = selectedText.match(/\(([^)]+)\)$/);
            
            if (unitMatch && unitMatch[1]) {
                return unitMatch[1].trim();
            }
        }
        
        return null;
    }
    
    // Función para validar el valor de cantidad
    function validateQuantity() {
        const unitType = getSelectedUnitType();
        const quantityInput = $("#ingredient-quantity-input");
        const warningMessage = $("#quantity-warning");
        
        // Verificar si es una unidad discreta (porción, rebanada, etc.)
        const discreteUnits = ['porción', 'porcion', 'rebanada', 'trozo', 'pieza', 'unidad'];
        
        if (unitType && discreteUnits.includes(unitType.toLowerCase())) {
            const value = parseFloat(quantityInput.val());
            
            // Verificar si el valor no es un entero o es menor a 1
            if ((value % 1 !== 0 || value < 1) && !isNaN(value)) {
                // Mostrar advertencia y aplicar estilo
                quantityInput.css({
                    'background-color': '#ffebee',
                    'border-color': '#f44336'
                });
                
                warningMessage.text("Por favor asegúrese que el número es correcto para la unidad de medida " + unitType);
                warningMessage.css({
                    'display': 'block',
                    'color': '#f44336',
                    'font-size': '0.8rem',
                    'margin-top': '0.25rem'
                });
            } else {
                // Quitar advertencia y estilo
                quantityInput.css({
                    'background-color': '',
                    'border-color': ''
                });
                
                warningMessage.hide();
            }
        } else {
            // No es una unidad discreta, quitar cualquier advertencia
            quantityInput.css({
                'background-color': '',
                'border-color': ''
            });
            
            warningMessage.hide();
        }
    }
    
    // Validar cuando cambie el valor de cantidad
    $("#ingredient-quantity-input").on('input', validateQuantity);
    
    // Validar cuando se seleccione un ingrediente
    $("#standardrecipeingredientform-ingredientid").on('select2:select', function() {
        setTimeout(validateQuantity, 100); // Pequeño retraso para asegurar que select2 ha actualizado su UI
    });
    
    // Validar cuando se seleccione una subreceta
    $("#standardrecipeingredientform-subrecipeid").on('select2:select', function() {
        setTimeout(validateQuantity, 100);
    });
    
    // También validar al enviar el formulario
    $("#form_ingredient").on('beforeSubmit', function(e) {
        validateQuantity();
        // No bloqueamos el envío, solo alertamos
        return true;
    });
});


JS;
$this->registerJs($js);
?>

<style>
.warning-message {
    color: #f44336;
    font-size: 0.8rem;
    margin-top: 0.25rem;
}
</style>
