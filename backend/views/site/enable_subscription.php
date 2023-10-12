<?php
/** @var $this \yii\web\View */
/** @var $user User */

/** @var $plan \common\models\Plan */

use common\models\User;

$this->title = "Habilitar suscripción";

?>

<div class="vh-100 d-flex justify-content-center align-items-center">
    <div style="min-width: 350px; max-width: 1024px">
        <div class="card m-5">
            <div class="card-body">
                <h4><?= Yii::t('app', "Enable susbcription to continue") ?></h4>
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6" style="max-height: 500px; overflow: auto">
                        <h5><?= "Plan {$plan->name}" ?></h5>
                        <?= $plan->description ?>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 d-flex justify-content-center">
                        <div class="d-flex flex-column justify-content-center align-items-center">
                            <div class="col text-center">
                                <div style="max-width: 400px">
                                    <img src="<?= Yii::getAlias("@web/images/img_enable_subscription.png") ?>" class="w-100" alt="" style="object-fit: scale-down">
                                </div>
                            </div>
                            <div class="col text-center">
                                <?php foreach (array_reverse($plan->getPrices()) as $price): ?>
                                    <?= \yii\bootstrap5\Html::a(Yii::t('app', "Pay {label}", [
                                        'label' => sprintf("$%s %s / %s", ($price->unit_amount / 100), strtoupper($price->currency), Yii::t('app', $price->recurring->interval))
                                    ]), ['//payment/create-checkout-session', 'price' => $price->id], [
                                        'class' => 'btn btn-warning w-100 mb-3',
                                        'style' => "font-weight: bold; max-width: 230px"
                                    ]) ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>

