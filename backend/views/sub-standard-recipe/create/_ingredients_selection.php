<?php
/** @var $this \yii\web\View */
/** @var $model \common\models\StandardRecipe */

use yii\helpers\ArrayHelper;

$this->title = Yii::t('app', 'Create new sub recipe');
$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$stock = \yii\helpers\ArrayHelper::map(
    (new \yii\db\Query())
        ->select(['i.*', "CONCAT(i.ingredient, ' (',i.portion_um,')') as label"])
        ->from('ingredient_stock i')
        ->leftJoin('ingredient_standard_recipe isr', 'i.id=isr.ingredient_id')
        ->leftJoin('standard_recipe sr', 'isr.standard_recipe_id = sr.id')
        ->where(['or', ['sr.id' => null], ['<>', 'sr.id', $model->id]])
        ->andWhere(['i.business_id' => $business['id']])
        ->all(),
    'id', 'label'
);

$subRecipes = \yii\helpers\ArrayHelper::map(
    (new \yii\db\Query())
        ->select(["id", "sr.title as label"])
        ->from("standard_recipe sr")
        ->where([
            'sr.business_id' => $business['id'],
            'sr.type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB
        ])
        ->andWhere(['not', ['id' => $model->id]])
        ->all(),
    'id', 'label'
);



?>


<div class="card">
    <div class="card-header">
        <span class="card-title">
            <?= Yii::t('app', "Add ingredients") ?>
        </span>
    </div>
    <div class="card-body">
        <div class="row gap-3">
            <div class="col-12">
                <table class="table">
                    <thead>
                    <th><?= Yii::t('app', "Ingredient") ?></th>
                    <th><?= Yii::t('app', "Quantity") ?></th>
                    <th><?= Yii::t('app', "Last Price") ?></th>
                    <th><?= Yii::t('app', "Avg Price") ?></th>
                    <th><?= Yii::t('app', "Higher Price") ?></th>
                    <th>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
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
    </div>
    <div class="card-footer">
        <?= \yii\bootstrap5\Html::a(
            Yii::t('app', 'Continue'),
            \yii\helpers\Url::to(['sub-standard-recipe/finish-recipe-creation', 'id' => $model->id]), [
            'class' => 'btn btn-success'
        ]) ?>
    </div>
</div>

<?php
\yii\bootstrap5\Modal::begin([
    'title' => Yii::t('app', 'Add ingredient'),
    'id' => 'modal-add-ingredient',

]);

echo $this->render('_form_ingredient', ['stock' => $stock, 'recipe' => $model, 'model' => new \backend\models\StandardRecipeIngredientForm(), 'subRecipes' => $subRecipes]);

\yii\bootstrap5\Modal::end();
?>
