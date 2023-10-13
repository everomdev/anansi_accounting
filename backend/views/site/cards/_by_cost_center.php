<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $consumptionCenters \common\models\ConsumptionCenter[] */

$consumptionCenters = $business->getConsumptionCenters()->all();

?>
<div class="p-2">
    <div class="card bg-secondary text-white mb-3">
        <div class="card-header">
            <span class="card-title"><strong>Consumo por centro de costo</strong></span>
        </div>
        <div class="card-body">
            <table class="table text-white table-borderless">
                <thead>
                <th class="text-white">Centro</th>
                <th class="text-white">Monto</th>
                </thead>
                <tbody>
                <?php foreach ($consumptionCenters as $consumptionCenter): ?>
                    <tr>
                        <td><?= $consumptionCenter->name ?></td>
                        <td>
                            <?php
                            $movements = $business->getMovements()
                                ->where([
                                    'type' => \common\models\Movement::TYPE_OUTPUT,
                                    'provider' => $consumptionCenter->name
                                ])->all();

                            echo $business->formatter->asCurrency(array_sum(\yii\helpers\ArrayHelper::getColumn($movements, function($movement){
                                return $movement->quantity * $movement->ingredient->lastPrice;
                            })));
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>


                </tbody>
            </table>
        </div>
    </div>
</div>
