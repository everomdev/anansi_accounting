<?php
/** @var $this \yii\web\View */
/** @var $user User */

/** @var $plan \common\models\Plan */

use common\models\User;

?>

<div class="vh-100 d-flex justify-content-center align-items-center">
    <div style="max-width: 500px; min-width: 350px">
        <div class="card">
            <div class="card-body">
                <h4><?= Yii::t('app', "Enable susbcription to continue") ?></h4>
                <h5>
                    <?= "Plan {$plan->name}" ?>
                </h5>
                <div class="text-center">
                    <?php foreach (array_reverse($plan->getPrices()) as $price): ?>
                        <?= \yii\bootstrap5\Html::a(Yii::t('app', "Pay {label}", [
                            'label' => sprintf("%s %s / %s", ($price->unit_amount / 100), strtoupper($price->currency), $price->recurring->interval)
                        ]), ['//payment/create-checkout-session', 'price' => $price->id], [
                            'class' => 'btn btn-success w-100 mb-3',
                            'style' => "font-weight: bold"
                        ]) ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

