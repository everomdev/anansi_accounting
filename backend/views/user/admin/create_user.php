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
        <div class="mb-3">
            <?= $form->field($model, 'password', [
                'template' => '{label}<div class="d-flex w-100" style="gap:0;"><input type="password" name="CreateUserForm[password]" class="form-control w-100" autocomplete="new-password" id="createuserform-password">'
                    .'<button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1" style="border-top-left-radius:0;border-bottom-left-radius:0;"><i class="bx bx-show"></i></button></div>{error}'
            ])->textInput(['style' => 'display:none']) ?>
        </div>
        <div class="mb-3">
            <?= $form->field($model, 'confirmPassword', [
                'template' => '{label}<div class="d-flex w-100" style="gap:0;"><input type="password" name="CreateUserForm[confirmPassword]" class="form-control w-100" autocomplete="new-password" id="createuserform-confirmpassword">'
                    .'<button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1" style="border-top-left-radius:0;border-bottom-left-radius:0;"><i class="bx bx-show"></i></button></div>{error}'
            ])->textInput(['style' => 'display:none']) ?>
        </div>
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

<?php
$this->registerJs(<<<JS
$('.toggle-password').on('click', function() {
    var input = $(this).closest('div.d-flex').find('input');
    var icon = $(this).find('i');
    if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        icon.removeClass('bx-show').addClass('bx-hide');
    } else {
        input.attr('type', 'password');
        icon.removeClass('bx-hide').addClass('bx-show');
    }
});
JS);
?>
