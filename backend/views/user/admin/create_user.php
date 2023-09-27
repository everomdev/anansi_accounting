<?php
/** @var $this \yii\web\View */

/** @var $model \backend\models\CreateUserForm */

use yii\bootstrap5\ActiveForm;

$this->title = Yii::t('app', "Add new user");

$roles = Yii::$app->authManager->getRoles();
$roles = array_filter($roles, function ($role) {
    return $role->name != 'admin';
});
?>

<div class="card">
    <?php $form = \yii\bootstrap5\ActiveForm::begin([
        'enableAjaxValidation' => true
    ]) ?>
    <div class="card-body">
        <?= $form->field($model, 'name')->textInput() ?>
        <?= $form->field($model, 'email')->textInput() ?>
        <?= $form->field($model, 'password')->textInput(['type' => 'password']) ?>
        <?= $form->field($model, 'confirmPassword')->textInput(['type' => 'password']) ?>
        <?= $form->field($model, 'role')->dropDownList(
                \yii\helpers\ArrayHelper::map($roles, 'name', 'description')
        ) ?>
    </div>
    <div class="card-footer">
        <?= \yii\bootstrap5\Html::submitButton(
            Yii::t('app', "Create"),
            [
                'class' => 'btn btn-success'
            ]
        ) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
