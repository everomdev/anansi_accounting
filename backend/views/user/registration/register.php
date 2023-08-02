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
?>
<div class="vh-100 d-flex align-items-center justify-content-center">
    <div style="max-width: 500px">
        <?php $form = ActiveForm::begin(
            [
                'id' => $model->formName(),
                'enableAjaxValidation' => true,
                'enableClientValidation' => false,
            ]
        ); ?>
        <div class="card">
            <div class="card-header">
                <span class="card-title"><?= Html::encode($this->title) ?></span>
            </div>
            <div class="card-body">
                <div class="row gap-3">
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
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <?= Html::submitButton(Yii::t('usuario', 'Sign up'), ['class' => 'btn btn-success btn-block']) ?>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <?= Html::a(Yii::t('usuario', 'Already registered? Sign in!'), ['/user/security/login']) ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>


</div>
