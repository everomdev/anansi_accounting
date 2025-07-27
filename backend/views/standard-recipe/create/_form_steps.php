<?php
/** @var $recipe \common\models\StandardRecipe */
/** @var $model \common\models\RecipeStep */
/** @var $pjaxId string */
?>

<div class="row gap-3">
    <?php $form = \yii\bootstrap5\ActiveForm::begin([
        'id' => 'form_step',
        'enableClientValidation' => false,
        'enableAjaxValidation' => false,
        'action' => \yii\helpers\Url::to(['standard-recipe/add-step', 'id' => $recipe->id]),
        'method' => 'post',
        'options' => [
            'enctype' => 'multipart/form-data',
            'data-pjax' => $pjaxId
        ]
    ]) ?>
    <?= $form->field($model, 'type')->hiddenInput()->label(false) ?>
    <div class="col-12">
        <?= $form->field($model, 'activity')->textarea() ?>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-success d-flex align-items-center gap-2" id="add-step-btn">
            <span class="spinner-border spinner-border-sm me-2" id="add-step-spinner" style="display:none;" role="status" aria-hidden="true"></span>
            <span id="add-step-btn-text"><?= Yii::t('app', 'Add') ?></span>
        </button>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var addBtn = document.getElementById('add-step-btn');
    var addSpinner = document.getElementById('add-step-spinner');
    var addText = document.getElementById('add-step-btn-text');
    var form = document.getElementById('form_step');
    if (form && addBtn && addSpinner && addText) {
        form.addEventListener('submit', function() {
            addSpinner.style.display = 'inline-block';
            addText.textContent = 'Cargando...';
            addBtn.disabled = true;
        });
        // Si el formulario se envía por PJAX, recargar la página al completar
        $(document).on('pjax:end', function(e) {
            // Solo recargar si el evento es para este formulario
            if (e.target && e.target.id === 'form_step') {
                location.reload();
            }
        });
    }
});
</script>
        </div>
        <div class="form-text">Selecciona la duración: horas, minutos y segundos. Ejemplo: 0 horas, 5 minutos y 40 segundos.</div>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'indicator')->textInput() ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, '_image')->fileInput() ?>
    </div>
    <div class="col-12">
        <?= \yii\bootstrap5\Html::submitButton(Yii::t('app', 'Add'), [
            'class' => 'btn btn-success'
        ]) ?>
    </div>
    <?php \yii\bootstrap5\ActiveForm::end(); ?>
</div>
