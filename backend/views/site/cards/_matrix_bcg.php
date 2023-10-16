<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $families \common\models\RecipeCategory[] */

$families = $business->getRecipeCategories()->andWhere(['type' => \common\models\RecipeCategory::TYPE_MAIN])->all();

$data = [];

?>
<?php foreach ($families as $family): ?>

    <?php
    $bcgData = $business->getBcgData($family->name);
    $totalCostEffectiveness = 0;
    array_walk($bcgData['data'], function ($item) use (&$totalCostEffectiveness) {
        $totalCostEffectiveness += ($item->sales * $item->price) - ($item->sales * $item->cost);
    });
    if (!empty($bcgData['totalSales'])) {
        $popularityAxis = round(0.7 * (100 / $bcgData['totalSales']) * 100, 2);
        $costEffectivenessAxis = round($totalCostEffectiveness / $bcgData['totalSales'], 2);
    } else {
        $popularityAxis = 0;
        $costEffectivenessAxis = 0;
    }
    $estrella = 0;
    $vaca = 0;
    $perro = 0;
    $rata = 0;

    foreach ($bcgData['data'] as $item) {
        if ($item instanceof \common\models\StandardRecipe) {
            /** @var $item \common\models\StandardRecipe */
            $quadrant = $item->getBcg($popularityAxis, $costEffectivenessAxis, $bcgData['totalSales']);
            switch ($quadrant) {
                case 'ESTRELLA':
                    $estrella++;
                    break;
                case 'VACA':
                    $vaca++;
                    break;
                case 'PERRO':
                    $perro++;
                    break;
                case 'ENIGMA':
                    $rata++;
                    break;
            }
        }
    }
    $data[$family->name] = [$estrella, $vaca, $perro, $rata];
    ?>
<?php endforeach; ?>
<div class="p-2">
    <div class="card bg-secondary text-white mb-3">
        <div class="card-header">
            <span class="card-title"><strong><?= "Matríz BCG" ?></strong></span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table text-white table-borderless">
                    <thead>
                    <th class="text-white">Familia</th>
                    <th class="text-white">Estrella</th>
                    <th class="text-white">Vaca</th>
                    <th class="text-white">Perro</th>
                    <th class="text-white">Rata</th>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $family => $bcg): ?>
                        <tr>
                            <td><?= $family ?></td>
                            <td><?= $bcg[0] ?></td>
                            <td><?= $bcg[1] ?></td>
                            <td><?= $bcg[2] ?></td>
                            <td><?= $bcg[3] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
