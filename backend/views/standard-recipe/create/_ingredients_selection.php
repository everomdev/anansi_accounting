<?php
/** @var $this \yii\web\View */
/** @var $model \common\models\StandardRecipe */
/** @var $business \common\models\Business */

use yii\helpers\ArrayHelper;

$business = \backend\helpers\RedisKeys::getBusiness();
$total = 0.0;
?>

<?php \yii\widgets\Pjax::begin([
    'id' => 'pjax-ingredients-selection',
    'timeout' => false
]) ?>
<div class="row gap-3 mt-3">
    <div class="col-12">
        <h4><?= Yii::t('app', "Ingredients") ?></h4>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <th></th>
                <th><?= Yii::t('app', "Ingredient") ?></th>
                <th><?= Yii::t('app', "Quantity") ?></th>
                <th><?= Yii::t('app', "Cost") ?></th>
                <th><?= Yii::t('app', "Excluído del costeo") ?></th> <!-- Nueva columna para excluir del costeo -->
                <th><?= Yii::t('app', "Porcentaje") ?></th> <!-- Nueva columna para el porcentaje de costo -->
                <th>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                            data-bs-target="#modal-add-ingredient">
                        <?= Yii::t('app', 'Add') ?>
                    </button>
                </th>
                </thead>
                <tbody>
                <?php foreach ($model->ingredientRelations as $index => $ingredientStandardRecipe): ?>
                <?php
                    $cost = $ingredientStandardRecipe->lastUnitPrice * $ingredientStandardRecipe->quantity;
                    $total += $cost;
                    ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td>
                            <?= $ingredientStandardRecipe->ingredient->ingredient ?>
                        </td>
                        <td>
                            <?= sprintf("%s %s", $ingredientStandardRecipe->quantity, $ingredientStandardRecipe->ingredient->portion_um) ?>
                        </td>
                        <td>
                            <?= $business->formatter->asCurrency($cost) ?>
                        </td>
                        <td>
                            <!-- Checkbox para excluir del costeo -->
                            <input type="checkbox" 
                                name="excludeFromCost[<?= $ingredientStandardRecipe->ingredient_id ?>]" 
                                class="exclude-checkbox"
                                data-ingredient-id="<?= $ingredientStandardRecipe->ingredient_id ?>"
                                <?= $ingredientStandardRecipe->exclude_from_cost ? 'checked' : '' ?>>
                        </td>
                        <td>
                            <!-- Input para el porcentaje de costo -->
                            <input type="number" 
                                   name="costPercentage[<?= $ingredientStandardRecipe->ingredient_id ?>]" 
                                   min="0" 
                                   max="100" 
                                   step="1" 
                                   class="form-control form-control-sm cost-percentage" 
                                   style="width: 80px;" 
                                   value="<?= $ingredientStandardRecipe->cost_percentage ?? 0 ?>">
                        </td>
                        <td>
                            <?= \yii\bootstrap5\Html::a(Yii::t('app', "Modify"), \yii\helpers\Url::to(['standard-recipe/update-selected-ingredient', 'id' => $model->id, 'ingredientId' => $ingredientStandardRecipe->ingredient_id]), [
                                'class' => "btn btn-sm btn-warning update-ingredient",
                                'data' => [
                                    'pjax' => "#pjax-ingredients-selection",
                                    'current' => $ingredientStandardRecipe->quantity
                                ]
                            ]) ?>
                            <?= \yii\bootstrap5\Html::a(Yii::t('app', "Remove"), \yii\helpers\Url::to(['standard-recipe/unselect-ingredient', 'id' => $model->id, 'ingredientId' => $ingredientStandardRecipe->ingredient_id]), [
                                'class' => "btn btn-sm btn-danger delete-ingredient",
                                'data' => [
                                    'confirm-message' => Yii::t('app', 'Are you sure you want to delete this item?'),
                                    'pjax' => "#pjax-ingredients-selection"
                                ]
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php foreach ($model->getSubStandardRecipes()->all() as $subStandardRecipe): ?>
                <?php
                    $quantity = $subStandardRecipe->getQuantityLinked($model->id);
                    $cost = $subStandardRecipe->custom_cost * $quantity;
                    $total += $cost;
                    ?>
                    <tr>
                        <td>
                            <?= $subStandardRecipe->title ?>
                        </td>
                        <td>
                            <?= sprintf("%s %s", $quantity, $subStandardRecipe->um); ?>
                        </td>
                        <td>
                            <?= $business->formatter->asCurrency($cost) ?>
                        </td>
                        <td>
                            <!-- Checkbox para excluir del costeo -->
                            <input type="checkbox" name="excludeFromCost[<?= $subStandardRecipe->id ?>]" class="exclude-checkbox">
                        </td>
                        <td>
                            <!-- Input para el porcentaje de costo -->
                            <input type="number" name="costPercentage[<?= $subStandardRecipe->id ?>]" min="0" max="100" step="1" class="form-control form-control-sm cost-percentage" style="width: 80px;" value="100">
                        </td>
                        <td>
                            <?= \yii\bootstrap5\Html::a(Yii::t('app', "Modify"), \yii\helpers\Url::to(['standard-recipe/update-selected-ingredient', 'id' => $model->id, 'ingredientId' => $subStandardRecipe->id, 'isRecipe' => true]), [
                                'class' => "btn btn-sm btn-warning update-ingredient",
                                'data' => [
                                    'pjax' => "#pjax-ingredients-selection",
                                    'current' => $quantity
                                ]
                            ]) ?>
                            <?= \yii\bootstrap5\Html::a(Yii::t('app', "Remove"), \yii\helpers\Url::to(['standard-recipe/unselect-ingredient', 'id' => $model->id, 'ingredientId' => $subStandardRecipe->id, 'isRecipe' => true]), [
                                'class' => "btn btn-sm btn-danger delete-ingredient",
                                'data' => [
                                    'confirm-message' => Yii::t('app', 'Are you sure you want to delete this item?'),
                                    'pjax' => "#pjax-ingredients-selection"
                                ]
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="4" class="text-center" style="font-weight: bold"><?= Yii::t('app', 'Total') ?></td>
                    <td>
                        <span id="ingredients-selection-total-cost"
                              data-total="<?= $total ?>"><?= $business->getFormatter()->asCurrency($total) ?></span>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php \yii\widgets\Pjax::end(); ?>

