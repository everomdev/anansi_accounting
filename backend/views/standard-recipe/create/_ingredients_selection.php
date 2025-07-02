<?php
/** @var $this \yii\web\View */
/** @var $model \common\models\StandardRecipe */
/** @var $business \common\models\Business */

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
?>

<?php
$this->registerJsFile(Yii::getAlias("@web/js/standard-recipe/format-utils.js"), [
    'depends' => [\yii\web\YiiAsset::class]
]);
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
                <tbody>                <?php foreach ($model->ingredientRelations as $index => $ingredientStandardRecipe): 
                    $counter++; // Incrementamos el contador
                    $cost = (float)($ingredientStandardRecipe->lastUnitPrice * $ingredientStandardRecipe->quantity);
                    $total += $cost; // Usar += en lugar de asignación completa
                    ?>
                    <tr>
                        <td><?= $counter ?></td>
                        <td>
                            <?= $ingredientStandardRecipe->ingredient->ingredient ?>
                        </td>
                        <td>
                            <?= sprintf("%s %s", $ingredientStandardRecipe->quantity, $ingredientStandardRecipe->ingredient->portion_um) ?>
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
        'current' => $ingredientStandardRecipe->quantity,
        'id' => $ingredientStandardRecipe->ingredient_id,
        'is-recipe' => false,
        'name' => $ingredientStandardRecipe->ingredient->ingredient
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
                <?php endforeach; ?>                <?php foreach ($model->getSubStandardRecipes()->all() as $subStandardRecipe): 
                    $counter++; // Incrementamos el contador para las subrecetas
                    $quantity = (float)$subStandardRecipe->getQuantityLinked($model->id);
                    $cost = (float)($subStandardRecipe->custom_cost * $quantity);
                    $total += $cost; // Usar += en lugar de asignación completa
                    ?>
                    <tr>
                        <td><?= $counter ?></td>
                        <td>
                            <?= $subStandardRecipe->title ?>
                        </td>
                        <td>
                            <?= sprintf("%s %s", $quantity, $subStandardRecipe->um); ?>
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
                            <!-- Checkbox para excluir del costeo -->
                            <input type="checkbox" name="excludeFromCost[<?= $subStandardRecipe->id ?>]" class="exclude-checkbox">
                        </td>
                        <td>
                            <!-- Input para el porcentaje de costo -->
                            <input type="number" name="costPercentage[<?= $subStandardRecipe->id ?>]" min="0" max="100" step="1" class="form-control form-control-sm cost-percentage" style="width: 80px;" value="0">
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

<?php \yii\widgets\Pjax::end(); ?>