<?php
/** @var $this \yii\web\View */

/** @var $model \backend\models\CreateUserForm */

use yii\bootstrap5\ActiveForm;

\backend\assets\DualListboxAsset::register($this);

$this->title = Yii::t('app', "Update user");

// Cargar roles igual que en create_user.php
$roles = Yii::$app->authManager->getRoles();
$roles = array_filter($roles, function ($role) {
    return $role->name != 'admin';
});

// Obtener todos los permisos disponibles para permisos adicionales
$allPermissions = Yii::$app->authManager->getPermissions();

$this->registerJsVar('availableTitle', Yii::t('app', "Available permissions"));
$this->registerJsVar('selectedTitle', Yii::t('app', "Additional permissions"));
$this->registerJsVar('addButtonText', Yii::t('app', "Select"));
$this->registerJsVar('addAllButtonText', Yii::t('app', "Select all"));
$this->registerJsVar('removeButtonText', Yii::t('app', "Unselect"));
$this->registerJsVar('removeAllButtonText', Yii::t('app', "Unselect all"));
$this->registerJsVar('searchPlaceholder', Yii::t('app', "Search"));
?>

<div class="card">
    <?php $form = \yii\bootstrap5\ActiveForm::begin([
        'enableAjaxValidation' => true
    ]) ?>
    <div class="card-body">
        <?= $form->field($model, 'name')->textInput() ?>
        <?= $form->field($model, 'email')->textInput() ?>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'role')->dropDownList(
                    \yii\helpers\ArrayHelper::map($roles, 'name', 'description'),
                    ['class' => 'form-control']
                )->label('Rol base <small class="text-muted">(selecciona uno)</small>') ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, '_permissions')->dropDownList(
                    \yii\helpers\ArrayHelper::map($allPermissions, 'name', 'description'),
                    ['multiple' => true, 'class' => 'form-control']
                )->label('Permisos adicionales <small class="text-muted">(opcional)</small>') ?>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <?= \yii\bootstrap5\Html::submitButton(
            Yii::t('app', "Update"),
            [
                'class' => 'btn btn-success'
            ]
        ) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<< JS
$(function(){
    let dlb1 = new DualListbox('#createuserform-_permissions', {
        availableTitle,
        selectedTitle,
        addButtonText,
        addAllButtonText,
        removeButtonText,
        removeAllButtonText,
        searchPlaceholder,
    });
    $(".dual-listbox__search").addClass("form-control");
});
JS;
$this->registerJs($js);
?>
