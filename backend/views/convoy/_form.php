<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Convoy */
/* @var $form yii\widgets\ActiveForm */

$this->registerJsFile(Yii::getAlias("@web/js/convoy/form.js"), [
    'depends' => [\yii\web\YiiAsset::class]
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

$data = \yii\helpers\ArrayHelper::map(array_merge($ingredients, $recipes), 'id', 'name');

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
            <?php if (!$model->isNewRecord): ?>
                <?php \yii\widgets\Pjax::begin(['id' => 'pjax-ingredients', 'timeout' => false]) ?>
                <?= $form->field($model, 'plates')->textInput(['type' => 'number']) ?>

                <?= \yii\bootstrap5\Html::label(Yii::t('app', "Cost")) ?>

                <?= \yii\bootstrap5\Html::tag('span', $business->formatter->asCurrency($model->amount), [
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
                                <td><?= $convoyIngredient->model->name ?></td>
                                <td><?= $convoyIngredient->quantity ?></td>
                                <td><?= $business->formatter->asCurrency($convoyIngredient->amount) ?></td>
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
    ]);

    echo $form->field($convoyIngredient, 'selectedEntity')->widget(\kartik\select2\Select2::class, [
        'data' => $data,
        'pluginOptions' => [
            'dropdownParent' => '#modal-add-ingredient'
        ]
    ]);

    echo $form->field($convoyIngredient, 'quantity')->textInput();

    echo \yii\bootstrap5\Html::submitButton(Yii::t('app', "Add"), [
        'class' => 'btn btn-success btn-sm',
    ]);

    \yii\bootstrap5\ActiveForm::end();

    \yii\bootstrap5\Modal::end();
}
?>
