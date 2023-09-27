<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Ingredient */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="ingredient-form">

    <?php $form = ActiveForm::begin(); ?>
<div class="card">
    <div class="card-body">
        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'um')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'category_id')->dropDownList(
                \yii\helpers\ArrayHelper::map(\common\models\Category::find()->all(), 'id', 'name')
        ) ?>
    </div>
    <div class="card-footer">
        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
        </div>
    </div>
</div>




    <?php ActiveForm::end(); ?>

</div>
