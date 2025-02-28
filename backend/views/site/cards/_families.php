<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $families \common\models\RecipeCategory[] */

$families = $business->getRecipeCategories()->andWhere(['type' => \common\models\RecipeCategory::TYPE_MAIN])->all();

usort($families, function($a, $b){
    return $b->getRecipes()->count() - $a->getRecipes()->count();
});
?>
<div class="p-2">
    <div class="card bg-secondary text-white mb-3">
        <div class="card-header">
            <span class="card-title"><strong>Familias</strong></span>
        </div>
        <div class="card-body">
            <div class="table-responsive" >
                <table class="table text-white table-borderless">
                    <tbody>
                    <?php foreach ($families as $family): ?>
                        <tr>
                            <td><?= $family['name'] ?></td>
                            <td><?= $family->getRecipes()->count() ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>