<?php
/** @var $this \yii\web\View */
/** @var $model \backend\models\UpdateAccountForm */

$this->title = Yii::t('app', "My business");

?>

<div class="card">
    <?php $form = \yii\bootstrap5\ActiveForm::begin([
        'id' => 'my-business-form',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true
    ]) ?>
    <div class="card-body">
        <?= $form->field($model, 'businessName')->textInput() ?>
        <?= $form->field($model, 'name')->textInput() ?>
        <?= $form->field($model, 'password')->textInput(['type' => 'password']) ?>
    </div>
    <div class="card-footer">
        <?= \yii\bootstrap5\Html::submitButton(Yii::t('app', 'Save'), [
                'class' => 'btn btn-success'
        ]) ?>
    </div>
    <?php \yii\bootstrap5\ActiveForm::end(); ?>
</div>
