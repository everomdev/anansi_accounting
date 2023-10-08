<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Plan */
/* @var $form yii\widgets\ActiveForm */

\frontend\assets\DualListboxAsset::register($this);
?>

<div class="plan-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="card">
        <div class="card-body">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'monthly_price')->textInput() ?>

            <?= $form->field($model, 'yearly_price')->textInput() ?>

            <?= $form->field($model, 'users')->textInput() ?>
            <?= $form->field($model, 'recetas')->textInput() ?>
            <?= $form->field($model, 'subrecetas')->textInput() ?>
            <?= $form->field($model, 'convoy')->textInput() ?>
            <?= $form->field($model, 'combos')->textInput() ?>
            <br><br>
            <?= $form->field($model, 'permissions')->dropDownList(
                \yii\helpers\ArrayHelper::map(Yii::$app->authManager->getPermissions(), 'name', 'description'),
                ['multiple' => true]
            ) ?>

            <?= $form->field($model, 'intro')->hiddenInput() ?>
            <div class="border">
                <div id="intro-toolbar"></div>
                <div id="intro-editor"><?= $model->intro ?></div>
            </div>
            <br><br>
            <?= $form->field($model, 'description')->hiddenInput() ?>
            <div class="border">
                <div id="description-toolbar"></div>
                <div id="description-editor"><?= $model->description ?></div>
            </div>


        </div>
        <div class="card-footer">
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
            </div>
        </div>
    </div>


    <?php ActiveForm::end(); ?>

</div>
<?php
$js = <<< JS
$(function(){
    let dlb1 = new DualListbox('#plan-permissions', {});
    $(".dual-listbox__search").addClass("form-control");
    
    DecoupledEditor
        .create(document.querySelector('#intro-editor'))
        .then(editor => {
            const toolbarContainer = document.querySelector('#intro-toolbar');

            toolbarContainer.appendChild(editor.ui.view.toolbar.element);
            editor.model.document.on('change:data', () => {
                $("#plan-intro").val(editor.getData());
            });
        })
        .catch(error => {
            console.error(error);
        });
    
    DecoupledEditor
        .create(document.querySelector('#description-editor'))
        .then(editor => {
            const toolbarContainer = document.querySelector('#description-toolbar');

            toolbarContainer.appendChild(editor.ui.view.toolbar.element);
            editor.model.document.on('change:data', () => {
                $("#plan-description").val(editor.getData());
            });
        })
        .catch(error => {
            console.error(error);
        });
});
JS;
$this->registerJs($js);
?>
