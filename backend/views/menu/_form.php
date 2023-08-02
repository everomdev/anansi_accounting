<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Menu */
/* @var $form yii\widgets\ActiveForm */
$availableRecipes = \common\models\StandardRecipe::findAll(['business_id' => $model->business_id]);

\backend\assets\DualListboxAsset::register($this);
$this->registerJsFile(Yii::getAlias("@web/js/menu/form.js"), [
    'position' => $this::POS_END,
    'depends' => [\yii\web\YiiAsset::class]
]);
$this->registerJsVar('availableTitle', Yii::t('app', "Available recipes"));
$this->registerJsVar('selectedTitle', Yii::t('app', "Selected recipes"));
$this->registerJsVar('addButtonText', Yii::t('app', "Add"));
$this->registerJsVar('addAllButtonText', Yii::t('app', "Add all"));
$this->registerJsVar('removeButtonText', Yii::t('app', "Remove"));
$this->registerJsVar('removeAllButtonText', Yii::t('app', "Remove all"));
$this->registerJsVar('searchPlaceholder', Yii::t('app', "Search"));
?>

<div class="menu-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-menu',
        'enableAjaxValidation' => true
    ]); ?>

    <div class="card">
        <div class="card-body">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'total_price')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, '_recipes')->dropDownList(
                \yii\helpers\ArrayHelper::map($availableRecipes, 'id', 'title'),
                [
                    'multiple' => true
                ]
            ) ?>
        </div>
        <div class="card-footer">
            <div class="form-group">
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>
        </div>
    </div>


    <?php ActiveForm::end(); ?>

</div>

