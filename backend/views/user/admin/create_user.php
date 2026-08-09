<?php
/** @var $this \yii\web\View */

/** @var $model \backend\models\CreateUserForm */

use yii\bootstrap5\ActiveForm;

$this->title = Yii::t('app', "Add new user");

$roles = Yii::$app->authManager->getRoles();
$roles = array_filter($roles, function ($role) {
    return $role->name != 'admin';
});
// Ordenar roles por descripción
usort($roles, function($a, $b) {
    return strcmp($a->description, $b->description);
});

// Obtener centros de consumo del restaurante (excluyendo "Almacén")
$business = \backend\helpers\RedisKeys::getBusiness();
$consumptionCenters = \yii\helpers\ArrayHelper::map(
    \common\models\ConsumptionCenter::find()
        ->where(['business_id' => $business->id])
        ->andWhere(['!=', 'name', 'Almacén'])
        ->all(),
    'id',
    'name'
);
?>

<div class="card">
    <?php $form = \yii\bootstrap5\ActiveForm::begin([
        'enableAjaxValidation' => true
    ]) ?>
    <div class="card-body">
        <?php if ($model->hasErrors()): ?>
            <?= $form->errorSummary($model, [
                'header' => '<strong>' . Yii::t('app', 'No se pudo crear el usuario. Corrige los siguientes errores:') . '</strong>',
            ]) ?>
        <?php endif; ?>
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
        
        <!-- Campo para Centro de Consumo (solo visible para "Solicitante de Consumo") -->
        <div id="consumption-center-field" style="display: none;">
            <?= $form->field($model, 'consumption_center_id')->radioList($consumptionCenters, [
                'item' => function($index, $label, $name, $checked, $value) {
                    $checked = $checked ? 'checked' : '';
                    return "<div class='form-check'>
                        <input type='radio' id='center-{$value}' name='{$name}' value='{$value}' class='form-check-input' {$checked}>
                        <label class='form-check-label' for='center-{$value}'>{$label}</label>
                    </div>";
                }
            ])->label('Centro de Consumo <span class="text-danger">*</span>') ?>
        </div>
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

// Mostrar/ocultar centros de consumo según el rol seleccionado
$('#createuserform-role').on('change', function() {
    var selectedRole = $(this).val();
    if (selectedRole === 'consumption_requester') {
        $('#consumption-center-field').slideDown();
    } else {
        $('#consumption-center-field').slideUp();
        // Desmarcar todos los radio buttons
        $('input[name="CreateUserForm[consumption_center_id]"]').prop('checked', false);
    }
});

// Verificar al cargar la página
$(document).ready(function() {
    var selectedRole = $('#createuserform-role').val();
    if (selectedRole === 'consumption_requester') {
        $('#consumption-center-field').show();
    }
});
JS);
?>
