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
            'data-pjax-container' => $pjaxId,
        ]
    ]) ?>
    <?= $form->field($model, 'type')->hiddenInput()->label(false) ?>
    <div class="col-12">
        <?= $form->field($model, 'activity')->textarea() ?>
    </div>
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input step-time-na-cb" type="checkbox" name="time_na" value="1"
                <?= (isset($model->time) && ($model->time === null || $model->time === '')) ? 'checked' : '' ?>>
            <label class="form-check-label fw-semibold">N/A &mdash; No aplica tiempo</label>
        </div>
    </div>
    <div class="step-time-inputs-group col-12 row g-2 align-items-end">
        <div class="col-4">
            <label class="form-label">Horas</label>
            <input type="number" min="0" max="23" class="form-control" name="input-hours" value="<?= isset($model->time) && $model->time ? explode(':', str_pad($model->time, 8, '0', STR_PAD_LEFT))[0] : '00' ?>">
        </div>
        <div class="col-4">
            <label class="form-label">Minutos</label>
            <input type="number" min="0" max="59" class="form-control" name="input-minutes" value="<?= isset($model->time) && $model->time ? explode(':', str_pad($model->time, 8, '0', STR_PAD_LEFT))[1] : '00' ?>">
        </div>
        <div class="col-4">
            <label class="form-label">Segundos</label>
            <input type="number" min="0" max="59" class="form-control" name="input-seconds" value="<?= isset($model->time) && $model->time ? explode(':', str_pad($model->time, 8, '0', STR_PAD_LEFT))[2] : '00' ?>">
        </div>
        <div class="form-text">Selecciona la duración: horas, minutos y segundos. Ejemplo: 0 horas, 5 minutos y 40 segundos.</div>
    </div>
    <script>
    (function() {
        var sc   = document.currentScript;
        var root = sc ? sc.parentElement : document.body;
        var cb   = root.querySelector('.step-time-na-cb');
        var grp  = root.querySelector('.step-time-inputs-group');
        if (!cb || !grp) return;
        function toggle() {
            var na = cb.checked;
            grp.querySelectorAll('input[type="number"]').forEach(function(i) { i.disabled = na; });
            grp.style.opacity = na ? '0.4' : '1';
        }
        cb.addEventListener('change', toggle);
        toggle();
    })();
    </script>
    <div class="col-12">
        <?= $form->field($model, 'indicator')->textInput() ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, '_image')->fileInput()->label('Imagen') ?>
    </div>
    <div class="col-12">
        <?= \yii\bootstrap5\Html::submitButton(Yii::t('app', 'Add'), [
            'class' => 'btn btn-success',
            'id' => 'btn-submit-step'
        ]) ?>
    </div>
    <?php \yii\bootstrap5\ActiveForm::end(); ?>
</div>