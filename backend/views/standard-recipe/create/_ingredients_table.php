<?php
/** @var $this \yii\web\View */
/** @var $model \common\models\StandardRecipe */

use yii\helpers\ArrayHelper;

$business = \backend\helpers\RedisKeys::getBusiness();
$businessObj = \common\models\Business::findOne(['id' => $business['id']]);

// Obtener símbolo de moneda de manera más robusta
$currencySymbol = '$'; // Valor por defecto
try {
    if (!empty($businessObj->currency_code)) {
        $symbol = \Symfony\Component\Intl\Currencies::getSymbol(strtoupper($businessObj->currency_code));
        // Limpiar el símbolo si contiene letras (como 'MXN', 'USD', etc.)
        $cleanSymbol = preg_replace('/[a-zA-Z]/', '', $symbol);
        if (!empty($cleanSymbol)) {
            $currencySymbol = $cleanSymbol;
        }
    }
} catch (Exception $e) {
    // Si hay error, mantener el símbolo por defecto
    $currencySymbol = '$';
}

// Si el símbolo está vacío después de la limpieza, usar el símbolo por defecto
if (empty($currencySymbol)) {
    $currencySymbol = '$';
}

// Configuración para formato de números
$formatConfig = [
    'decimalSeparator' => $businessObj->decimal_separator,
    'thousandSeparator' => $businessObj->thousands_separator,
    'currencySymbol' => $currencySymbol,
];
$this->registerJsVar('userFormatConfig', $formatConfig);

$total = 0.0;
$counter = 0; // Inicializamos el contador en 0

// Obtener los campos pendientes de ingredientes para esta receta
$pendingIngredientFields = \common\models\PendingField::find()
    ->select('field')
    ->where([
        'model_type' => 'recipe_ingredient',
        'model_id' => $model->id
    ])->column();
// Convertir a un array asociativo para lookup rápido
$pendingIngredientFieldsAssoc = array_flip($pendingIngredientFields);

$this->registerJsFile(Yii::getAlias("@web/js/standard-recipe/format-utils.js"), [
    'depends' => [\yii\web\YiiAsset::class]
]);
?>

