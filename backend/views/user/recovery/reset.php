<?php

/*
 * This file is part of the 2amigos/yii2-usuario project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View               $this
 * @var yii\widgets\ActiveForm     $form
 * @var \Da\User\Form\RecoveryForm $model
 */

$this->title = Yii::t('usuario', 'Reset your password');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="vh-100 d-flex justify-content-center align-items-center">
    <div style="max-width: 500px; min-width: 350px">
        <div class="text-center mb-3">
            <img src="<?= Yii::getAlias("@web/images/logo1.png") ?>" alt="" width="300">
        </div>
        <?php $form = ActiveForm::begin([
            'id' => $model->formName(),
            'enableAjaxValidation' => true,
            'enableClientValidation' => false,
        ]); ?>
        <div class="card">
            <div class="card-header text-center" style="background: #fff; color: #222;">
                <h3 class="card-title mb-0"><?= Html::encode($this->title) ?></h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <?= $form->field($model, 'password', [
                        'template' => '{label}<div class="d-flex w-100" style="gap:0;">'
                            .'<input type="password" name="'.Html::getInputName($model, 'password').'" class="form-control w-100" autocomplete="new-password" id="'.Html::getInputId($model, 'password').'">'
                            .'<button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1" style="border-top-left-radius:0;border-bottom-left-radius:0;"><i class="bx bx-show"></i></button></div>{error}'
                    ])->textInput(['style' => 'display:none']) ?>
                </div>
            </div>
            <div class="card-footer">
                <?= Html::submitButton(Yii::t('usuario', 'Finish'), ['class' => 'btn btn-success btn-block w-100']) ?><br>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
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
