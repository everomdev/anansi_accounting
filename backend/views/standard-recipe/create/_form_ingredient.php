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
                'class' => 'form-control',
                'step' => 'any'
            ]) ?>
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


JS;
$this->registerJs($js);
?>