<div class="row gap-3 mt-3">
    <div class="col-12">
        <h4><?= Yii::t('app', "Ingredients") ?></h4>
        <input type="hidden" id="pendingFieldsRecipeIngredients" name="pendingFieldsRecipeIngredients" value="">
        <div class="table-responsive">
            <table class="table">
                <thead>
                <th></th>
                <th>
                    <?= Yii::t('app', "Ingredient") ?>
                </th>
                <th>
                    <?= Yii::t('app', "Quantity") ?>
                </th>
                <th><?= Yii::t('app', "Cost") ?></th>
                <th>
                    <?= Yii::t('app', "Excluído del costeo") ?>
                </th>
                <th>
                    <?= Yii::t('app', "Porcentaje") ?>
                </th>
                <th>
                    <button type="button" class="btn btn-sm btn-primary" id="btn-open-add-ingredient" data-bs-toggle="modal"
                            data-bs-target="#modal-add-ingredient">
                        <?= Yii::t('app', 'Add') ?>
                    </button>
                </th>
                </thead>
                <?php foreach ($model->ingredientRelations as $index => $ingredientStandardRecipe):
                    $counter++;
                    $cost = (float)($ingredientStandardRecipe->lastUnitPrice * $ingredientStandardRecipe->quantity);
                    $total += $cost;
                ?>
                    <tr>
                        <td><?= $counter ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span><?= $ingredientStandardRecipe->ingredient->ingredient ?></span>
                                <input type="checkbox"
                                    class="form-check-input ms-2 pending-checkbox d-none"
                                    data-field="ingredient"
                                    data-ingredient-id="<?= $ingredientStandardRecipe->ingredient_id ?>"
                                    data-is-recipe="0"
                                    title="Marcar ingrediente como pendiente"
                                    <?php $key = $ingredientStandardRecipe->ingredient_id . ':ingredient:0'; if (isset($pendingIngredientFieldsAssoc[$key])) echo 'checked'; ?>
                                >
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span><?= sprintf("%s %s", $ingredientStandardRecipe->quantity, $ingredientStandardRecipe->ingredient->portion_um) ?></span>
                                <input type="checkbox"
                                    class="form-check-input ms-2 pending-checkbox d-none"
                                    data-field="quantity"
                                    data-ingredient-id="<?= $ingredientStandardRecipe->ingredient_id ?>"
                                    data-is-recipe="0"
                                    title="Marcar cantidad como pendiente"
                                    <?php $key = $ingredientStandardRecipe->ingredient_id . ':quantity:0'; if (isset($pendingIngredientFieldsAssoc[$key])) echo 'checked'; ?>
                                >
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="currency-symbol me-1"><?= $currencySymbol ?></span>
                                <span class="ingredient-cost"
                                      id="ingredient-cost-<?= $ingredientStandardRecipe->ingredient_id ?>"
                                      data-raw-value="<?= $cost ?>">
                                    <?= number_format($cost, 2, $businessObj->decimal_separator, $businessObj->thousands_separator) ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <input type="checkbox"
                                    name="excludeFromCost[<?= $ingredientStandardRecipe->ingredient_id ?>]"
                                    class="exclude-checkbox"
                                    data-ingredient-id="<?= $ingredientStandardRecipe->ingredient_id ?>"
                                    <?= $ingredientStandardRecipe->exclude_from_cost ? 'checked' : '' ?>>
                                <input type="checkbox"
                                    class="form-check-input ms-2 pending-checkbox d-none"
                                    data-field="exclude_from_cost"
                                    data-ingredient-id="<?= $ingredientStandardRecipe->ingredient_id ?>"
                                    data-is-recipe="0"
                                    title="Marcar exclusión del costeo como pendiente"
                                    <?php $key = $ingredientStandardRecipe->ingredient_id . ':exclude_from_cost:0'; if (isset($pendingIngredientFieldsAssoc[$key])) echo 'checked'; ?>
                                >
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <input type="number"
                                       name="costPercentage[<?= $ingredientStandardRecipe->ingredient_id ?>]"
                                       min="0"
                                       max="100"
                                       step="1"
                                       class="form-control form-control-sm cost-percentage"
                                       style="width: 80px;"
                                       value="<?= $ingredientStandardRecipe->cost_percentage ?? 0 ?>">
                                <input type="checkbox"
                                    class="form-check-input ms-2 pending-checkbox d-none"
                                    data-field="cost_percentage"
                                    data-ingredient-id="<?= $ingredientStandardRecipe->ingredient_id ?>"
                                    data-is-recipe="0"
                                    title="Marcar porcentaje como pendiente"
                                    <?php $key = $ingredientStandardRecipe->ingredient_id . ':cost_percentage:0'; if (isset($pendingIngredientFieldsAssoc[$key])) echo 'checked'; ?>
                                >
                            </div>
                        </td>
                        <td>
