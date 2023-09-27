<?php
/** @var $this \yii\web\View */
/** @var $plan \common\models\Plan */

?>

<h5><?= Yii::t('app', "You are selecting plan: {plan}", ['plan' => $plan->name]) ?></h5>
*<?= Yii::t('app', "Nothing will be charged now, the charge will be made in the next period") ?>
<br>
<?php foreach (array_reverse($plan->getPrices()) as $price): ?>
    <?= \yii\bootstrap5\Html::a(Yii::t('app', "Pay {label}", [
        'label' => sprintf("%s %s / %s", ($price->unit_amount / 100), strtoupper($price->currency), $price->recurring->interval)
    ]), ['//payment/change-plan', 'price' => $price->id, 'plan' => $plan->id], [
        'class' => 'btn btn-success w-100 mb-3',
        'style' => "font-weight: bold"
    ]) ?>
<?php endforeach; ?>
