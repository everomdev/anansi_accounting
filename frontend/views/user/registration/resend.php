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
 * @var yii\web\View $this
 * @var \Da\User\Form\ResendForm $model
 */
\backend\assets\SneatAsset::register($this);
$this->title = Yii::t('usuario', 'Request new confirmation message');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="vh-100 d-flex align-items-center justify-content-center">
    <div style="min-width: 350px; max-width: 500px">
        <div class="card">
            <div class="card-body">
                <?php $form = ActiveForm::begin(
                    [
                        'id' => $model->formName(),
                        'enableAjaxValidation' => true,
                        'enableClientValidation' => false,
                    ]
                ); ?>
                <div class="row gap-3">
                    <div class="col-12">
                        <?= $form->field($model, 'email')->textInput(['autofocus' => true]) ?>
                    </div>
                    <div class="col-12">
                        <?= Html::submitButton(Yii::t('usuario', 'Continue'), ['class' => 'btn btn-primary btn-block']) ?>
                        <br>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
