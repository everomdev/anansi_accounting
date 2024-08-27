<?php
/** @var $this \yii\web\View
 */



?>
<div class="card shadow-none">
    <?php $form = \yii\bootstrap5\ActiveForm::begin([
        'action' => \yii\helpers\Url::to(['standard-recipe/finish-recipe-creation', 'id' => $model->id]),
        'enableAjaxValidation' => true
    ]) ?>
    <div class="card-body">
        <div class="row gap-3">
            <div class="col-12"><?= $form->field($model, 'flowchart')->textarea() ?></div>
            <div class="col-12"><?= $form->field($model, 'equipment')->textarea() ?></div>
            <div class="col-12"><?= $form->field($model, 'steps')->textarea() ?></div>
            <div class="col-12"><?= $form->field($model, 'allergies')->textarea() ?></div>
        </div>
    </div>
    <div class="card-footer">
        <?= \yii\bootstrap5\Html::a(Yii::t('app', 'Back'), \yii\helpers\Url::to(['standard-recipe/select-ingredients', 'id' => $model->id]), [
            'class' => "btn btn-warning"
        ]) ?>
        <?= \yii\bootstrap5\Html::submitButton(Yii::t('app', 'Finish'), [
            'class' => 'btn btn-success'
        ]) ?>
    </div>
    <?php \yii\bootstrap5\ActiveForm::end(); ?>
</div>

