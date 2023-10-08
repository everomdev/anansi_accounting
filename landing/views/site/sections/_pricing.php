<?php

$plans = \common\models\Plan::find()->all();
?>
<div class="container-fluid py-5" id="precios">
    <div class="container py-5">
        <div class="text-center mb-5 wow fadeInUp" data-wow-delay=".3s">
            <h5 class="mb-2 px-3 py-1 text-dark rounded-pill d-inline-block border border-2 border-primary">Nuestros
                planes</h5>
            <h1 class="display-5 w-50 mx-auto">Elige el Plan que se Adapte a las Necesidades de tu Restaurante</h1>
        </div>
        <div class="row g-5">
            <?php foreach ($plans as $index => $plan): ?>
                <?= $this->render('_plan', ['index' => $index, 'plan' => $plan]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php foreach ($plans as $index => $plan): ?>
    <div class="container-fluid py-5" id="<?= "plan_$index" ?>">
        <div class="container py-5">
            <div class="text-start mb-5 wow fadeInUp" data-wow-delay=".3s">
                <h5 class="mb-2 px-3 py-1 text-dark rounded-pill d-inline-block border border-2 border-primary">
                    <?= $plan->name ?>
                </h5>
                <h5 class="display-6 mx-auto text-start"><?= "$ {$plan->monthly_price}/mes o $ {$plan->yearly_price}/año ({$plan->trial_days} días gratis)" ?></h5>
            </div>
            <div class="card border-0 shadow-sm" style="background-color: rgb(168,168,168, .3)">
                <div class="card-body">
                    <?= $plan->description ?>
                </div>
            </div>
            <a href="<?= Yii::$app->params['backendBaseUrl'] . "/user/register?plan={$plan->id}" ?>" class="btn btn-primary border-0 rounded-pill px-4 py-3 mt-3">¡Comenzar!</a>
        </div>
    </div>
<?php endforeach; ?>
