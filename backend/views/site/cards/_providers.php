<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $providers \common\models\Provider[] */

$providers = $business->getProviders()->all();

usort($providers, function ($a, $b) {
    $purchasesA = $a->getMovements()->count();
    $purchasesB = $b->getMovements()->count();

    return $purchasesB - $purchasesA;
});

$providers = array_slice($providers, 0, 5);

?>
<div class="p-2">
    <div class="card bg-secondary text-white mb-3">
        <div class="card-header">
            <span class="card-title"><strong>Proveedores</strong></span>
        </div>
        <div class="card-body">
            <table class="table text-white table-borderless">
                <tbody>
                <?php foreach ($providers as $provider): ?>
                    <tr>
                        <td><?= $provider->name ?></td>
                        <td><?php
                            $total = array_sum(\yii\helpers\ArrayHelper::getColumn($provider->getMovements()->all(), 'total'));
                            echo $business->formatter->asCurrency($total);
                            ?></td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
        </div>
    </div>
</div>
