<?php
/** @var $model \common\models\Contact */

use yii\bootstrap5\ActiveForm;

?>

<div class="vh-100 d-flex justify-content-center align-items-center">
    <div style="max-width: 500px; min-width: 350px">
        <div class="card">

            <div class="card-body">
                <?php
                $form = ActiveForm::begin(
                    [
                        'enableAjaxValidation' => true,
                        'enableClientValidation' => false,
                    ]
                ) ?>
                <p>
                    ¿te interesaría una prueba gratis de nuestro sistema de costeo? Registrate y la siguiente semana te
                    lo haremos llegar
                </p>
                <div class="row gap-3">
                    <div class="col-12">
                        <?= $form->field(
                            $model,
                            'name',
                            ['inputOptions' => ['autofocus' => 'autofocus', 'class' => 'form-control', 'tabindex' => '1']]
                        ) ?>
                    </div>
                    <div class="col-12">
                        <?= $form->field(
                            $model,
                            'correo',
                            ['inputOptions' => ['autofocus' => 'autofocus', 'class' => 'form-control', 'tabindex' => '1']]
                        ) ?>
                    </div>
                    <div class="col-12">
                        <?= $form->field(
                            $model,
                            'whatsapp',
                            ['inputOptions' => ['autofocus' => 'autofocus', 'class' => 'form-control', 'tabindex' => '1', 'placeholder' => 'codigo de tu pais + numero']]
                        ) ?>
                    </div>
                    <div class="col-12">
                        <?= \yii\bootstrap5\Html::submitButton("Enviar",[
                                'class' => 'btn btn-success'
                        ]) ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>


    </div>

</div>
