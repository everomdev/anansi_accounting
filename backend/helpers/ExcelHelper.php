<?php

namespace backend\helpers;

use common\models\Business;
use common\models\Category;
use common\models\Ingredient;
use common\models\IngredientStock;
use common\models\Movement;
use common\models\UnitOfMeasurement;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\CellIterator;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Yaml\Yaml;

class ExcelHelper
{
    public static function generateReferenceTemplate(Business $business)
    {
        $categories = Category::find()
            ->where([
                'or',
                ['business_id' => $business->id],
                ['business_id' => null],
            ])
            ->all();

        $unitOfMeasurements = UnitOfMeasurement::find()
            ->where(['business_id' => $business->id])
            ->all();

        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->setCellValue('A1', 'Identificador');
        $activeWorksheet->setCellValue('B1', 'Categoría');
        $activeWorksheet->setCellValue('E1', 'Unidad de Medida');

        $currentIndex = 2;
        foreach ($categories as $category) {
            $activeWorksheet->setCellValue("A$currentIndex", $category->id);
            $activeWorksheet->setCellValue("B$currentIndex", $category->name);
            $currentIndex++;
        }

        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);

        $currentIndex = 2;
        foreach ($unitOfMeasurements as $unitOfMeasurement) {
            $activeWorksheet->setCellValue("E$currentIndex", $unitOfMeasurement->name);
            $currentIndex++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Documento_de_referencias.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
        exit(200);
    }

    public static function generateIngredientsTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();

