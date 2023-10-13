<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */

?>
<div class="p-2">
    <div class="card bg-secondary text-white mb-3">
        <div class="card-header">
            <span class="card-title"><strong>Numero de subrecetas, recetas y combos</strong></span>
        </div>
        <div class="card-body">
            <table class="table text-white table-borderless">
                <tbody>
                <tr>
                    <td>Recetas</td>
                    <td><?= $business->getStandardRecipes()->andWhere(['type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN, 'in_construction' => false])->count() ?></td>
                </tr>
                <tr>
                    <td>Subrecetas</td>
                    <td><?= $business->getStandardRecipes()->andWhere(['type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB, 'in_construction' => false])->count() ?></td>
                </tr>
                <tr>
                    <td>Combos</td>
                    <td><?= $business->getMenus()->count() ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
