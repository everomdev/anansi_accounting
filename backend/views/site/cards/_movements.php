<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $movements \common\models\Movement[] */

$movements = $business->getMovements()
->orderBy(['created_at' => SORT_DESC])
->limit(10)
->all();

?>
<div class="p-2">
    <div class="card bg-secondary text-white mb-3">
        <div class="card-header">
            <span class="card-title"><strong>Movimientos</strong></span>
        </div>
        <div class="card-body">
            <table class="table text-white table-borderless">
                <thead>
                <th class="text-white">Tipo</th>
                <th class="text-white">Insumo</th>
                <th class="text-white">Cantidad</th>
                <th class="text-white">Total</th>
                </thead>
                <tbody>
                <?php foreach ($movements as $movement): ?>
                    <tr>
                        <td><?= $movement->getFormattedType() ?></td>
                        <td><?= $movement->ingredient->ingredient ?></td>
                        <td><?= sprintf("%s %s", $movement->quantity, $movement->ingredient->um) ?></td>
                        <td><?= $business->formatter->asCurrency($movement->total) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
