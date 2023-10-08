<?php
/** @var $this \yii\web\View */
/** @var $plan \common\models\Plan */
/** @var $index integer */

?>

<div class="col-lg-4 col-md-6 col-sm-12 wow fadeInUp" data-wow-delay=".3s">
    <div class="rounded pricing-item bg-light">
        <div class="<?= $index % 2 == 0 ? 'bg-primary' : 'bg-dark' ?> py-3 px-5 text-center rounded-top border-bottom border-dark">
            <h2 class="<?= $index % 2 == 0 ? '' : 'text-primary' ?> m-0"><?= $plan->name ?></h2>
        </div>
        <div class="px-4 py-5 text-center <?= $index % 2 == 0 ? 'bg-primary' : 'bg-dark' ?> pricing-label mb-2">
            <h1 class="<?= $index % 2 == 0 ? '' : 'text-primary' ?> mb-0">$<?= $plan->monthly_price ?><span class="<?= $index % 2 == 0 ? 'text-secondary' : 'text-primary' ?> fs-5 fw-normal">/mes</span></h1>
            <h1 class="<?= $index % 2 == 0 ? '' : 'text-primary' ?> mb-0">$<?= $plan->yearly_price ?><span class="<?= $index % 2 == 0 ? 'text-secondary' : 'text-primary' ?> fs-5 fw-normal">/año</span></h1>

        </div>
        <div class="p-4 text-start fs-5">
            <?= $plan->intro ?>
            <a href="#<?= "plan_$index" ?>" class="btn btn-primary border-0 rounded-pill px-4 py-3 mt-3">Ver mas</a>
        </div>
    </div>
</div>
