<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\data\ArrayDataProvider;
use common\models\IngredientStock;
use common\models\StandardRecipe;
use common\models\PendingField;
use backend\helpers\RedisKeys;

class PendingController extends Controller
{
    public function actionGlobal()
    {
        $business   = RedisKeys::getBusiness();
        $businessId = $business->id;

        // ── 1. Insumos pendientes ──────────────────────────────────────────────
        $ingredients        = IngredientStock::find()->where(['business_id' => $businessId])->all();
        $ingredientPendings = [];

        foreach ($ingredients as $ingredient) {
            $fields = PendingField::find()
                ->select('field')
                ->where(['model_type' => 'ingredient', 'model_id' => $ingredient->id])
                ->column();
            if (!empty($fields)) {
                $ingredientPendings[] = [
                    'id'             => $ingredient->id,
                    'name'           => $ingredient->ingredient,
                    'pending_fields' => $fields,
                ];
            }
        }

        // ── 2. Pre-cargar recipe_ingredient pendings para todas las recetas del negocio ──
        $allRecipeIds = StandardRecipe::find()
            ->select('id')
            ->where(['business_id' => $businessId])
            ->column();

        // Indexados por model_id (recipe id)
        $riPendingsByRecipe = [];
        if (!empty($allRecipeIds)) {
            $riRows = PendingField::find()
                ->where(['model_type' => 'recipe_ingredient', 'model_id' => $allRecipeIds])
                ->all();

            foreach ($riRows as $row) {
                // formato: "itemId:fieldName:isRecipe"
                $parts = explode(':', $row->field, 3);
                if (count($parts) < 2) continue;

                [$itemId, $fieldName] = $parts;
                $isRecipe = isset($parts[2]) ? (bool)(int)$parts[2] : false;

                $riPendingsByRecipe[$row->model_id][] = [
                    'item_id'   => $itemId,
                    'field'     => $fieldName,
                    'is_recipe' => $isRecipe,
                ];
            }
        }

        // Helper: obtener nombre del ítem (insumo o subreceta)
        $resolveItemName = function (string $itemId, bool $isRecipe): string {
            if ($isRecipe) {
                $sr = StandardRecipe::findOne($itemId);
                return $sr ? strtoupper($sr->title) : 'Subreceta ID:' . $itemId;
            }
            $ing = IngredientStock::findOne($itemId);
            return $ing ? $ing->ingredient : 'Insumo ID:' . $itemId;
        };

        // Helper: agrupar filas por ítem único dentro de una receta
        $groupIngredientRows = function (array $rows) use ($resolveItemName): array {
            $grouped = [];
            foreach ($rows as $r) {
                $key = $r['item_id'] . ':' . ($r['is_recipe'] ? '1' : '0');
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'item_id'        => $r['item_id'],
                        'is_recipe'      => $r['is_recipe'],
                        'item_name'      => $resolveItemName($r['item_id'], $r['is_recipe']),
                        'pending_fields' => [],
                    ];
                }
                $grouped[$key]['pending_fields'][] = $r['field'];
            }
            return array_values($grouped);
        };

        // ── 3. Recetas principales ─────────────────────────────────────────────
        $recipes        = StandardRecipe::find()
            ->where(['business_id' => $businessId, 'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN])
            ->all();
        $recipePendings = [];

        foreach ($recipes as $recipe) {
            $ownFields      = PendingField::find()
                ->select('field')
                ->where(['model_type' => 'recipe', 'model_id' => $recipe->id])
                ->column();
            $ingredientRows = $groupIngredientRows($riPendingsByRecipe[$recipe->id] ?? []);

            if (!empty($ownFields) || !empty($ingredientRows)) {
                $recipePendings[] = [
                    'id'              => $recipe->id,
                    'name'            => $recipe->title,
                    'pending_fields'  => $ownFields,       // campos propios de la receta
                    'ingredient_rows' => $ingredientRows,  // campos pendientes de sus ingredientes
                ];
            }
        }

        // ── 4. Subrecetas ─────────────────────────────────────────────────────
        $subrecipes        = StandardRecipe::find()
            ->where(['business_id' => $businessId, 'type' => StandardRecipe::STANDARD_RECIPE_TYPE_SUB])
            ->all();
        $subrecipePendings = [];

        foreach ($subrecipes as $sr) {
            // Los pendientes propios de subreceta usan model_type 'subrecipe'
            $ownFields      = PendingField::find()
                ->select('field')
                ->where(['model_type' => 'subrecipe', 'model_id' => $sr->id])
                ->column();
            $ingredientRows = $groupIngredientRows($riPendingsByRecipe[$sr->id] ?? []);

            if (!empty($ownFields) || !empty($ingredientRows)) {
                $subrecipePendings[] = [
                    'id'              => $sr->id,
                    'name'            => $sr->title,
                    'pending_fields'  => $ownFields,
                    'ingredient_rows' => $ingredientRows,
                ];
            }
        }

        return $this->render('//pending/global-pending', [
            'ingredientProvider' => new ArrayDataProvider([
                'allModels'  => $ingredientPendings,
                'pagination' => ['pageSize' => 20],
            ]),
            'recipeProvider' => new ArrayDataProvider([
                'allModels'  => $recipePendings,
                'pagination' => ['pageSize' => 20],
            ]),
            'subrecipeProvider' => new ArrayDataProvider([
                'allModels'  => $subrecipePendings,
                'pagination' => ['pageSize' => 20],
            ]),
            'counts' => [
                'ingredient' => count($ingredientPendings),
                'recipe'     => count($recipePendings),
                'subrecipe'  => count($subrecipePendings),
            ],
        ]);
    }
}