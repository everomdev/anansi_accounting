<?php
/** @var $this \yii\web\View */

/** @var $model \common\models\StandardRecipe */

use yii\helpers\ArrayHelper;

$this->title = Yii::t('app', 'Create new recipe');
$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);

?>


<div class="row gap-3 mt-3">
    <div class="col-12">
        <table class="table">
            <thead class="bg-primary">
            <th class="text-white"><?= Yii::t('app', "Ingredient") ?></th>
            <th class="text-white"><?= Yii::t('app', "Quantity") ?></th>
            <th class="text-white"><?= Yii::t('app', "Last Price") ?></th>
            <th class="text-white"><?= Yii::t('app', "Avg Price") ?></th>
            <th class="text-white"><?= Yii::t('app', "Higher Price") ?></th>
            <th>
                <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal"
                        data-bs-target="#modal-add-ingredient">
                    <?= Yii::t('app', 'Add') ?>
                </button>
            </th>
            </thead>
            <tbody>
            <?php foreach ($model->ingredientRelations as $ingredientStandardRecipe): ?>
                <tr>
                    <td>
                        <?= $ingredientStandardRecipe->ingredient->ingredient ?>
                    </td>
                    <td>
                        <?= sprintf("%s %s", $ingredientStandardRecipe->quantity, $ingredientStandardRecipe->ingredient->portion_um) ?>
                    </td>
                    <td>
                        <?= $ingredientStandardRecipe->lastUnitPrice ?>
                    </td>
                    <td>
                        <?= $ingredientStandardRecipe->avgUnitPrice ?>
                    </td>
                    <td>
                        <?= $ingredientStandardRecipe->higherUnitPrice ?>
                    </td>

                    <td>
                        <?= \yii\bootstrap5\Html::a(Yii::t('app', "Remove"), \yii\helpers\Url::to(['standard-recipe/unselect-ingredient', 'id' => $model->id, 'ingredientId' => $ingredientStandardRecipe->ingredient_id]), [
                            'class' => "btn btn-sm btn-danger",
                            'data' => [
                                'method' => 'post',
                                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?')
                            ]
                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php foreach ($model->getSubStandardRecipes()->all() as $subStandardRecipe): ?>
                <tr>
                    <td>
                        <?= $subStandardRecipe->title ?>
                    </td>
                    <td>
                        <?= $subStandardRecipe->getQuantityLinked($model->id) ?>
                    </td>
                    <td>
                        <?= $subStandardRecipe->subRecipeLastPrice ?>
                    </td>
                    <td>
                        <?= $subStandardRecipe->subrecipeAvgPrice ?>
                    </td>
                    <td>
                        <?= $subStandardRecipe->subRecipeHigherPrice ?>
                    </td>

                    <td>
                        <?= \yii\bootstrap5\Html::a(Yii::t('app', "Remove"), \yii\helpers\Url::to(['standard-recipe/unselect-ingredient', 'id' => $model->id, 'ingredientId' => $subStandardRecipe->id, 'isRecipe' => true]), [
                            'class' => "btn btn-sm btn-danger",
                            'data' => [
                                'method' => 'post',
                                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?')
                            ]
                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="2" class="text-center" style="font-weight: bold"><?= Yii::t('app', 'Total') ?></td>
                <td><?= Yii::$app->formatter->asCurrency(
                        array_sum(
                            array_merge(
                                ArrayHelper::getColumn($model->ingredientRelations, 'lastUnitPrice'),
                                ArrayHelper::getColumn($model->getSubStandardRecipes()->all(), 'subRecipeLastPrice')
                            )
                        ), 'usd') ?></td>
                <td><?= Yii::$app->formatter->asCurrency(array_sum(
                        array_merge(
                            ArrayHelper::getColumn($model->ingredientRelations, 'avgUnitPrice'),
                            ArrayHelper::getColumn($model->getSubStandardRecipes()->all(), 'subRecipeAvgPrice')
                        )
                    ), 'usd') ?></td>
                <td><?= Yii::$app->formatter->asCurrency(array_sum(
                        array_merge(
                            ArrayHelper::getColumn($model->ingredientRelations, 'higherUnitPrice'),
                            ArrayHelper::getColumn($model->getSubStandardRecipes()->all(), 'subRecipeHigherPrice')
                        )
                    ), 'usd') ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>



