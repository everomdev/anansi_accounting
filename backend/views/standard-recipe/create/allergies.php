<?php
/** @var $this \yii\web\View */
/** @var $model \common\models\StandardRecipe */
/** @var $form \yii\bootstrap5\ActiveForm */

$allergies = Yii::$app->params['allergies'];
$selectedAllergies = [];
if (!empty($model->allergies)) {
    $selectedAllergies = array_values(explode(";", trim($model->allergies)));
    $allergies = array_unique(array_values(array_merge($allergies, $selectedAllergies)));
}

$indexOfSelectedItems = [];
foreach ($selectedAllergies as $selectedAllergy) {
    $indexOfSelectedItems[] = array_search($selectedAllergy, $allergies);
}
?>

<?= $form->field($model, 'allergies')->hiddenInput() ?>

<br>

<?= \yii\bootstrap5\Html::checkboxList('_allergies', $indexOfSelectedItems, $allergies, [
    'class' => 'd-flex flex-wrap gap-3',
    'id' => 'allergies-list',
    'itemOptions' => [
        'class' => 'form-check-input allergen-checkbox',
    ],
]) ?>

<br>
<div class="form-group">
    <?= \yii\bootstrap5\Html::label("Otros (poner ; para insertar)", 'allergies-other') ?>
    <?= \yii\bootstrap5\Html::textInput('allergies-other', '', ['class' => 'form-control', 'id' => 'allergies-other']) ?>
</div>
