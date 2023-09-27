<?php

/** @var $balances \common\models\Balance[] */
$business = \backend\helpers\RedisKeys::getBusiness();
$model = new \common\models\Balance();
?>

<?php $form = \yii\bootstrap5\ActiveForm::begin([
    'id' => 'form-balance',
    'action' => \yii\helpers\Url::to(['movement/register-balance']),
    'enableAjaxValidation' => true
]) ?>

<?= $form->field($model, 'current_balance')->input('number', ['step' => 'any']) ?>

<?= \yii\bootstrap5\Html::submitButton(Yii::t('app', "Register"), [
    'class' => 'btn btn-success'
]) ?>

<?php \yii\bootstrap5\ActiveForm::end(); ?>


<div class="table-responsive">
    <table class="table">
        <thead>
        <th><?= Yii::t('app', 'Date') ?></th>
        <th><?= Yii::t('app', 'Initial balance') ?></th>
        <th><?= Yii::t('app', 'Expenses') ?></th>
        </thead>
        <tbody>
        <?php foreach ($balances as $balance): ?>
            <tr>
                <td><?= $business->getFormatter()->asDate($balance->date) ?></td>
                <td><?= $business->getFormatter()->asCurrency($balance->current_balance) ?></td>
                <td><?= $business->getFormatter()->asCurrency($balance->expense) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

