<?php

use yii\db\Migration;

class m251201_012453_update_procedure extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Drop the existing procedure
        $this->execute("DROP PROCEDURE IF EXISTS get_theoretical_yield_summary");

        // Create the corrected procedure
        $this->execute("
            CREATE PROCEDURE get_theoretical_yield_summary(IN businessId INT)
            BEGIN
                -- Tabla temporal para resultados
                CREATE TEMPORARY TABLE temp_yield AS
                SELECT
                    'recipe' AS item_type,
                    sr.is_food,
                    -- Calcular costPercent: recipeLastPrice / price
                    CASE 
                        WHEN sr.price > 0 THEN (
                            -- Calcular recipeLastPrice: suma de ingredientes + subrecetas + convoy, dividido por portions * yield si aplica
                            CASE
                                WHEN sr.portions > 0 AND sr.yield > 0 THEN (
                                    COALESCE((
                                        SELECT SUM(
                                            isr.quantity * (
                                                SELECT COALESCE(sp.adjusted_price, 0)
                                                FROM stock_price sp
                                                WHERE sp.stock_id = isr.ingredient_id
                                                ORDER BY sp.date DESC
                                                LIMIT 1
                                            )
                                        )
                                        FROM ingredient_standard_recipe isr
                                        WHERE isr.standard_recipe_id = sr.id
                                    ), 0) +
                                    COALESCE((
                                        SELECT SUM(
                                            CASE 
                                                WHEN sub_sr.custom_cost IS NOT NULL THEN srssr.quantity * sub_sr.custom_cost
                                                ELSE 0
                                            END
                                        )
                                        FROM standard_recipe_sub_standard_recipe srssr
                                        LEFT JOIN standard_recipe sub_sr ON srssr.sub_standard_recipe_id = sub_sr.id
                                        WHERE srssr.standard_recipe_id = sr.id
                                    ), 0)
                                ) / (sr.portions * sr.yield)
                                ELSE (
                                    COALESCE((
                                        SELECT SUM(
                                            isr.quantity * (
                                                SELECT COALESCE(sp.adjusted_price, 0)
                                                FROM stock_price sp
                                                WHERE sp.stock_id = isr.ingredient_id
                                                ORDER BY sp.date DESC
                                                LIMIT 1
                                            )
                                        )
                                        FROM ingredient_standard_recipe isr
                                        WHERE isr.standard_recipe_id = sr.id
                                    ), 0) +
                                    COALESCE((
                                        SELECT SUM(
                                            CASE 
                                                WHEN sub_sr.custom_cost IS NOT NULL THEN srssr.quantity * sub_sr.custom_cost
                                                ELSE 0
                                            END
                                        )
                                        FROM standard_recipe_sub_standard_recipe srssr
                                        LEFT JOIN standard_recipe sub_sr ON srssr.sub_standard_recipe_id = sub_sr.id
                                        WHERE srssr.standard_recipe_id = sr.id
                                    ), 0)
                                )
                            END
                        ) / sr.price
                        ELSE 0
                    END AS cost_percent
                FROM standard_recipe sr
                WHERE sr.business_id = businessId
                  AND sr.in_construction = 0
                  AND sr.type = 'main'
                  AND sr.in_menu = 1

                UNION ALL

                SELECT
                    'combo' AS item_type,
                    NULL AS is_food,
                    m.cost_percent_last_price AS cost_percent
                FROM menu m
                WHERE m.business_id = businessId
                  AND m.in_menu = 1;

                -- Devolver promedios agrupados
                SELECT
                    item_type,
                    is_food,
                    SUM(cost_percent) AS total_cost_sum,
                    COUNT(*) AS total_count
                FROM temp_yield
                GROUP BY item_type, is_food;

                -- Limpiar tabla temporal
                DROP TEMPORARY TABLE temp_yield;
            END
        ");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop the procedure
        $this->execute("DROP PROCEDURE IF EXISTS get_theoretical_yield_summary");
    }
}
