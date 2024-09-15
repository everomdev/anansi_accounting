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
                <th><?= Yii::t('app', "Ingredient") ?></th>
                <th><?= Yii::t('app', "Quantity") ?></th>
                <th><?= Yii::t('app', "Cost") ?></th>
                <th>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                            data-bs-target="#modal-add-ingredient">
                        <?= Yii::t('app', 'Add') ?>
                    </button>
                </th>
                </thead>
                <tbody>
                <?php foreach ($model->ingredientRelations as $ingredientStandardRecipe): ?>
                <?php
                    $cost = $ingredientStandardRecipe->lastUnitPrice * $ingredientStandardRecipe->quantity;
                    $total += $cost;
                    ?>
                    <tr>
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
                    $quantity =  $subStandardRecipe->getQuantityLinked($model->id);
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
                    <td colspan="2" class="text-center" style="font-weight: bold"><?= Yii::t('app', 'Total') ?></td>
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