<?= \yii\bootstrap5\Html::a(Yii::t('app', "Modify"), \yii\helpers\Url::to(['standard-recipe/update-selected-ingredient', 'id' => $model->id, 'ingredientId' => $ingredientStandardRecipe->ingredient_id]), [
    'class' => "btn btn-sm btn-warning update-ingredient",
    'data' => [
        'pjax' => "#pjax-ingredients-selection",
        'current' => $ingredientStandardRecipe->quantity,
        'id' => $ingredientStandardRecipe->ingredient_id,
        'is-recipe' => false,
        'name' => $ingredientStandardRecipe->ingredient->ingredient
    ]
]) ?>
                            <?= \yii\bootstrap5\Html::a(Yii::t('app', "Remove"), 'javascript:void(0);', [
                                'class' => "btn btn-sm btn-danger delete-ingredient",
                                'data-url' => \yii\helpers\Url::to(['standard-recipe/unselect-ingredient', 'id' => $model->id, 'ingredientId' => $ingredientStandardRecipe->ingredient_id]),
                                'data-pjax' => "#pjax-ingredients-selection"
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php foreach ($model->getSubStandardRecipes()->all() as $subStandardRecipe):
                    $counter++;
                    $quantity = (float)$subStandardRecipe->getQuantityLinked($model->id);
                    $cost = (float)($subStandardRecipe->custom_cost * $quantity);
                    $total += $cost;
                ?>
                    <tr>
                        <td><?= $counter ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span><?= $subStandardRecipe->title ?></span>
                                <input type="checkbox"
                                    class="form-check-input ms-2 pending-checkbox d-none"
                                    data-field="ingredient"
                                    data-ingredient-id="<?= $subStandardRecipe->id ?>"
                                    data-is-recipe="1"
                                    title="Marcar subreceta como pendiente"
                                    <?php $key = $subStandardRecipe->id . ':ingredient:1'; if (isset($pendingIngredientFieldsAssoc[$key])) echo 'checked'; ?>
                                >
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span><?= sprintf("%s %s", $quantity, $subStandardRecipe->um); ?></span>
                                <input type="checkbox"
                                    class="form-check-input ms-2 pending-checkbox d-none"
                                    data-field="quantity"
                                    data-ingredient-id="<?= $subStandardRecipe->id ?>"
                                    data-is-recipe="1"
                                    title="Marcar cantidad como pendiente"
                                    <?php $key = $subStandardRecipe->id . ':quantity:1'; if (isset($pendingIngredientFieldsAssoc[$key])) echo 'checked'; ?>
                                >
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="currency-symbol me-1"><?= $currencySymbol ?></span>
                                <span class="subrecipe-cost"
                                      id="subrecipe-cost-<?= $subStandardRecipe->id ?>"
                                      data-raw-value="<?= $cost ?>">
                                    <?= number_format($cost, 2, $businessObj->decimal_separator, $businessObj->thousands_separator) ?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <input type="checkbox" name="excludeFromCost[<?= $subStandardRecipe->id ?>]" class="exclude-checkbox">
                                <input type="checkbox"
                                    class="form-check-input ms-2 pending-checkbox d-none"
                                    data-field="exclude_from_cost"
                                    data-ingredient-id="<?= $subStandardRecipe->id ?>"
                                    data-is-recipe="1"
                                    title="Marcar exclusión del costeo como pendiente"
                                    <?php $key = $subStandardRecipe->id . ':exclude_from_cost:1'; if (isset($pendingIngredientFieldsAssoc[$key])) echo 'checked'; ?>
                                >
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <input type="number" name="costPercentage[<?= $subStandardRecipe->id ?>]" min="0" max="100" step="1" class="form-control form-control-sm cost-percentage" style="width: 80px;" value="0">
                                <input type="checkbox"
                                    class="form-check-input ms-2 pending-checkbox d-none"
                                    data-field="cost_percentage"
                                    data-ingredient-id="<?= $subStandardRecipe->id ?>"
                                    data-is-recipe="1"
                                    title="Marcar porcentaje como pendiente"
                                    <?php $key = $subStandardRecipe->id . ':cost_percentage:1'; if (isset($pendingIngredientFieldsAssoc[$key])) echo 'checked'; ?>
                                >
                            </div>
                        </td>
                        <td>
                            <?= \yii\bootstrap5\Html::a(Yii::t('app', "Modify"), \yii\helpers\Url::to(['standard-recipe/update-selected-ingredient', 'id' => $model->id, 'ingredientId' => $subStandardRecipe->id, 'isRecipe' => true]), [
                                'class' => "btn btn-sm btn-warning update-ingredient",
                                'data' => [
                                    'pjax' => "#pjax-ingredients-selection",
                                    'current' => $quantity,
                                    'id' => $subStandardRecipe->id,
                                    'is-recipe' => 1,
                                    'name' => $subStandardRecipe->title
                                ]
                            ]) ?>
                            <?= \yii\bootstrap5\Html::a(Yii::t('app', "Remove"), 'javascript:void(0);', [
                                'class' => "btn btn-sm btn-danger delete-ingredient",
                                'data-url' => \yii\helpers\Url::to(['standard-recipe/unselect-ingredient', 'id' => $model->id, 'ingredientId' => $subStandardRecipe->id, 'isRecipe' => true]),
                                'data-pjax' => "#pjax-ingredients-selection"
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" class="text-end" style="font-weight: bold"><?= Yii::t('app', 'Total') ?></td>

                    <td>
                        <div class="d-flex align-items-center">
                            <span class="currency-symbol me-1"><?= $currencySymbol ?></span>
                            <span id="ingredients-selection-total-cost"
                                  data-raw-value="<?= $total ?>"
                                  data-format-config='<?= json_encode($formatConfig) ?>'>
                                <?= number_format($total, 2, $businessObj->decimal_separator, $businessObj->thousands_separator) ?>
                            </span>
                        </div>
                    </td>
                    <td colspan="3"></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
