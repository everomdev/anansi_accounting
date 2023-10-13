<?php

/*
 * This file is part of the 2amigos/yii2-usuario project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use backend\assets\AdminLtePluginAsset;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var \Da\User\Form\RegistrationForm $model
 * @var \Da\User\Model\User $user
 * @var \Da\User\Module $module
 */

\backend\assets\SneatAsset::register($this);

$this->title = "Sign-up";
$plans = \common\models\Plan::find()->all();
?>
<div class="vh-100 d-flex align-items-center justify-content-center">
    <div>
        <?php $form = ActiveForm::begin(
            [
                'id' => $model->formName(),
                'enableAjaxValidation' => true,
                'enableClientValidation' => false,
            ]
        ); ?>
        <div class="card m-5">
            <div class="card-header">
                <div class="text-center">
                    <img src="<?= Yii::getAlias("@web/images/logo.png") ?>" alt="" width="300">
                </div>
                <span class="card-title"><?= Html::encode($this->title) ?></span>
            </div>
            <div class="card-body">
                <div class="row gap-3">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 row">
                        <div class="col-12">
                            <?= $form->field($model, 'name')->textInput(['autofocus' => true]) ?>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'businessName')->textInput(['autofocus' => true]) ?>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'password')->passwordInput() ?>
                        </div>
                        <div class="col-12">
                            <?= $form->field($model, 'planId')->dropDownList(
                                \yii\helpers\ArrayHelper::map($plans, 'id', 'label'),

                            ) ?>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <?php foreach ($plans as $index => $plan): ?>
                            <div id="plan_<?= $plan->id ?>" class="<?= $index == 0 ? '' : 'd-none' ?> plan">
                                <h3><?= 'Plan ' . $plan->name ?></h3>
                                <?= $plan->description ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <?= Html::submitButton(Yii::t('usuario', 'Sign up'), ['class' => 'btn btn-success btn-block']) ?>
                        <?= Html::a(Yii::t('usuario', 'Already registered? Sign in!'), ['/user/security/login']) ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>


</div>
<?php
$js = <<< JS
$(function(){
    $("#registrationform-planid").trigger('change');
})
$(document).on('change', "#registrationform-planid", function(event){
    const id = $(this).val();
    $(".plan").addClass('d-none');
    $('#plan_' + id).removeClass('d-none');
})
JS;
$this->registerJs($js);
?>
