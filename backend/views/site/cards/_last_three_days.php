<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $movements \common\models\Movement[] */

$now = new DateTime();
$hoy = $now->format("Y-m-d");
$ayer = $now->modify("-1 day")->format('Y-m-d');
$antier = $now->modify("-1 day")->format('Y-m-d');


$antierTotal = array_sum(\yii\helpers\ArrayHelper::getColumn($business->getMovements()->andWhere(['DATE(created_at)' => $antier, 'type' => \common\models\Movement::TYPE_INPUT])->all(), 'total'));
$ayerTotal = array_sum(\yii\helpers\ArrayHelper::getColumn($business->getMovements()->andWhere(['DATE(created_at)' => $ayer, 'type' => \common\models\Movement::TYPE_INPUT])->all(), 'total'));;
$hoyTotal = array_sum(\yii\helpers\ArrayHelper::getColumn($business->getMovements()->andWhere(['DATE(created_at)' => $hoy, 'type' => \common\models\Movement::TYPE_INPUT])->all(), 'total'));;

?>
<div class="p-2">
    <div class="card bg-secondary text-white mb-3">
        <div class="card-header">
            <span class="card-title"><strong>Gastos</strong></span>
        </div>
        <div class="card-body">
            <table class="table text-white table-borderless">
                <tbody>
                <tr>
                    <td>Antier</td>
                    <td><?= $business->formatter->asCurrency($antierTotal) ?></td>
                </tr>
                <tr>
                    <td>Ayer</td>
                    <td><?= $business->formatter->asCurrency($ayerTotal) ?></td>
                </tr>
                <tr>
                    <td>Hoy</td>
                    <td><?= $business->formatter->asCurrency($hoyTotal) ?></td>
                </tr>

                </tbody>
            </table>
        </div>
    </div>
</div>