        $activeWorksheet->setCellValue("A1", "Clave");
        $activeWorksheet->setCellValue("B1", "Insumo");
        $activeWorksheet->setCellValue("C1", "Categoría");
        $activeWorksheet->setCellValue("D1", "Unidad de compra");
        $activeWorksheet->setCellValue("E1", "Unidad de cocina");
        $activeWorksheet->setCellValue("F1", "Factor de Rendimiento");
        $activeWorksheet->setCellValue("G1", "Porciones por unidad");
        $activeWorksheet->setCellValue("H1", "Observaciones");

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Plantilla_para_importar_insumos.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
        exit(200);
    }

    public static function generateMovementTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();

        $activeWorksheet->setCellValue("A1", "Clave");
        $activeWorksheet->setCellValue("B1", "Fecha (año-mes-dia)");
        $activeWorksheet->setCellValue("C1", "Proveedor");
        $activeWorksheet->setCellValue("D1", "Tipo de Pago");
        $activeWorksheet->setCellValue("E1", "Factura");
        $activeWorksheet->setCellValue("F1", "Cantidad");
        $activeWorksheet->setCellValue("G1", "Precio de Compra");
        $activeWorksheet->setCellValue("H1", "Impuesto");
        $activeWorksheet->setCellValue("I1", "Precio Unitario");
        $activeWorksheet->setCellValue("J1", "Total");
        $activeWorksheet->setCellValue("K1", "Observaciones");

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);

        $activeWorksheet->setCellValue("N1", "Referencia de tipos de pago");
        $activeWorksheet->setCellValue("N2", "Valor");
        $activeWorksheet->setCellValue("O2", "Descripción");

        $activeWorksheet->setCellValue("N3", Movement::PAYMENT_TYPE_CARD);
        $activeWorksheet->setCellValue("O3", "Tarjeta");

        $activeWorksheet->setCellValue("N4", Movement::PAYMENT_TYPE_BANK_TRANSFERENCE);
        $activeWorksheet->setCellValue("O4", "Transferencia Bancaria");

        $activeWorksheet->setCellValue("N5", Movement::PAYMENT_TYPE_CASH);
        $activeWorksheet->setCellValue("O5", "Efectivo");

        $activeWorksheet->setCellValue("N6", Movement::PAYMENT_TYPE_OTHER);
        $activeWorksheet->setCellValue("O6", "Otro");

        $spreadsheet->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);


        $writer = new Xlsx($spreadsheet);
        $fileName = 'Plantilla_para_importar_movimientos_de_entrada.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
        exit(200);
    }

    public static function importIngredients(Business $business, $fileName)
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileName);

        $ingredientData = [];

        $rowIterator = $spreadsheet->getActiveSheet()->getRowIterator();
        while (true) {
            $cellIterator = $rowIterator->current()->getCellIterator('A', 'G');
            if($rowIterator->current()->getRowIndex() != 1) {
                if (empty($cellIterator->current()->getValue())) {
                    break;
                }
                $data = [];
                $data[] = $cellIterator->current()->getValue(); // A - Clave
                $cellIterator->next();
                $data[] = $cellIterator->current()->getValue(); // B - Insumo
                $cellIterator->next();
                $data[] = $cellIterator->current()->getValue(); // C - Categoría
                $cellIterator->next();
                $data[] = $cellIterator->current()->getValue(); // D - Unidad de compra
                $cellIterator->next();
                $data[] = $cellIterator->current()->getValue(); // E - Unidad de cocina
                $cellIterator->next();
                $data[] = $cellIterator->current()->getValue(); // F - Factor de Rendimiento
                $cellIterator->next();
                $data[] = $cellIterator->current()->getValue(); // G - Porciones por unidad
                $cellIterator->next();
                $data[] = $cellIterator->current()->getValue(); // H - Observaciones
                $data[] = $business->id;
                $data[] = 0;

                $ingredientData[] = $data;
            }

            $rowIterator->next();
        }

        \Yii::$app->db->createCommand()
            ->batchInsert(
                'ingredient_stock',
                ['key', 'ingredient', 'category_id', 'um', 'portion_um', 'yield','portions_per_unit', 'observations', 'business_id', 'quantity'],
                $ingredientData
            )
            ->execute();

        return;
    }

    public static function importMovements(Business $business, $fileName)
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileName);

        $movementsData = [];

        $rowIterator = $spreadsheet->getActiveSheet()->getRowIterator();
        while (true) {
            $cellIterator = $rowIterator->current()->getCellIterator('A', 'K');
            if($rowIterator->current()->getRowIndex() != 1) {
                if (empty($cellIterator->current()->getValue())) {
                    break;
                }
                $data = [];
                $data['key'] = $cellIterator->current()->getValue(); // A - Clave
                $cellIterator->next();
                $createdAt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($cellIterator->current()->getValue());
                $data['created_at'] = $createdAt->format('Y-m-d'); // B - Fecha
                $cellIterator->next();
                $data['provider'] = $cellIterator->current()->getValue(); // C - Proveedor
                $cellIterator->next();
                $data['payment_type'] = $cellIterator->current()->getValue(); // D - Tipo de Pago
                $cellIterator->next();
                $data['invoice'] = $cellIterator->current()->getValue(); // E - Factura
                $cellIterator->next();
                $data['quantity'] = $cellIterator->current()->getValue(); // F - Cantidad
                $cellIterator->next();
                $data['amount'] = $cellIterator->current()->getValue(); // G - Precio de Compra
                $cellIterator->next();
                $data['tax'] = $cellIterator->current()->getValue(); // H - Impuesto
                $cellIterator->next();
                $data['unit_price'] = $cellIterator->current()->getValue(); // I - Precio Unitario
                $cellIterator->next();
                $data['total'] = $cellIterator->current()->getValue(); // J - Total
                $cellIterator->next();
                $data['observations'] = $cellIterator->current()->getValue(); // K - Observaciones

                $data['business_id'] = $business->id;

                $movementsData[] = $data;
            }

            $rowIterator->next();
        }

        $movementsData = array_map(function($movement){
            $ingredient = IngredientStock::find()
                ->where([
                    'key' => $movement['key'],
                    'business_id' => $movement['business_id']
                ])
                ->select(["id"])
                ->asArray(true)
                ->one();
            unset($movement['key']);
            if($ingredient){
                $movement['ingredient_id'] = $ingredient['id'];
            }else{
                $movement['ingredient_id'] = null;
            }

            return $movement;
        }, $movementsData);

        $movementsData = array_filter($movementsData, function($movement){
            return !empty($movement['ingredient_id']);
        });

        foreach ($movementsData as $movementData){
            $movement = new Movement($movementData);
            $movement->type = Movement::TYPE_INPUT;
            $movement->save();
            if($movement->hasErrors()){
                \Yii::error(Yaml::dump($movement->errors));
            }
        }

        return;
    }

    public static function exportIngredients(Business $business){
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();

        $activeWorksheet->setCellValue("A1", "Clave");
        $activeWorksheet->setCellValue("B1", "Insumo");
        $activeWorksheet->setCellValue("C1", "Categoría");
        $activeWorksheet->setCellValue("D1", "Unidad de compra");
        $activeWorksheet->setCellValue("E1", "Unidad de cocina");
        $activeWorksheet->setCellValue("F1", "Factor de Rendimiento");
        $activeWorksheet->setCellValue("G1", "Porciones por unidad");
        $activeWorksheet->setCellValue("H1", "Observaciones");

        $ingredients = $business->getIngredientStocks()->all();
        $currentRow = 2;
        foreach ($ingredients as $ingredient){
            /** @var $ingredient IngredientStock */
            $activeWorksheet->setCellValue("A$currentRow", $ingredient->key);
            $activeWorksheet->setCellValue("B$currentRow", $ingredient->ingredient);
            $activeWorksheet->setCellValue("C$currentRow", $ingredient->category->name);
            $activeWorksheet->setCellValue("D$currentRow", $ingredient->um);
            $activeWorksheet->setCellValue("E$currentRow", $ingredient->portion_um);
            $activeWorksheet->setCellValue("F$currentRow", "{$ingredient->yield}%");
            $activeWorksheet->setCellValue("G$currentRow", $ingredient->portions_per_unit);
            $activeWorksheet->setCellValue("H$currentRow", $ingredient->observations);

            $currentRow++;
        }

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Catálogo_de_insumos.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
        exit(200);
    }

    public static function exportMovements(Business $business)
    {
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();

        $activeWorksheet->setCellValue("A1", "Tipo de movimiento");
        $activeWorksheet->setCellValue("B1", "Clave");
        $activeWorksheet->setCellValue("C1", "Insumo");
        $activeWorksheet->setCellValue("D1", "Fecha");
        $activeWorksheet->setCellValue("E1", "Proveedor");
        $activeWorksheet->setCellValue("F1", "Tipo de Pago");
        $activeWorksheet->setCellValue("G1", "Factura");
        $activeWorksheet->setCellValue("H1", "Cantidad");
        $activeWorksheet->setCellValue("I1", "Precio de Compra");
        $activeWorksheet->setCellValue("J1", "Impuesto");
        $activeWorksheet->setCellValue("K1", "Precio Unitario");
        $activeWorksheet->setCellValue("L1", "Total");
        $activeWorksheet->setCellValue("M1", "Observaciones");

        $movements = Movement::find()->where(['business_id' => $business->id])->all();
        $currentRow = 2;
        foreach ($movements as $movement){
            $activeWorksheet->setCellValue("A$currentRow", $movement->getFormattedType());
            $activeWorksheet->setCellValue("B$currentRow", $movement->ingredient->key);
            $activeWorksheet->setCellValue("C$currentRow", $movement->ingredient->ingredient);
            $activeWorksheet->setCellValue("D$currentRow", \PhpOffice\PhpSpreadsheet\Shared\Date::dateTimeToExcel(\DateTime::createFromFormat('Y-m-d H:i:s', $movement->created_at)));
            $activeWorksheet->setCellValue("E$currentRow", $movement->provider);
            $activeWorksheet->setCellValue("F$currentRow", $movement->getFormattedPaymentType());
            $activeWorksheet->setCellValue("G$currentRow", $movement->invoice);
            $activeWorksheet->setCellValue("H$currentRow", $movement->quantity);
            $activeWorksheet->setCellValue("I$currentRow", $movement->amount);
            $activeWorksheet->setCellValue("J$currentRow", $movement->tax);
            $activeWorksheet->setCellValue("K$currentRow", $movement->unit_price);
            $activeWorksheet->setCellValue("L$currentRow", $movement->total);
            $activeWorksheet->setCellValue("M$currentRow", $movement->observations);

            $currentRow++;
        }

        $spreadsheet->getActiveSheet()->getStyle("I2:L$currentRow")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD);
        $spreadsheet->getActiveSheet()->getStyle("D2:D$currentRow")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD);

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);


        $writer = new Xlsx($spreadsheet);
        $fileName = 'Movimientos.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
        exit(200);
    }
}
