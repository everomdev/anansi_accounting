<?php
/**
 * Script de consola para probar el sistema de control de inventario
 * Ejecutar con: php yii test-inventory/run
 */

namespace console\controllers;

use Yii;
use yii\console\Controller;
use common\models\IngredientStock;

class TestInventoryController extends Controller
{
    public function actionRun()
    {
        echo "=== PRUEBA DEL SISTEMA DE CONTROL DE INVENTARIO ===\n\n";

        // Buscar un ingrediente existente para probar
        $ingredient = IngredientStock::find()->one();

        if ($ingredient) {
            echo "Probando con ingrediente: " . $ingredient->name . "\n";
            echo "Stock actual: " . $ingredient->stock . "\n";
            
            // Probar configuración de límites
            $ingredient->min_stock = 10;
            $ingredient->max_stock = 100;
            
            if ($ingredient->save()) {
                echo "✅ Límites de stock configurados correctamente\n";
                echo "Stock mínimo: " . $ingredient->min_stock . "\n";
                echo "Stock máximo: " . $ingredient->max_stock . "\n";
                
                // Probar métodos de validación
                echo "\n--- Pruebas de validación ---\n";
                
                // Simular stock bajo
                $originalStock = $ingredient->stock;
                $ingredient->stock = 5;
                echo "Stock simulado bajo (5): " . ($ingredient->isLowStock() ? "⚠️ STOCK BAJO" : "✅ Stock normal") . "\n";
                
                // Simular stock alto
                $ingredient->stock = 150;
                echo "Stock simulado alto (150): " . ($ingredient->isOverStock() ? "⚠️ STOCK ALTO" : "✅ Stock normal") . "\n";
                
                // Restaurar stock original
                $ingredient->stock = $originalStock;
                echo "Stock normal (" . $originalStock . "): " . $ingredient->getStockStatus() . "\n";
                
                // Probar validación de máximo mayor que mínimo
                echo "\n--- Pruebas de validación de formulario ---\n";
                $ingredient->min_stock = 100;
                $ingredient->max_stock = 50; // Inválido: máximo menor que mínimo
                
                if (!$ingredient->validate()) {
                    echo "✅ Validación funcionando: ";
                    foreach ($ingredient->getErrors() as $field => $errors) {
                        echo $field . ": " . implode(', ', $errors) . "\n";
                    }
                } else {
                    echo "❌ Error: La validación debería fallar\n";
                }
                
            } else {
                echo "❌ Error al guardar límites: ";
                foreach ($ingredient->getErrors() as $field => $errors) {
                    echo $field . ": " . implode(', ', $errors) . "\n";
                }
            }
            
        } else {
            echo "❌ No se encontraron ingredientes para probar\n";
        }

        echo "\n=== FIN DE LA PRUEBA ===\n";
    }
}
