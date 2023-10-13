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
use Da\User\Widget\ConnectWidget;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var \Da\User\Form\LoginForm $model
 * @var \Da\User\Module $module
 */

\backend\assets\SneatAsset::register($this);

$this->title = Yii::t('usuario', 'Sign in');
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="vh-100 d-flex justify-content-center align-items-center">
    <div style="max-width: 500px; min-width: 350px">
        <div class="text-center">
            <img src="<?= Yii::getAlias("@web/images/logo.png") ?>" alt="" width="300">
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            </div>
            <div class="card-body">
                <?php $form = ActiveForm::begin(
                    [
                        'id' => $model->formName(),
                        'enableAjaxValidation' => true,
                        'enableClientValidation' => false,
                        'validateOnBlur' => false,
                        'validateOnType' => false,
                        'validateOnChange' => false,
                    ]
                ) ?>

                <div class="row gap-3">
                    <div class="col-12">
                        <?= $form->field(
                            $model,
                            'login',
                            ['inputOptions' => ['autofocus' => 'autofocus', 'class' => 'form-control', 'tabindex' => '1']]
                        ) ?>




                    </div>
                    <div class="col-12">
                        <?= $form
                            ->field(
                                $model,
                                'password',
                                ['inputOptions' => ['class' => 'form-control', 'tabindex' => '2']]
                            )
                            ->passwordInput()
                            ->label(
                                Yii::t('usuario', 'Password')
                                . ($module->allowPasswordRecovery ?
                                    ' (' . Html::a(
                                        Yii::t('usuario', 'Forgot password?'),
                                        ['/user/recovery/request'],
                                        ['tabindex' => '5']
                                    )
                                    . ')' : '')
                            ) ?>
                    </div>
                    <div class="col-12">
                        <?= $form->field($model, 'rememberMe')->checkbox(['tabindex' => '4']) ?>
                    </div>
                    <div class="col-12">
                        <?= Html::submitButton(
                            Yii::t('usuario', 'Sign in'),
                            ['class' => 'btn btn-primary btn-block', 'tabindex' => '3']
                        ) ?>
                    </div>
                    <div class="col-12">
                        <?php if ($module->enableEmailConfirmation): ?>
                            <p class="text-center">
                                <?= Html::a(
                                    Yii::t('usuario', 'Didn\'t receive confirmation message?'),
                                    ['/user/registration/resend']
                                ) ?>
                            </p>
                        <?php endif ?>
                        <?php if ($module->enableRegistration): ?>
                            <p class="text-center">
                                <?= Html::a(Yii::t('usuario', 'Don\'t have an account? Sign up!'), ['/user/registration/register']) ?>
                            </p>
                        <?php endif ?>
                    </div>
                </div>



                <?php ActiveForm::end(); ?>
            </div>
        </div>


    </div>

</div>
