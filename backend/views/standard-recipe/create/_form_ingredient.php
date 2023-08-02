<?php
/** @var $this \yii\web\View */

use kartik\typeahead\Typeahead;

?>

    <div class="row gap-3">
        <?php $form = \yii\bootstrap5\ActiveForm::begin([
            'id' => 'form_ingredient',
            'enableClientValidation' => true,
            'enableAjaxValidation' => true,
            'action' => \yii\helpers\Url::to(['standard-recipe/select-ingredients', 'id' => $recipe->id]),
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
