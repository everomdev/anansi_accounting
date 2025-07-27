<script>
// Cambia el id por el de tu contenedor PJAX de la lista de pasos si es diferente
$(document).on('pjax:end', '#pjax-list-steps', function() {
    location.reload();
});
</script>
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
    <div class="col-12 row g-2 align-items-end">
        <div class="col-4">
            <label for="input-hours" class="form-label">Horas</label>
            <input type="number" min="0" max="23" class="form-control" id="input-hours" name="input-hours" value="<?= isset($model->time) && $model->time ? explode(':', str_pad($model->time, 8, '0', STR_PAD_LEFT))[0] : '00' ?>">
        </div>
        <div class="col-4">
            <label for="input-minutes" class="form-label">Minutos</label>
            <input type="number" min="0" max="59" class="form-control" id="input-minutes" name="input-minutes" value="<?= isset($model->time) && $model->time ? explode(':', str_pad($model->time, 8, '0', STR_PAD_LEFT))[1] : '00' ?>">
        </div>
        <div class="col-4">
            <label for="input-seconds" class="form-label">Segundos</label>
            <input type="number" min="0" max="59" class="form-control" id="input-seconds" name="input-seconds" value="<?= isset($model->time) && $model->time ? explode(':', str_pad($model->time, 8, '0', STR_PAD_LEFT))[2] : '00' ?>">
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
