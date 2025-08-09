<?php

namespace backend\helpers;

use common\models\Business;
use common\models\Category;
use common\models\Ingredient;
use common\models\Convoy;
use common\models\IngredientStock;
use common\models\IngredientStandardRecipe;
use common\models\StandardRecipe;
use common\models\Movement;
use common\models\Provider;
use common\models\StockPrice;
use common\models\UnitOfMeasurement;
use common\models\ConsumptionCenter;
use Yii;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\CellIterator;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Yaml\Yaml;
use yii\web\HttpException;

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

 public static function generateIngredientsTemplate($id)
{
    /** @var Category[] $categories */
    $categories = Category::find()->where([
        'or',
        ['business_id' => $id],
        ['business_id' => null],
    ])->all();

    /** @var UnitOfMeasurement[] $unitOfMeasurements */
    $unitOfMeasurements = UnitOfMeasurement::find()
        ->where(['business_id' => $id])
        ->all();

    $spreadsheet = new Spreadsheet();
    $activeWorksheet = $spreadsheet->getActiveSheet();

    // 7. Configurar estilos
     $centerStyle = [
         'alignment' => [
             'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
             'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
         ],
     ];
    $activeWorksheet->setCellValue("A1", "Clave*");
    $activeWorksheet->setCellValue("B1", "Insumo*");
    $activeWorksheet->setCellValue("C1", "Marca");
    $activeWorksheet->setCellValue("D1", "Presentación");
    $activeWorksheet->setCellValue("E1", "Categoría*");
    $activeWorksheet->setCellValue("F1", "Unidad de compra*");
    $activeWorksheet->setCellValue("G1", "Unidad de cocina*");
    $activeWorksheet->setCellValue("H1", "Factor de Rendimiento*");
    $activeWorksheet->setCellValue("I1", "EQ. UNI. Cocina*");
    $activeWorksheet->setCellValue("J1", "Precio*");
    $activeWorksheet->setCellValue("K1", "Observaciones");
    
    $activeWorksheet->getStyle('A1:L100')->applyFromArray($centerStyle);
    $activeWorksheet->freezePane('C2');

    // Set manual column widths instead of auto-size
    $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(15); // Clave
    $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(35); // Insumo
    $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(35); // Insumo
    $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(35); // Insumo
    $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(18); // Categoría
    $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(18); // Unidad de compra
    $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(18); // Unidad de cocina
    $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(25); // Factor de Rendimiento
    $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(25); // Porciones por unidad
    $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(10); // Observaciones
    $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(30);

    // Create a named range for categories
    $categorySheet = $spreadsheet->createSheet();
    $categorySheet->setTitle('Categorias');
    $categorySheet->setCellValue('A1', 'Categoría');

    $row = 2;
    foreach ($categories as $category) {
        $categorySheet->setCellValue("A$row", $category->name);
        $row++;
    }

    $spreadsheet->addNamedRange(
        new \PhpOffice\PhpSpreadsheet\NamedRange('Categorias', $categorySheet, 'A2:A' . ($row - 1))
    );

    // Apply data validation to the category column
    $dataValidation = $spreadsheet->getActiveSheet()->getCell('E2')->getDataValidation();
    $dataValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
    $dataValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
    $dataValidation->setAllowBlank(false);
    $dataValidation->setShowInputMessage(true);
    $dataValidation->setShowErrorMessage(true);
    $dataValidation->setShowDropDown(true);
    $dataValidation->setErrorTitle('Error de entrada');
    $dataValidation->setError('Este valor no es admitido');
    $dataValidation->setPromptTitle('Selecciona una categoría');
    $dataValidation->setPrompt('Por favor, selecciona un valor del desplegable.');
    $dataValidation->setFormula1('=Categorias!$A$2:$A$' . ($row - 1));

    for ($i = 2; $i <= 5000; $i++) {
        $spreadsheet->getActiveSheet()->getCell("E$i")->setDataValidation(clone $dataValidation);
    }

    // Create a named range for unit of measurements
    $umSheet = $spreadsheet->createSheet();
    $umSheet->setTitle('UMs');
    $umSheet->setCellValue('A1', 'Unidad de medida');

    $row = 2;
    foreach ($unitOfMeasurements as $um) {
        $umSheet->setCellValue("A$row", $um->name);
        $row++;
    }

    $spreadsheet->addNamedRange(
        new \PhpOffice\PhpSpreadsheet\NamedRange('UMs', $umSheet, 'A2:A' . ($row - 1))
    );

    // Apply data validation to the um column
    $dataValidation = $spreadsheet->getActiveSheet()->getCell('F1')->getDataValidation();
    $dataValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
    $dataValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
    $dataValidation->setAllowBlank(false);
    $dataValidation->setShowInputMessage(true);
    $dataValidation->setShowErrorMessage(true);
    $dataValidation->setShowDropDown(true);
    $dataValidation->setErrorTitle('Error de entrada');
    $dataValidation->setError('Este valor no es admitido');
    $dataValidation->setPromptTitle('Selecciona una unidad de medida');
    $dataValidation->setPrompt('Por favor, selecciona un valor del desplegable.');
    $dataValidation->setFormula1('=UMs!$A$2:$A$' . ($row - 1));

    for ($i = 2; $i <= 5000; $i++) {
        $spreadsheet->getActiveSheet()->getCell("F$i")->setDataValidation(clone $dataValidation);
        $spreadsheet->getActiveSheet()->getCell("G$i")->setDataValidation(clone $dataValidation);
    }

    // Apply data validation to the factor de rendimiento column
    // CAMBIO: Tipo cambiado a DECIMAL para permitir valores con decimales
    $factorValidation = $spreadsheet->getActiveSheet()->getCell('H2')->getDataValidation();
    $factorValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DECIMAL);
    $factorValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
    $factorValidation->setAllowBlank(false);
    $factorValidation->setShowInputMessage(true);
    $factorValidation->setShowErrorMessage(true);
    $factorValidation->setErrorTitle('Error de entrada');
    $factorValidation->setError('Este valor no es admitido');
    $factorValidation->setPromptTitle('Factor de Rendimiento');
    $factorValidation->setPrompt('Por favor, ingresa un valor numérico entre 0 y 100 (permite decimales)');
    $factorValidation->setFormula1(0); // Valor mínimo
    $factorValidation->setFormula2(100); // Valor máximo

    for ($i = 2; $i <= 5000; $i++) {
        $spreadsheet->getActiveSheet()->getCell("H$i")->setDataValidation(clone $factorValidation);
    }

    // Apply data validation to the price column - Permitir cualquier valor numérico
    $priceValidation = $spreadsheet->getActiveSheet()->getCell('J2')->getDataValidation();
    $priceValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DECIMAL);
    $priceValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
    $priceValidation->setAllowBlank(false);
    $priceValidation->setShowInputMessage(true);
    $priceValidation->setShowErrorMessage(true);
    $priceValidation->setErrorTitle('Error de entrada');
    $priceValidation->setError('Este valor no es admitido');
    $priceValidation->setPromptTitle('Precio');
    $priceValidation->setPrompt('Por favor, ingresa un valor numérico.');
    $priceValidation->setFormula1(0);
    $priceValidation->setFormula2(99999999); // Aumentar el límite máximo

    for ($i = 2; $i <= 5000; $i++) {
        $spreadsheet->getActiveSheet()->getCell("K$i")->setDataValidation(clone $priceValidation);
    }

    // Create a legend sheet
    $legendSheet = $spreadsheet->createSheet();
    $legendSheet->setTitle('Leyenda');
    $legendSheet->setCellValue('A1', 'Columna');
    $legendSheet->setCellValue('B1', 'Descripción');
    $legendSheet->setCellValue('A2', 'Identificador');
    $legendSheet->setCellValue('B2', 'Debe ser un número entero. No puede contener % ni $.');
    $legendSheet->setCellValue('A3', 'Categoría');
    $legendSheet->setCellValue('B3', 'Debe ser una de las categorías listadas en la hoja Categorías.');
    $legendSheet->setCellValue('A4', 'Unidad de Medida');
    $legendSheet->setCellValue('B4', 'Debe ser una de las unidades de medida listadas en la hoja UMs.');
    $legendSheet->setCellValue('A5', 'Factor de Rendimiento');
    $legendSheet->setCellValue('B5', 'Debe ser un valor numérico entre 0 y 100. Permite decimales.');
    $legendSheet->setCellValue('A6', 'Precio');
    $legendSheet->setCellValue('B6', 'Debe ser un valor numérico mayor que 0.');

    $spreadsheet->getSheetByName('Leyenda')->getColumnDimension('A')->setAutoSize(true);
    $spreadsheet->getSheetByName('Leyenda')->getColumnDimension('B')->setAutoSize(true);

     foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
         $worksheet->calculateColumnWidths();
     }
    // Activar la primera hoja antes de guardar
    $spreadsheet->setActiveSheetIndex(0);
    
    // Establecer la celda activa en A2 para que el usuario pueda empezar a llenar datos inmediatamente
    $spreadsheet->getActiveSheet()->setSelectedCell('A2');

    $writer = new Xlsx($spreadsheet);
    $fileName = 'Plantilla_para_importar_insumos.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
    $writer->save('php://output');
    exit(200);
}
    public static function generateMovementTemplate($businessId = null)
    {
        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->setTitle('Movimientos');

        // Configurar estilos
        $centerStyle = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        // Encabezados principales
        $activeWorksheet->setCellValue("A1", "Movimiento*");
        $activeWorksheet->setCellValue("D1", "Clave");
        $activeWorksheet->setCellValue("C1", "Insumo*");
        $activeWorksheet->setCellValue("B1", "Fecha (año-mes-dia)*");
        $activeWorksheet->setCellValue("E1", "Proveedor*");
        $activeWorksheet->setCellValue("F1", "Tipo de Pago*");
        $activeWorksheet->setCellValue("G1", "Factura");
        $activeWorksheet->setCellValue("H1", "Centro de Consumo*");
        $activeWorksheet->setCellValue("I1", "Cantidad*");
        $activeWorksheet->setCellValue("J1", "Precio de Compra*");
        $activeWorksheet->setCellValue("K1", "Impuesto");
        $activeWorksheet->setCellValue("L1", "Precio Unitario*");
        $activeWorksheet->setCellValue("M1", "Total*");
        $activeWorksheet->setCellValue("N1", "Observaciones");

        // Agregar comentarios descriptivos a los encabezados
        $comment = $activeWorksheet->getComment('A1');
        $textRun = $comment->getText()->createTextRun('Selecciona si el movimiento es una Entrada (compra o ingreso al almacén) o una Salida (requisición, consumo, merma o traspaso).');
        $textRun->getFont()->setSize(12);
        $comment->setWidth('400px');
        $comment->setHeight('100px');
        
        $comment = $activeWorksheet->getComment('D1');
        $textRun = $comment->getText()->createTextRun('(Opcional) Clave única del insumo. Si ya está registrado en el sistema, se llenará automáticamente al seleccionarla. Si no lo conoces, déjalo en blanco.');
        $textRun->getFont()->setSize(12);
        $comment->setWidth('400px');
        $comment->setHeight('80px');
        
        $comment = $activeWorksheet->getComment('C1');
        $textRun = $comment->getText()->createTextRun('Selecciona un insumo de la lista desplegable. También se completará automáticamente al seleccionar una clave.');
        $textRun->getFont()->setSize(12);
        $comment->setWidth('400px');
        $comment->setHeight('80px');
        
        $comment = $activeWorksheet->getComment('B1');
        $textRun = $comment->getText()->createTextRun('Fecha en que se realizó la entrada o salida del insumo. Usa el formato AAAA-MM-DD (ej. 2025-08-06).');
        $textRun->getFont()->setSize(12);
        $comment->setWidth('400px');
        $comment->setHeight('80px');
        
        $comment = $activeWorksheet->getComment('E1');
        $textRun = $comment->getText()->createTextRun('Nombre del proveedor o empresa a quien se compró el insumo. Si no tienes proveedor fijo, escribe o selecciona "Por definir".');
        $textRun->getFont()->setSize(12);
        $comment->setWidth('400px');
        $comment->setHeight('80px');
        
        $comment = $activeWorksheet->getComment('F1');
        $textRun = $comment->getText()->createTextRun('Selecciona la forma en que se realizó el pago. Ejemplos: Efectivo, Transferencia, etc.');
        $textRun->getFont()->setSize(12);
        $comment->setWidth('400px');
        $comment->setHeight('60px');
        
        $comment = $activeWorksheet->getComment('G1');
        $textRun = $comment->getText()->createTextRun('(Opcional) Número de factura o comprobante de la compra. Si no aplica, puedes dejarlo en blanco.');
        $textRun->getFont()->setSize(12);
        $comment->setWidth('400px');
        $comment->setHeight('80px');
        
        $comment = $activeWorksheet->getComment('H1');
        $textRun = $comment->getText()->createTextRun('Solo para SALIDAS: indica en qué área se utilizó el insumo. No llenar si el movimiento es una Entrada.');
        $textRun->getFont()->setSize(12);
        $comment->setWidth('400px');
        $comment->setHeight('80px');
        
        $comment = $activeWorksheet->getComment('I1');
        $textRun = $comment->getText()->createTextRun('Número de unidades compradas. Debe coincidir con la unidad de compra registrada (kg, litros, piezas, etc.).');
        $textRun->getFont()->setSize(12);
        $comment->setWidth('400px');
        $comment->setHeight('80px');
        
        $comment = $activeWorksheet->getComment('J1');
        $textRun = $comment->getText()->createTextRun('Precio total pagado por el insumo, antes de impuestos. Este dato se usa para calcular el precio unitario.');
        $textRun->getFont()->setSize(12);
        $comment->setWidth('400px');
        $comment->setHeight('80px');
        
        $comment = $activeWorksheet->getComment('K1');
        $textRun = $comment->getText()->createTextRun('Monto del impuesto aplicado (ej. IVA). Si no hubo impuestos, deja en blanco o coloca 0.');
        $textRun->getFont()->setSize(12);
        $comment->setWidth('400px');
        $comment->setHeight('80px');
        
        $comment = $activeWorksheet->getComment('L1');
        $textRun = $comment->getText()->createTextRun('Se calcula automáticamente si no lo llenas. Es el precio por unidad del insumo ((Precio de Compra + Impuesto) ÷ Cantidad).');
        $textRun->getFont()->setSize(12);
        $comment->setWidth('450px');
        $comment->setHeight('80px');
        
        $comment = $activeWorksheet->getComment('M1');
        $textRun = $comment->getText()->createTextRun('Total con impuestos. Se usa para validar los datos. Si no lo llenas, el sistema lo calcula automáticamente.');
        $textRun->getFont()->setSize(12);
        $comment->setWidth('400px');
        $comment->setHeight('80px');
        
        $comment = $activeWorksheet->getComment('N1');
        $textRun = $comment->getText()->createTextRun('(Opcional) Notas adicionales como promociones, aclaraciones, devoluciones, detalles del insumo o cualquier información útil.');
        $textRun->getFont()->setSize(12);
        $comment->setWidth('450px');
        $comment->setHeight('80px');

        // Aplicar estilos y configurar anchos
        $activeWorksheet->getStyle('A1:N1')->applyFromArray($centerStyle);
        $activeWorksheet->freezePane('E2');

        // Aplicar estilo centrado a todas las celdas de datos
        $activeWorksheet->getStyle('A2:N5000')->applyFromArray($centerStyle);

        // Agregar bordes finos blancos a toda la tabla para mejor visualización
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'E0E0E0']
                ]
            ]
        ];
        $activeWorksheet->getStyle('A1:N5000')->applyFromArray($borderStyle);

        // Configurar anchos de columnas
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(15); // Movimiento
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(15); // Clave
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(35); // Insumo
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(20); // Fecha
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(25); // Proveedor
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(25); // Tipo de Pago
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(15); // Factura
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(25); // Centro de Consumo
        $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(12); // Cantidad
        $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(20); // Precio de Compra
        $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(12); // Impuesto
        $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(15); // Precio Unitario
        $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(15); // Total
        $spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(30); // Observaciones

        // Configurar formato de fecha para la columna B (Fecha)
        $spreadsheet->getActiveSheet()->getStyle('B2:B5000')
            ->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDD);

        // Hoja de insumos
        if ($businessId) {
            $ingredientsSheet = $spreadsheet->createSheet();
            $ingredientsSheet->setTitle('Insumos');
            $ingredientsSheet->setCellValue('A1', 'Clave');
            $ingredientsSheet->setCellValue('B1', 'Insumo');
            $ingredientsSheet->setCellValue('C1', 'Unidad');

            $ingredients = IngredientStock::find()
                ->where(['business_id' => $businessId])
                ->all();

            $row = 2;
            foreach ($ingredients as $ingredient) {
                $ingredientsSheet->setCellValue("A$row", $ingredient->key);
                $ingredientsSheet->setCellValue("B$row", $ingredient->ingredient);
                $ingredientsSheet->setCellValue("C$row", $ingredient->portion_um);
                $row++;
            }

            $ingredientsSheet->getColumnDimension('A')->setWidth(15);
            $ingredientsSheet->getColumnDimension('B')->setWidth(40);
            $ingredientsSheet->getColumnDimension('C')->setWidth(15);

            // Aplicar validación de datos a las columnas en la hoja principal
            if ($row > 2) {
                $mainSheet = $spreadsheet->getSheet(0); // Obtener la hoja principal
                
                // Validación para columna A (Movimiento) - desplegable con Entrada/Salida
                $movementValidation = $mainSheet->getCell('A2')->getDataValidation();
                $movementValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $movementValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                $movementValidation->setAllowBlank(false);
                $movementValidation->setShowInputMessage(true);
                $movementValidation->setShowErrorMessage(true);
                $movementValidation->setShowDropDown(true);
                $movementValidation->setErrorTitle('Error de entrada');
                $movementValidation->setError('Este valor no es admitido. Debe seleccionar Entrada o Salida.');
                $movementValidation->setPromptTitle('Tipo de Movimiento');
                $movementValidation->setPrompt('Seleccione el tipo de movimiento: Entrada o Salida.');
                $movementValidation->setFormula1('"Entrada,Salida"');
                
                // Validación para columna B (Clave) - desplegable con todas las claves
                $keyValidation = $mainSheet->getCell('D2')->getDataValidation();
                $keyValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $keyValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                $keyValidation->setAllowBlank(true); // Permitir vacío
                $keyValidation->setShowInputMessage(true);
                $keyValidation->setShowErrorMessage(true);
                $keyValidation->setShowDropDown(true);
                $keyValidation->setErrorTitle('Error de entrada');
                $keyValidation->setError('Este valor no es admitido. Debe seleccionar una clave válida.');
                $keyValidation->setPromptTitle('Selecciona una clave');
                $keyValidation->setPrompt('Selecciona una clave del desplegable. El insumo se completará automáticamente.');
                $keyValidation->setFormula1('Insumos!$A$2:$A$' . ($row - 1));
                
                // Validación para columna C (Insumo) - desplegable con todos los nombres de insumos
                $ingredientValidation = $mainSheet->getCell('C2')->getDataValidation();
                $ingredientValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $ingredientValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                $ingredientValidation->setAllowBlank(true); // Permitir vacío
                $ingredientValidation->setShowInputMessage(true);
                $ingredientValidation->setShowErrorMessage(true);
                $ingredientValidation->setShowDropDown(true);
                $ingredientValidation->setErrorTitle('Error de entrada');
                $ingredientValidation->setError('Este valor no es admitido. Debe seleccionar un insumo válido.');
                $ingredientValidation->setPromptTitle('Selecciona un insumo');
                $ingredientValidation->setPrompt('Selecciona un insumo del desplegable. También se completa automáticamente al seleccionar una clave.');
                $ingredientValidation->setFormula1('Insumos!$B$2:$B$' . ($row - 1));

                // Aplicar validaciones y fórmulas a todas las filas
                for ($i = 2; $i <= 5000; $i++) {
                    // Aplicar validación a columna A (Movimiento)
                    $mainSheet->getCell("A$i")->setDataValidation(clone $movementValidation);
                    
                    // Aplicar validación a columna B (Clave)
                    $mainSheet->getCell("D$i")->setDataValidation(clone $keyValidation);
                    
                    // Aplicar validación a columna C (Insumo)
                    $mainSheet->getCell("C$i")->setDataValidation(clone $ingredientValidation);
                    
                    // Fórmula BUSCARV en columna C (Insumo) como solicitó el usuario
                    $mainSheet->setCellValue("C$i", "=IF(D$i<>\"\",VLOOKUP(D$i,Insumos!\$A\$2:\$B\$1000,2,FALSE),\"\")");
                }
                
                // Aplicar formato condicional a la columna B (Clave)
                // Condición: Si C (Insumo) no está vacía Y B (Clave) está vacía, poner en gris
                $conditionalFormatting = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
                $conditionalFormatting->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
                $conditionalFormatting->addCondition('AND($C2<>"",$D2="")');
                $conditionalFormatting->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $conditionalFormatting->getStyle()->getFill()->getStartColor()->setRGB('D3D3D3'); // Gris claro

                $mainSheet->getStyle('D2:D5000')->setConditionalStyles([$conditionalFormatting]);
            }
        }

        // Hoja de proveedores
        if ($businessId) {
            $providersSheet = $spreadsheet->createSheet();
            $providersSheet->setTitle('Proveedores');
            $providersSheet->setCellValue('A1', 'Proveedor');
            $providersSheet->setCellValue('B1', 'Métodos de Pago');

            $providers = Provider::find()
                ->where(['business_id' => $businessId])
                ->all();

            $row = 2;
            $allProviders = [];
            
            // Agregar siempre "Por definir" como primera opción
            $providersSheet->setCellValue("A$row", "Por definir");
            $providersSheet->setCellValue("B$row", "Todos los métodos disponibles");
            $providersSheet->setCellValue("C$row", "PM_PorDefinir");
            $allProviders[] = "Por definir";
            $row++;
            
            // Para cada proveedor crear también una hoja con sus métodos de pago
            foreach ($providers as $providerIndex => $provider) {
                $providersSheet->setCellValue("A$row", $provider->business_name);
                $paymentMethods = is_array($provider->payment_method) ? 
                    $provider->payment_method : 
                    explode(',', $provider->payment_method);
                    
                $paymentMethodStr = is_array($provider->payment_method) ? 
                    implode(', ', $provider->payment_method) : 
                    $provider->payment_method;
                $providersSheet->setCellValue("B$row", $paymentMethodStr);
                $allProviders[] = $provider->business_name;
                
                // Crear una hoja para los métodos de pago de este proveedor
                $providerName = preg_replace('/[^\w\s]/', '', $provider->business_name);
                $providerName = substr(str_replace(' ', '_', $providerName), 0, 20); // Limitar longitud
                $providerSheetName = 'PM_' . ($providerIndex + 1); // Nombre corto para evitar problemas
                
                $providerPaymentSheet = $spreadsheet->createSheet();
                $providerPaymentSheet->setTitle($providerSheetName);
                $providerPaymentSheet->setCellValue('A1', 'Valor');
                $providerPaymentSheet->setCellValue('B1', 'Descripción');
                
                // Mapear códigos de pago a descripciones en español
                $paymentDescriptions = [
                    Movement::PAYMENT_METHOD_CASH => 'Efectivo',
                    Movement::PAYMENT_METHOD_TRANSFER => 'Transferencia Bancaria',
                    Movement::PAYMENT_METHOD_CHECK => 'Cheque',
                    Movement::PAYMENT_METHOD_CREDIT_CARD => 'Tarjeta de Crédito',
                    Movement::PAYMENT_METHOD_DEBIT_CARD => 'Tarjeta de Débito',
                    Movement::PAYMENT_METHOD_OTHER => 'Otro Método de Pago'
                ];
                
                // Agregar solo los métodos de pago disponibles para este proveedor
                $pmRow = 2;
                foreach ($paymentMethods as $method) {
                    $method = trim($method);
                    if (!empty($method)) {
                        // Mostrar la descripción en español en lugar del código
                        $description = isset($paymentDescriptions[$method]) ? $paymentDescriptions[$method] : $method;
                        $providerPaymentSheet->setCellValue("A$pmRow", $description);
                        $providerPaymentSheet->setCellValue("B$pmRow", $method); // Código interno para referencia
                        $pmRow++;
                    }
                }
                
                $providerPaymentSheet->getColumnDimension('A')->setWidth(25);
                $providerPaymentSheet->getColumnDimension('B')->setWidth(20);
                
                // Asociar el nombre de la hoja con el proveedor para uso posterior
                $providersSheet->setCellValue("C$row", $providerSheetName);
                
                $row++;
            }
            
            // Crear hoja especial para "Por definir" con todos los tipos de pago
            $porDefinirSheet = $spreadsheet->createSheet();
            $porDefinirSheet->setTitle('PM_PorDefinir');
            $porDefinirSheet->setCellValue('A1', 'Valor');
            $porDefinirSheet->setCellValue('B1', 'Descripción');
            
            // Mapear códigos de pago a descripciones en español
            $paymentDescriptions = [
                Movement::PAYMENT_METHOD_CASH => 'Efectivo',
                Movement::PAYMENT_METHOD_TRANSFER => 'Transferencia Bancaria',
                Movement::PAYMENT_METHOD_CHECK => 'Cheque',
                Movement::PAYMENT_METHOD_CREDIT_CARD => 'Tarjeta de Crédito',
                Movement::PAYMENT_METHOD_DEBIT_CARD => 'Tarjeta de Débito',
                Movement::PAYMENT_METHOD_OTHER => 'Otro Método de Pago'
            ];
            
            // Agregar "Por definir" primero
            $porDefinirSheet->setCellValue('A2', 'Por definir');
            $porDefinirSheet->setCellValue('B2', 'Por definir');
            
            // Agregar todos los métodos de pago disponibles
            $pmRow = 3;
            foreach ($paymentDescriptions as $code => $description) {
                $porDefinirSheet->setCellValue("A$pmRow", $description);
                $porDefinirSheet->setCellValue("B$pmRow", $code);
                $pmRow++;
            }
            
            $porDefinirSheet->getColumnDimension('A')->setWidth(25);
            $porDefinirSheet->getColumnDimension('B')->setWidth(20);

            $providersSheet->getColumnDimension('A')->setWidth(30);
            $providersSheet->getColumnDimension('B')->setWidth(40);

            // Aplicar validación de datos a la columna de proveedor (ahora columna E) en la hoja principal
            if ($row > 2) {
                $mainSheet = $spreadsheet->getSheet(0); // Obtener la hoja principal
                $providerValidation = $mainSheet->getCell('E2')->getDataValidation();
                $providerValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $providerValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                $providerValidation->setAllowBlank(false);
                $providerValidation->setShowInputMessage(true);
                $providerValidation->setShowErrorMessage(true);
                $providerValidation->setShowDropDown(true);
                $providerValidation->setErrorTitle('Error de entrada');
                $providerValidation->setError('Este valor no es admitido. Debe seleccionar un proveedor válido.');
                $providerValidation->setPromptTitle('Selecciona un proveedor');
                $providerValidation->setPrompt('Por favor, selecciona un proveedor de la lista desplegable.');
                $providerValidation->setFormula1('Proveedores!$A$2:$A$' . ($row - 1));

                // Aplicar validación a múltiples filas
                for ($i = 2; $i <= 5000; $i++) {
                    $mainSheet->getCell("E$i")->setDataValidation(clone $providerValidation);
                }
            }
        }

        // Hoja de centros de consumo
        if ($businessId) {
            $consumptionCentersSheet = $spreadsheet->createSheet();
            $consumptionCentersSheet->setTitle('Centros de Consumo');
            $consumptionCentersSheet->setCellValue('A1', 'Centro de Consumo');

            $consumptionCenters = ConsumptionCenter::find()
                ->where(['business_id' => $businessId])
                ->all();

            $ccRow = 2;
            foreach ($consumptionCenters as $center) {
                $consumptionCentersSheet->setCellValue("A$ccRow", $center->name);
                $ccRow++;
            }

            $consumptionCentersSheet->getColumnDimension('A')->setWidth(30);

            // Aplicar validación de datos a la columna de centro de consumo (columna H) en la hoja principal
            if ($ccRow > 2) {
                $mainSheet = $spreadsheet->getSheet(0); // Obtener la hoja principal
                $consumptionCenterValidation = $mainSheet->getCell('H2')->getDataValidation();
                $consumptionCenterValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $consumptionCenterValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                $consumptionCenterValidation->setAllowBlank(true);
                $consumptionCenterValidation->setShowInputMessage(true);
                $consumptionCenterValidation->setShowErrorMessage(true);
                $consumptionCenterValidation->setShowDropDown(true);
                $consumptionCenterValidation->setErrorTitle('Error de entrada');
                $consumptionCenterValidation->setError('Este valor no es admitido. Debe seleccionar un centro de consumo válido.');
                $consumptionCenterValidation->setPromptTitle('Selecciona un centro de consumo');
                $consumptionCenterValidation->setPrompt('Por favor, selecciona un centro de consumo del desplegable. Sólo aplica si el movimiento es una salida.');
                $consumptionCenterValidation->setFormula1('\'Centros de Consumo\'!$A$2:$A$' . ($ccRow - 1));

                // Aplicar validación a múltiples filas
                for ($i = 2; $i <= 5000; $i++) {
                    $mainSheet->getCell("H$i")->setDataValidation(clone $consumptionCenterValidation);
                }
            }
        }

        // Hoja de referencia de tipos de pago
        $paymentTypesSheet = $spreadsheet->createSheet();
        $paymentTypesSheet->setTitle('Tipos de Pago');
        $paymentTypesSheet->setCellValue("A1", "Descripción");
        $paymentTypesSheet->setCellValue("B1", "Código Interno");

        $paymentTypesSheet->setCellValue("A2", "Efectivo");
        $paymentTypesSheet->setCellValue("B2", Movement::PAYMENT_METHOD_CASH);

        $paymentTypesSheet->setCellValue("A3", "Transferencia Bancaria");
        $paymentTypesSheet->setCellValue("B3", Movement::PAYMENT_METHOD_TRANSFER);

        $paymentTypesSheet->setCellValue("A4", "Cheque");
        $paymentTypesSheet->setCellValue("B4", Movement::PAYMENT_METHOD_CHECK);

        $paymentTypesSheet->setCellValue("A5", "Tarjeta de Crédito");
        $paymentTypesSheet->setCellValue("B5", Movement::PAYMENT_METHOD_CREDIT_CARD);

        $paymentTypesSheet->setCellValue("A6", "Tarjeta de Débito");
        $paymentTypesSheet->setCellValue("B6", Movement::PAYMENT_METHOD_DEBIT_CARD);

        $paymentTypesSheet->setCellValue("A7", "Otro Método de Pago");
        $paymentTypesSheet->setCellValue("B7", Movement::PAYMENT_METHOD_OTHER);

        $paymentTypesSheet->getColumnDimension('A')->setWidth(25);
        $paymentTypesSheet->getColumnDimension('B')->setWidth(20);

        // Aplicar validación de datos a la columna de tipo de pago (ahora columna F) en la hoja principal
        // con validación dependiente del proveedor seleccionado
        $mainSheet = $spreadsheet->getSheet(0); // Obtener la hoja principal
        
        // Para cada fila, crear una validación dinámica que dependa del proveedor seleccionado
        for ($i = 2; $i <= 5000; $i++) {
            $paymentValidation = $mainSheet->getCell("F$i")->getDataValidation();
            $paymentValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $paymentValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $paymentValidation->setAllowBlank(false);
            $paymentValidation->setShowInputMessage(true);
            $paymentValidation->setShowErrorMessage(true);
            $paymentValidation->setShowDropDown(true);
            $paymentValidation->setErrorTitle('Error de entrada');
            $paymentValidation->setError('Este valor no es admitido. Debe seleccionar un tipo de pago válido para este proveedor.');
            $paymentValidation->setPromptTitle('Selecciona un tipo de pago');
            $paymentValidation->setPrompt('Selecciona cómo se pagó este insumo: efectivo, transferencia, etc.');
            
            // Fórmula que obtiene la referencia a la hoja de métodos de pago del proveedor seleccionado
            // INDIRECTO busca en la columna C de la hoja Proveedores el nombre de la hoja correspondiente
            // COINCIDIR busca el nombre del proveedor en la columna A de la hoja Proveedores (ahora columna E)
            $paymentValidation->setFormula1('=INDIRECT(CONCATENATE(VLOOKUP(E'.$i.',Proveedores!A:C,3,FALSE),"!$A$2:$A$20"))');
            
            $mainSheet->getCell("F$i")->setDataValidation($paymentValidation);
        }
        
        // Agregar formato condicional para ocultar/mostrar columnas según el tipo de movimiento
        $mainSheet = $spreadsheet->getSheet(0);
        
        // Formato condicional para columnas de ENTRADA (E, F, G, J, K, L, M) - solo visibles si Movimiento = "Entrada"
        // Columna E (Proveedor) - gris si es Salida
        $providerConditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $providerConditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $providerConditional->addCondition('$A2="Salida"');
        $providerConditional->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $providerConditional->getStyle()->getFill()->getStartColor()->setRGB('CCCCCC');
        $providerConditional->getStyle()->getFont()->getColor()->setRGB('CCCCCC');
        $mainSheet->getStyle('E2:E5000')->setConditionalStyles([$providerConditional]);

        // Columna F (Tipo de Pago) - gris si es Salida
        $paymentConditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $paymentConditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $paymentConditional->addCondition('$A2="Salida"');
        $paymentConditional->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $paymentConditional->getStyle()->getFill()->getStartColor()->setRGB('CCCCCC');
        $paymentConditional->getStyle()->getFont()->getColor()->setRGB('CCCCCC');
        $mainSheet->getStyle('F2:F5000')->setConditionalStyles([$paymentConditional]);

        // Columna G (Factura) - gris si es Salida
        $invoiceConditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $invoiceConditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $invoiceConditional->addCondition('$A2="Salida"');
        $invoiceConditional->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $invoiceConditional->getStyle()->getFill()->getStartColor()->setRGB('CCCCCC');
        $invoiceConditional->getStyle()->getFont()->getColor()->setRGB('CCCCCC');
        $mainSheet->getStyle('G2:G5000')->setConditionalStyles([$invoiceConditional]);

        // Columna H (Centro de Consumo) - gris si es Entrada
        $consumptionCenterConditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $consumptionCenterConditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $consumptionCenterConditional->addCondition('$A2="Entrada"');
        $consumptionCenterConditional->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $consumptionCenterConditional->getStyle()->getFill()->getStartColor()->setRGB('CCCCCC');
        $consumptionCenterConditional->getStyle()->getFont()->getColor()->setRGB('CCCCCC');
        $mainSheet->getStyle('H2:H5000')->setConditionalStyles([$consumptionCenterConditional]);

        // Columna J (Precio de Compra) - gris si es Salida
        $priceConditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $priceConditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $priceConditional->addCondition('$A2="Salida"');
        $priceConditional->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $priceConditional->getStyle()->getFill()->getStartColor()->setRGB('CCCCCC');
        $priceConditional->getStyle()->getFont()->getColor()->setRGB('CCCCCC');
        $mainSheet->getStyle('J2:J5000')->setConditionalStyles([$priceConditional]);

        // Columna K (Impuesto) - gris si es Salida
        $taxConditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $taxConditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $taxConditional->addCondition('$A2="Salida"');
        $taxConditional->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $taxConditional->getStyle()->getFill()->getStartColor()->setRGB('CCCCCC');
        $taxConditional->getStyle()->getFont()->getColor()->setRGB('CCCCCC');
        $mainSheet->getStyle('K2:K5000')->setConditionalStyles([$taxConditional]);

        // Columna L (Precio Unitario) - gris si es Salida
        $unitPriceConditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $unitPriceConditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $unitPriceConditional->addCondition('$A2="Salida"');
        $unitPriceConditional->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $unitPriceConditional->getStyle()->getFill()->getStartColor()->setRGB('CCCCCC');
        $unitPriceConditional->getStyle()->getFont()->getColor()->setRGB('CCCCCC');
        $mainSheet->getStyle('L2:L5000')->setConditionalStyles([$unitPriceConditional]);

        // Columna M (Total) - gris si es Salida
        $totalConditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $totalConditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_EXPRESSION);
        $totalConditional->addCondition('$A2="Salida"');
        $totalConditional->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $totalConditional->getStyle()->getFill()->getStartColor()->setRGB('CCCCCC');
        $totalConditional->getStyle()->getFont()->getColor()->setRGB('CCCCCC');
        $mainSheet->getStyle('M2:M5000')->setConditionalStyles([$totalConditional]);

        // Agregar fórmulas automáticas para cálculos
        // Obtener la hoja principal
        $mainSheet = $spreadsheet->getSheet(0);
        
        // Para cada fila de datos (desde la fila 2), agregar las fórmulas de cálculo
        for ($i = 2; $i <= 5000; $i++) {
            // Fórmula para Precio Unitario (Columna L): (Precio de compra + Impuesto) / Cantidad - solo si es Entrada
            // Columna J = Precio de Compra, Columna K = Impuesto, Columna I = Cantidad
            $mainSheet->setCellValue("L$i", "=IF(AND(A$i=\"Entrada\",I$i>0,OR(J$i>0,K$i>0)),((IFERROR(J$i,0)+IFERROR(K$i,0))/I$i),\"\")");
            
            // Fórmula para Total (Columna M): Cantidad * Precio Unitario - solo si es Entrada
            // Columna I = Cantidad, Columna L = Precio Unitario
            $mainSheet->setCellValue("M$i", "=IF(AND(A$i=\"Entrada\",I$i>0,L$i>0),I$i*L$i,\"\")");
        }
        
        // Proteger las celdas de fórmulas (L y M) para que no se puedan editar
        $mainSheet->getStyle('L2:L5000')->getProtection()->setLocked(true);
        $mainSheet->getStyle('M2:M5000')->getProtection()->setLocked(true);
        
        // Aplicar formato numérico a las columnas de cálculo
        $mainSheet->getStyle('I2:M5000')->getNumberFormat()->setFormatCode('#,##0.00');
        $mainSheet->getStyle('L2:M5000')->getNumberFormat()->setFormatCode('#,##0.00');

        // Crear hoja de leyenda
        $legendSheet = $spreadsheet->createSheet();
        $legendSheet->setTitle('Leyenda');
        $legendSheet->setCellValue('A1', 'Columna');
        $legendSheet->setCellValue('B1', 'Descripción');
        $legendSheet->setCellValue('A2', 'Movimiento');
        $legendSheet->setCellValue('B2', 'IMPORTANTE: Seleccione "Entrada" para ingresos de stock o "Salida" para egresos de stock. Esta selección determina qué columnas están disponibles.');
        $legendSheet->setCellValue('A3', 'Entrada vs Salida');
        $legendSheet->setCellValue('B3', 'ENTRADA: Usa Proveedor, Tipo de Pago, Factura, Precio de Compra, Impuesto, Precio Unitario y Total. La columna Centro de Consumo se vuelve gris. SALIDA: Usa Centro de Consumo. Las columnas de proveedor, precios e impuestos se vuelven grises.');
        $legendSheet->setCellValue('A4', 'Clave');
        $legendSheet->setCellValue('B4', 'Seleccione una clave del desplegable. El insumo aparecerá automáticamente en la columna C mediante fórmula BUSCARV.');
        $legendSheet->setCellValue('A5', 'Insumo');
        $legendSheet->setCellValue('B5', 'OPCIÓN 1: Se completa automáticamente cuando selecciona una clave en B mediante fórmula BUSCARV. OPCIÓN 2: También puede seleccionar directamente del desplegable si no recuerda la clave. Si esta columna tiene contenido pero la clave está vacía, la celda de clave se pondrá gris.');
        $legendSheet->setCellValue('A6', 'Fecha');
        $legendSheet->setCellValue('B6', 'Formato: año-mes-día (ej: 2025-08-07)');
        $legendSheet->setCellValue('A7', 'Proveedor');
        $legendSheet->setCellValue('B7', 'SOLO PARA ENTRADAS: Seleccione un proveedor del desplegable. Si no tiene proveedores cargados o no está definido, puede seleccionar "Por definir". Se vuelve gris automáticamente si selecciona "Salida".');
        $legendSheet->setCellValue('A8', 'Tipo de Pago');
        $legendSheet->setCellValue('B8', 'SOLO PARA ENTRADAS: Seleccione un tipo de pago del desplegable (en español). Los tipos disponibles dependen del proveedor seleccionado. Se vuelve gris automáticamente si selecciona "Salida".');
        $legendSheet->setCellValue('A9', 'Centro de Consumo');
        $legendSheet->setCellValue('B9', 'SOLO PARA SALIDAS: Seleccione el centro de consumo donde se utilizó el insumo. Se vuelve gris automáticamente si selecciona "Entrada".');
        $legendSheet->setCellValue('A10', 'Cantidad');
        $legendSheet->setCellValue('B10', 'Ingrese un valor numérico. Aplica tanto para entradas como para salidas.');
        $legendSheet->setCellValue('A11', 'Precio de Compra');
        $legendSheet->setCellValue('B11', 'SOLO PARA ENTRADAS: Ingrese un valor numérico. Se vuelve gris automáticamente si selecciona "Salida".');
        $legendSheet->setCellValue('A12', 'Impuesto');
        $legendSheet->setCellValue('B12', 'SOLO PARA ENTRADAS: Ingrese un valor numérico. Se vuelve gris automáticamente si selecciona "Salida".');
        $legendSheet->setCellValue('A13', 'Precio Unitario');
        $legendSheet->setCellValue('B13', 'SOLO PARA ENTRADAS: SE CALCULA AUTOMÁTICAMENTE: (Precio de compra + Impuesto) ÷ Cantidad. CELDA PROTEGIDA: No se puede editar manualmente. Se vuelve gris automáticamente si selecciona "Salida".');
        $legendSheet->setCellValue('A14', 'Total');
        $legendSheet->setCellValue('B14', 'SOLO PARA ENTRADAS: SE CALCULA AUTOMÁTICAMENTE: Cantidad × Precio Unitario. CELDA PROTEGIDA: No se puede editar manualmente. Se vuelve gris automáticamente si selecciona "Salida".');
        $legendSheet->setCellValue('A15', 'Observaciones');
        $legendSheet->setCellValue('B15', '(Opcional) Notas adicionales. Aplica tanto para entradas como para salidas.');
        $legendSheet->setCellValue('A16', 'Autocompletado con BUSCARV');
        $legendSheet->setCellValue('B16', 'FUNCIONAMIENTO: Cuando seleccione una clave en columna B, el insumo correspondiente aparece AUTOMÁTICAMENTE en columna C mediante fórmula BUSCARV. Alternativamente, puede seleccionar el insumo directamente del desplegable en columna C.');
        $legendSheet->setCellValue('A17', 'Formato Condicional');
        $legendSheet->setCellValue('B17', 'ALERTA VISUAL: Las columnas se vuelven grises automáticamente según el tipo de movimiento. Si selecciona "Entrada", solo las columnas relevantes para entrada permanecen activas. Si selecciona "Salida", solo las columnas relevantes para salida permanecen activas.');
        $legendSheet->setCellValue('A18', 'Instrucciones de Uso');
        $legendSheet->setCellValue('B18', 'PASOS: 1) Seleccione tipo de movimiento (Entrada/Salida) - esto determinará qué columnas usar, 2) Seleccione una clave O un insumo, 3) Complete solo las columnas que no estén en gris. FLEXIBILIDAD: El sistema se adapta automáticamente al tipo de movimiento seleccionado.');
        $legendSheet->setCellValue('A19', 'Protección de Celdas');
        $legendSheet->setCellValue('B19', 'CELDAS PROTEGIDAS: Las columnas Precio Unitario (L) y Total (M) están protegidas y no se pueden editar manualmente ya que se calculan automáticamente. El resto de celdas sí se pueden editar libremente.');
        
        // Configurar estilo de título para la leyenda
        $titleStyle = [
            'font' => ['bold' => true, 'size' => 14],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DDDDDD']
            ]
        ];
        
        // Configurar estilo de encabezados
        $headerStyle = [
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F0F0F0']
            ]
        ];
        
        // Aplicar estilos
        $legendSheet->getStyle('A1:B1')->applyFromArray($titleStyle);
        $legendSheet->getStyle('A2:A19')->applyFromArray($headerStyle);

        $legendSheet->getColumnDimension('A')->setWidth(20);
        $legendSheet->getColumnDimension('B')->setWidth(80);

        // Activar la primera hoja antes de guardar
        $spreadsheet->setActiveSheetIndex(0);
        
        // Configurar protección de celdas
        $mainSheet = $spreadsheet->getActiveSheet();
        
        // Desbloquear las celdas que sí se pueden editar (todas excepto L y M que ya están bloqueadas)
        $mainSheet->getStyle('A2:K5000')->getProtection()->setLocked(false);
        $mainSheet->getStyle('N2:N5000')->getProtection()->setLocked(false);
        
        // Habilitar protección de la hoja (las celdas L y M ya están bloqueadas)
        $mainSheet->getProtection()->setSheet(true);
        $mainSheet->getProtection()->setSort(false);
        $mainSheet->getProtection()->setInsertRows(false);
        $mainSheet->getProtection()->setFormatCells(false);
        
        // Establecer la celda activa en A2 para que el usuario pueda empezar a llenar datos inmediatamente
        $spreadsheet->getActiveSheet()->setSelectedCell('A2');

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
        $test = array();

        $rowIterator = $spreadsheet->getActiveSheet()->getRowIterator();

        while (true) {
            $cellIterator = $rowIterator->current()->getCellIterator('A', 'I');
            //var_dump($cellIterator->current()->getValue());
            if($rowIterator->current()->getRowIndex() != 1) {
                if (empty($cellIterator->current()->getValue())) {
                    break;
                }
                $data = [];
                $data['key'] = strval($cellIterator->current()->getValue()); // A - Clave
                $cellIterator->next();
                $data['ingredient'] = $cellIterator->current()->getValue(); // B - Insumo
                $cellIterator->next();
                $data['brand'] = $cellIterator->current()->getValue(); // B - Insumo
                $cellIterator->next();
                $data['presentation'] = $cellIterator->current()->getValue(); // B - Insumo
                $cellIterator->next();
                $data['category_id'] = $cellIterator->current()->getValue(); // C - Categoría
                $cellIterator->next();
                $data['um'] = $cellIterator->current()->getValue(); // D - Unidad de compra
                $cellIterator->next();
                $data['portion_um'] = $cellIterator->current()->getValue(); // E - Unidad de cocina
                $cellIterator->next();
                $data['yield'] = $cellIterator->current()->getValue(); // F - Factor de Rendimiento
                $cellIterator->next();
                $data['portions_per_unit'] = $cellIterator->current()->getValue(); // G - Porciones por unidad
                $cellIterator->next();
                $data['observations'] = $cellIterator->current()->getValue(); // H - Observaciones
                $cellIterator->next();
                $data['price'] = $cellIterator->current()->getValue(); // I - Precio
                $cellIterator->next();
                $price = preg_replace('/[^\d.]/', '', $data['price']); // Eliminar símbolos no numéricos
                $data['unit_price'] = $price / $data['portions_per_unit'];
                $yield = preg_replace('/[^\d.]/', '', $data['yield']); // Eliminar símbolos no numéricos
                $data['adjusted_price'] = $data['unit_price'] / ($yield / 100);
                $data['business_id'] = $business->id;
                $data['quantity'] = 0;

                /// extract category id
                //var_dump($data['category_id']);
                $data['category_id'] = explode(' - ', $data['category_id'])[0];
                $data['category_id'] = trim($data['category_id']);
                //var_dump($data['category_id']);
                /// check if category exists
                $category = Category::find()
                    ->where([
                        'name' => $data['category_id'],
                    ])
                    ->andWhere([
                        'or',
                        ['business_id' => $business->id],
                        ['business_id' => null]
                    ])
                    ->one();
                if(empty($category)){
                    throw new HttpException(400, "No existe ninguna categoría con el identificador \"{$data[2]}\"");
                }

                $data['category_id'] = $category->id;

                $ingredientData[] = $data;
                $test[] = $data;
                
            }

            $rowIterator->next();
        }
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($ingredientData as $data) {
                $ingredientStock = new IngredientStock();
                $_price = $data['price'];
                unset($data['price']);
                if ($ingredientStock->load($data, '') && $ingredientStock->save()) {
                    $price = new StockPrice([
                        'price' => $_price,
                        'stock_id' => $ingredientStock->id,
                        'date' => date('Y-m-d')
                        ]);

                    if (!($price->load($data, '') && $price->save()) && $price->hasErrors()) {
                        throw new HttpException(400, json_encode($price->errors));
                    }
                }elseif ($ingredientStock->hasErrors()) {
                    throw new HttpException(400, json_encode($ingredientStock->errors));
                }
            }
            $transaction->commit();
        }catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
//        \Yii::$app->db->createCommand()
//            ->batchInsert(
//                'ingredient_stock',
//                ['key', 'ingredient', 'category_id', 'um', 'portion_um', 'yield','portions_per_unit', 'observations', 'business_id', 'quantity'],
//                $ingredientData
//            )
//            ->execute();

        return;
    }

    public static function importIngredientsAdmin($fileName)
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileName);

        $ingredientData = [];

        $rowIterator = $spreadsheet->getActiveSheet()->getRowIterator();

        while (true) {
            $cellIterator = $rowIterator->current()->getCellIterator('A', 'C');
            if($rowIterator->current()->getRowIndex() != 1) {
                if (empty($cellIterator->current()->getValue())) {
                    break;
                }
                $data = [];
                $data[] = $cellIterator->current()->getValue(); // A - Clave
                $cellIterator->next();
                $data[] = $cellIterator->current()->getValue(); // B - Unidad de Medida
                $cellIterator->next();
                $data[] = $cellIterator->current()->getValue(); // C - Categoría


                /// extract category id
                $data[2] = explode(' - ', $data[2])[0];
                $data[2] = trim($data[2]);

                /// check if category exists
                $category = Category::find()
                    ->where([
                        'key_prefix' => $data[2],
                        'business_id' => null
                    ])
                    ->one();

                if(empty($category)){
                    throw new HttpException(400, "No existe ninguna categoría con el identificador \"{$data[2]}\"");
                }

                $data[2] = $category->id;

                $ingredientData[] = $data;

            }

            $rowIterator->next();
        }

        \Yii::$app->db->createCommand()
            ->batchInsert(
                'ingredient',
                ['name', 'um', 'category_id'],
                $ingredientData
            )
            ->execute();

        return;
    }

    public static function importMovements(Business $business, $fileName)
    {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileName);

        $movementsData = [];
        $errors = [];

        $rowIterator = $spreadsheet->getActiveSheet()->getRowIterator();
        while (true) {
            $cellIterator = $rowIterator->current()->getCellIterator('A', 'N');
            if($rowIterator->current()->getRowIndex() != 1) {
                if (empty($cellIterator->current()->getValue())) {
                    break;
                }
                $data = [];
                $rowNumber = $rowIterator->current()->getRowIndex();
                
                // A - Movimiento (Entrada/Salida)
                $movementTypeValue = trim($cellIterator->current()->getValue());
                
                // Convertir descripciones en español a códigos internos
                $movementTypeMap = [
                    'Entrada' => Movement::TYPE_INPUT,
                    'Salida' => Movement::TYPE_OUTPUT
                ];
                
                $data['type'] = isset($movementTypeMap[$movementTypeValue]) ? 
                    $movementTypeMap[$movementTypeValue] : Movement::TYPE_INPUT; // Por defecto entrada
                $cellIterator->next();
                // D - Fecha
                try {
                    $dateValue = $cellIterator->current()->getValue();
                    if (is_numeric($dateValue)) {
                        $createdAt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
                        $data['created_at'] = $createdAt->format('Y-m-d');
                    } else {
                        // Si no es numérico, intentar parsear como string de fecha
                        $data['created_at'] = date('Y-m-d', strtotime($dateValue));
                    }
                } catch (\Exception $e) {
                    $errors[] = "Fila $rowNumber: Error en formato de fecha";
                    $data['created_at'] = date('Y-m-d'); // Fecha por defecto
                }
                $cellIterator->next();
               
                
                // C - Insumo (nombre)
                $data['ingredient_name'] = trim($cellIterator->current()->getValue());
                $cellIterator->next();
                
                 // B - Clave
                $data['key'] = trim($cellIterator->current()->getValue());
                $cellIterator->next();
                
                // E - Proveedor (solo para entradas)
                $providerValue = trim($cellIterator->current()->getValue());
                // Solo asignar proveedor si es entrada
                if ($data['type'] === Movement::TYPE_INPUT) {
                    // Para entradas, convertir "Por definir" y valores vacíos a null
                    if ($providerValue === 'Por definir' || empty($providerValue)) {
                        $data['provider'] = null;
                    } else {
                        $data['provider'] = $providerValue;
                    }
                } else {
                    // Para otros tipos, no asignar provider field en esta sección
                    $data['provider'] = null;
                }
                $cellIterator->next();
                
                // F - Tipo de Pago
                $paymentTypeValue = trim($cellIterator->current()->getValue());
                
                // Convertir descripciones en español a códigos internos
                $paymentTypeMap = [
                    'Efectivo' => Movement::PAYMENT_METHOD_CASH,
                    'Transferencia Bancaria' => Movement::PAYMENT_METHOD_TRANSFER,
                    'Cheque' => Movement::PAYMENT_METHOD_CHECK,
                    'Tarjeta de Crédito' => Movement::PAYMENT_METHOD_CREDIT_CARD,
                    'Tarjeta de Débito' => Movement::PAYMENT_METHOD_DEBIT_CARD,
                    'Otro Método de Pago' => Movement::PAYMENT_METHOD_OTHER
                ];
                
                if ($paymentTypeValue === 'Por definir') {
                    $data['payment_type'] = null;
                } else {
                    $data['payment_type'] = isset($paymentTypeMap[$paymentTypeValue]) ? 
                        $paymentTypeMap[$paymentTypeValue] : $paymentTypeValue;
                }
                $cellIterator->next();
                
                // G - Factura
                $data['invoice'] = trim($cellIterator->current()->getValue());
                $cellIterator->next();
                
                // H - Centro de Consumo (solo para salidas)
                $consumptionCenterValue = trim($cellIterator->current()->getValue());
                // Para movimientos de salida, el centro de consumo se guarda en el campo provider
                if ($data['type'] === Movement::TYPE_OUTPUT && !empty($consumptionCenterValue)) {
                    $data['provider'] = $consumptionCenterValue;
                }
                $cellIterator->next();
                
                // I - Cantidad
                $data['quantity'] = $cellIterator->current()->getValue();
                $cellIterator->next();
                
                // J - Precio de Compra
                $data['amount'] = $cellIterator->current()->getValue();
                $cellIterator->next();
                
                // K - Impuesto
                $data['tax'] = $cellIterator->current()->getValue();
                $cellIterator->next();
                
                // L - Precio Unitario
                $unitPriceValue = $cellIterator->current()->getValue();
                $cellIterator->next();
                
                // M - Total
                $totalValue = $cellIterator->current()->getValue();
                $cellIterator->next();
                
                // N - Observaciones
                $data['observations'] = trim($cellIterator->current()->getValue());
                
                // Calcular unit_price y total si son fórmulas o están vacíos
                $quantity = floatval($data['quantity']);
                $amount = floatval($data['amount']); // Precio de compra
                $tax = floatval($data['tax']); // Impuesto
                
                // Calcular unit_price: (Precio de compra + Impuesto) / Cantidad
                if ($quantity > 0) {
                    $calculatedUnitPrice = ($amount + $tax) / $quantity;
                } else {
                    $calculatedUnitPrice = 0;
                }
                
                // Calcular total: Cantidad × Precio Unitario
                $calculatedTotal = $quantity * $calculatedUnitPrice;
                
                // Usar valores calculados si el valor de Excel es una fórmula o está vacío
                if (is_string($unitPriceValue) && (strpos($unitPriceValue, '=') === 0 || empty($unitPriceValue))) {
                    $data['unit_price'] = $calculatedUnitPrice;
                } else {
                    $data['unit_price'] = floatval($unitPriceValue);
                }
                
                if (is_string($totalValue) && (strpos($totalValue, '=') === 0 || empty($totalValue))) {
                    $data['total'] = $calculatedTotal;
                } else {
                    $data['total'] = floatval($totalValue);
                }

                $data['business_id'] = $business->id;
                $data['row_number'] = $rowNumber; // Para tracking de errores

                $movementsData[] = $data;
            }

            $rowIterator->next();
        }

        // Procesar los datos y buscar ingredientes
        $processedMovements = [];
        foreach ($movementsData as $movement) {
            $ingredient = null;
            $rowNumber = $movement['row_number'];
            
            // Estrategia de búsqueda mejorada:
            // 1. Primero por clave si está disponible
            if (!empty($movement['key'])) {
                $ingredient = IngredientStock::find()
                    ->where([
                        'key' => $movement['key'],
                        'business_id' => $movement['business_id']
                    ])
                    ->one();
            }
                
            // 2. Si no se encuentra por clave, buscar por nombre exacto
            if (!$ingredient && !empty($movement['ingredient_name'])) {
                $ingredient = IngredientStock::find()
                    ->where([
                        'ingredient' => $movement['ingredient_name'],
                        'business_id' => $movement['business_id']
                    ])
                    ->one();
            }
            
            // 3. Si aún no se encuentra, buscar por nombre con LIKE (búsqueda parcial)
            if (!$ingredient && !empty($movement['ingredient_name'])) {
                $ingredient = IngredientStock::find()
                    ->where(['like', 'ingredient', $movement['ingredient_name'], false])
                    ->andWhere(['business_id' => $movement['business_id']])
                    ->one();
            }
            
            // Limpiar datos innecesarios
            unset($movement['key']);
            unset($movement['ingredient_name']);
            unset($movement['row_number']);
            
            if ($ingredient) {
                $movement['ingredient_id'] = $ingredient->id;
                
                // Asegurarse de que um esté definido desde el ingrediente
                $movement['um'] = $ingredient->portion_um;
                
                // Validar que los movimientos de salida tengan centro de consumo
                if ($movement['type'] === Movement::TYPE_OUTPUT && empty($movement['provider'])) {
                    $errors[] = "Fila $rowNumber: Los movimientos de salida deben tener un centro de consumo especificado en la columna H";
                    continue;
                }
                
                // Para movimientos de entrada, el proveedor puede ser null (cuando es "Por definir")
                // No agregamos validación adicional para entrada ya que provider puede ser null
                
                $processedMovements[] = $movement;
            } else {
                $errors[] = "Fila $rowNumber: No se encontró el insumo especificado";
            }
        }

        // Guardar movimientos válidos
        $savedCount = 0;
        $inputCount = 0;
        $outputCount = 0;
        
        // Contar tipos de movimientos para logging
        foreach ($processedMovements as $movData) {
            if ($movData['type'] === Movement::TYPE_INPUT) {
                $inputCount++;
            } elseif ($movData['type'] === Movement::TYPE_OUTPUT) {
                $outputCount++;
            }
        }
        
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($processedMovements as $movementData) {
                $movement = new Movement($movementData);
                // El tipo ya viene definido desde el Excel en $movementData['type']
                
                if ($movement->save()) {
                    $savedCount++;
                } else {
                    $validationErrors = [];
                    foreach ($movement->errors as $field => $fieldErrors) {
                        $validationErrors[] = "$field: " . implode(', ', $fieldErrors);
                    }
                    $errors[] = "Error al guardar movimiento (datos: " . json_encode($movementData) . "): " . implode('; ', $validationErrors);
                }
            }
            
            $transaction->commit();
            
            // Log de resultados
            \Yii::info("Importación de movimientos completada. Guardados: $savedCount");
            if (!empty($errors)) {
                \Yii::warning("Errores durante la importación: " . implode(', ', $errors));
            }
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new HttpException(500, "Error durante la importación: " . $e->getMessage());
        }

        return [
            'success' => true,
            'saved_count' => $savedCount,
            'errors' => $errors
        ];
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
        $activeWorksheet->setCellValue("G1", "EQ. Uni. Cocina");
        $activeWorksheet->setCellValue("H1", "Observaciones");
        $activeWorksheet->setCellValue("I1", "Precio");
        $activeWorksheet->freezePane("C2");

        $ingredients = $business->getIngredientStocks()->all();
        $currentRow = 2;
        foreach ($ingredients as $ingredient){
            /** @var $ingredient IngredientStock */
            $activeWorksheet->setCellValue("A$currentRow", $ingredient->key);
            $activeWorksheet->setCellValue("B$currentRow", $ingredient->ingredient);
            $activeWorksheet->setCellValue("C$currentRow", $ingredient->category->name);
            $activeWorksheet->setCellValue("D$currentRow", $ingredient->um);
            $activeWorksheet->setCellValue("E$currentRow", $ingredient->portion_um);
            $activeWorksheet->setCellValue("F$currentRow", $ingredient->yield);
            $activeWorksheet->setCellValue("G$currentRow", $ingredient->portions_per_unit);
            $activeWorksheet->setCellValue("H$currentRow", $ingredient->observations);
            $activeWorksheet->setCellValue("I$currentRow",$ingredient->lastPrice/$ingredient->portions_per_unit);

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

    public static function generateAdminIngredientsTemplate()
    {
        /** @var Category[] $categories */
        $categories = Category::find()->where([
            'or',
            ['business_id' => null],
        ])->all();

        /** @var UnitOfMeasurement[] $unitOfMeasurements */
        $unitOfMeasurements = UnitOfMeasurement::find()
            ->select('name')
            ->groupBy('name')
            ->all();

        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();

        $activeWorksheet->setCellValue("A1", "Nombre");
        $activeWorksheet->setCellValue("B1", "Unidad de Medida");
        $activeWorksheet->setCellValue("C1", "Categoría");


        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);

        // Create a named range for categories
        $categorySheet = $spreadsheet->createSheet();
        $categorySheet->setTitle('Categorias');
        $categorySheet->setCellValue('A1', 'Categoría');

        $row = 2;
        foreach ($categories as $category) {
            $categorySheet->setCellValue("A$row", sprintf("%s - %s", $category->key_prefix, $category->name));
            $row++;
        }

        $spreadsheet->addNamedRange(
            new \PhpOffice\PhpSpreadsheet\NamedRange('Categorias', $categorySheet, 'A2:A' . ($row - 1))
        );



        // Apply data validation to the category column
        $dataValidation = $spreadsheet->getActiveSheet()->getCell('C2')->getDataValidation();
        $dataValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $dataValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $dataValidation->setAllowBlank(false);
        $dataValidation->setShowInputMessage(true);
        $dataValidation->setShowErrorMessage(true);
        $dataValidation->setShowDropDown(true);
        $dataValidation->setErrorTitle('Error de entrada');
        $dataValidation->setError('Este valor no es admitido');
        $dataValidation->setPromptTitle('Selecciona una categoría');
        $dataValidation->setPrompt('Por favor, selecciona un valor del desplegable.');
        $dataValidation->setFormula1('=Categorias!$A$2:$A$' . ($row - 1));

        for ($i = 2; $i <= 5000; $i++) {
            $spreadsheet->getActiveSheet()->getCell("C$i")->setDataValidation(clone $dataValidation);
        }

        // Create a named range for unit of measuerements
        $umSheet = $spreadsheet->createSheet();
        $umSheet->setTitle('UMs');
        $umSheet->setCellValue('A1', 'Unidad de medida');

        $row = 2;
        foreach ($unitOfMeasurements as $um) {
            $umSheet->setCellValue("A$row", $um->name);
            $row++;
        }

        $spreadsheet->addNamedRange(
            new \PhpOffice\PhpSpreadsheet\NamedRange('UMs', $categorySheet, 'A2:A' . ($row - 1))
        );

        // Apply data validation to the um column
        $dataValidation = $spreadsheet->getActiveSheet()->getCell('B1')->getDataValidation();
        $dataValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $dataValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $dataValidation->setAllowBlank(false);
        $dataValidation->setShowInputMessage(true);
        $dataValidation->setShowErrorMessage(true);
        $dataValidation->setShowDropDown(true);
        $dataValidation->setErrorTitle('Error de entrada');
        $dataValidation->setError('Este valor no es admitido');
        $dataValidation->setPromptTitle('Selecciona una unidad de medida');
        $dataValidation->setPrompt('Por favor, selecciona un valor del desplegable.');
        $dataValidation->setFormula1('=UMs!$A$2:$A$' . ($row - 1));

        for ($i = 2; $i <= 5000; $i++) {
            $spreadsheet->getActiveSheet()->getCell("B$i")->setDataValidation(clone $dataValidation);
        }

        // Establecer la celda activa en A2 para que el usuario pueda empezar a llenar datos inmediatamente
        $spreadsheet->getActiveSheet()->setSelectedCell('A2');

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Plantilla_para_importar_ingredientes.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
        $writer->save('php://output');
        exit(200);
    }
    
    public static function importRecipe(Business $business, $fileName)
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileName);
            $recipeData = [];
            $ingredientData = [];
    
            $recipesSheet = $spreadsheet->getSheetByName('FICHA GENERAL DE LA RECETA');
            $ingredientsSheet = $spreadsheet->getSheetByName('INGREDIENTES');
    
            if ($recipesSheet === null) {
                throw new HttpException(400, 'La hoja "Recipes" no se encontró en el archivo Excel.');
            }
    
            if ($ingredientsSheet === null) {
                throw new HttpException(400, 'La hoja "Ingredients" no se encontró en el archivo Excel.');
            }
            // Importar recetas
            $rowIterator = $recipesSheet->getRowIterator();
            while (true) {
                $cellIterator = $rowIterator->current()->getCellIterator('A', 'M');
                if ($rowIterator->current()->getRowIndex() != 1) {
                    if (empty($cellIterator->current()->getValue())) {
                        break;
                    }
                    $data = [];
                    $data['title'] = $cellIterator->current()->getValue(); // A - Nombre
                    $cellIterator->next();
                    $data['type_of_recipe'] = $cellIterator->current()->getValue(); // B - Tipo de Receta
                    $cellIterator->next();
                    $timeValue = $cellIterator->current()->getValue();
                    $cellIterator->next();
                    $timeUnit = $cellIterator->current()->getValue(); // Time unit (minutes, hours, days)
                    $data['time_of_preparation'] = $timeValue . ' ' . $timeUnit;
                    $cellIterator->next();
                    $data['yield'] = $cellIterator->current()->getValue(); // E - Rendimiento
                    $cellIterator->next();
                    $data['yield_um'] = $cellIterator->current()->getValue(); // F - Unidad de medida final
                    $cellIterator->next();
                    $data['um'] = $cellIterator->current()->getValue(); // F - Unidad de medida final
                    $cellIterator->next();
                    $portionsValue = $cellIterator->current();
                    $data['portions'] = $portionsValue->getCalculatedValue(); // G - Porciones
                    if (!is_numeric($data['portions'])) {
                        // Try to clean/extract numeric value if it's not already numeric
                        $data['portions'] = preg_replace('/[^\d.]/', '', $data['portions']);
                        // If still empty or not numeric, default to 1
                        if (empty($data['portions']) || !is_numeric($data['portions'])) {
                            $data['portions'] = 1;
                        }
                    }
                    $cellIterator->next();
                    $timeValue = $cellIterator->current()->getValue();
                    $cellIterator->next();
                    $timeUnit = $cellIterator->current()->getValue();
                    $data['lifetime'] = $timeValue . ' ' . $timeUnit; // H-I - Duración
                    $cellIterator->next();
                    $priceRaw = $cellIterator->current()->getValue();
                    if (is_numeric($priceRaw)) {
                        $data['price'] = floatval($priceRaw);
                    } else {
                        $cleanPrice = preg_replace('/[^\d.]/', '', strval($priceRaw));
                        $data['price'] = floatval($cleanPrice);
                    }
                    $cellIterator->next();                    
                    $data['is_food'] = $cellIterator->current()->getValue() === 'Alimento'; // K - Alimento o Bebida
                    $cellIterator->next();
                    $convoyName = $cellIterator->current()->getValue(); // L - Convoy
                    $convoy = Convoy::find()->where(['name' => $convoyName, 'business_id' => $business->id])->one();
                    if ($convoy) {
                        $data['convoy_id'] = $convoy->id;
                    }
                    $cellIterator->next();
                    
                    $data['business_id'] = $business->id;
                    
    
                    $recipeData[] = $data;
                }
                $rowIterator->next();
            }
            // Importar ingredientes agrupados por receta
           $rowIterator = $ingredientsSheet->getRowIterator();
            while (true) {
                $cellIterator = $rowIterator->current()->getCellIterator('A', 'F');
                if ($rowIterator->current()->getRowIndex() != 1) {
                    if (empty($cellIterator->current()->getValue())) {
                        break;
                    }
                    $data = [];
                    $data['recipe'] = $cellIterator->current()->getValue(); // A - Receta
                    $cellIterator->next();
                    $data['type'] = $cellIterator->current()->getValue(); // B - Tipo (INSUMO/SUBRECETA)
                    $cellIterator->next();
                    $data['item'] = $cellIterator->current()->getValue(); // C - Item (Insumo o Subreceta)
                    $cellIterator->next();
                    $data['quantity'] = $cellIterator->current()->getValue(); // D - Cantidad
                    $cellIterator->next();
                    $data['portion_um'] = $cellIterator->current()->getValue(); // E - UM
                    $cellIterator->next();
                    $data['lastPrice'] = $cellIterator->current()->getValue(); // F - Costo

                    $data['business_id'] = $business->id;

                    if (!isset($ingredientData[$data['recipe']])) {
                        $ingredientData[$data['recipe']] = [];
                    }
                    $ingredientData[$data['recipe']][] = $data;
                }
                $rowIterator->next();
            }


            
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                foreach ($recipeData as $data) {
                    $recipe = new StandardRecipe();
                    $recipe['type'] = \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN;
                    //die(var_dump($recipe->load($data, '')));
                    $recipe->load($data, '');
                    if (!$recipe->save()){
                        var_dump($recipe->errors);
                        throw new HttpException(400, "Error al guardar receta: " . json_encode($recipe->errors));
                    }
                    if ($recipe->load($data, '') && $recipe->validate() && $recipe->save()) {
                        //die(var_dump(isset($ingredientData[$data['title']])));
                        if (isset($ingredientData[$data['title']])) {
                            foreach ($ingredientData[$data['title']] as $ingredient) {
                                if ($ingredient['type'] === 'INSUMO') {
                                    $ingredientStock = IngredientStock::find()
                                        ->where(['ingredient' => $ingredient['item'], 'business_id' => $business->id])
                                        ->one();

                                    if (!$ingredientStock) {
                                        throw new HttpException(400, "No se encontró el insumo \"{$ingredient['item']}\" en el negocio.");
                                    }

                                    $ingredientRelation = new IngredientStandardRecipe();
                                    $ingredientRelation->ingredient_id = $ingredientStock->id;
                                    $ingredientRelation->standard_recipe_id = $recipe->id;
                                    $ingredientRelation->quantity = $ingredient['quantity'];
                                    $ingredientRelation->save();
                                } else if ($ingredient['type'] === 'SUBRECETA') {
                                    $subrecipe = StandardRecipe::find()
                                        ->where([
                                            'title' => $ingredient['item'],
                                            'business_id' => $business->id,
                                            'type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB
                                        ])
                                        ->one();

                                    if (!$subrecipe) {
                                        throw new HttpException(400, "No se encontró la subreceta \"{$ingredient['item']}\" en el negocio.");
                                    }

                                    // Insert the relation between the main recipe and subrecipe directly to the database
                                    Yii::$app->db->createCommand()
                                        ->insert(
                                            'standard_recipe_sub_standard_recipe',
                                            [
                                                'sub_standard_recipe_id' => $subrecipe->id,
                                                'quantity' => $ingredient['quantity'],
                                                'standard_recipe_id' => $recipe->id
                                            ]
                                        )
                                        ->execute();
                                }
                            }
                        }
                    } else {
                        Yii::error("Error al guardar receta: " . json_encode($recipe->errors));
                    }
                }
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
    
            return;
        } catch (\Exception $e) {
            Yii::error("Error al importar recetas: " . $e->getMessage());
            Yii::error($e->getTraceAsString());
            throw new HttpException(500, "Error al importar recetas: " . $e->getMessage());
        }
    }
    public static function importSubRecipe(Business $business, $fileName)
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileName);
            $recipeData = [];
            $ingredientData = [];
    
            $recipesSheet = $spreadsheet->getSheetByName('FICHA GENERAL DE LA SUBRECETA');
            $ingredientsSheet = $spreadsheet->getSheetByName('INGREDIENTES');
    
            if ($recipesSheet === null) {
                throw new HttpException(400, 'La hoja "Recipes" no se encontró en el archivo Excel.');
            }
    
            if ($ingredientsSheet === null) {
                throw new HttpException(400, 'La hoja "Ingredients" no se encontró en el archivo Excel.');
            }
            // Importar recetas
            $rowIterator = $recipesSheet->getRowIterator();
            while (true) {
                $cellIterator = $rowIterator->current()->getCellIterator('A', 'M');
                if ($rowIterator->current()->getRowIndex() != 1) {
                    if (empty($cellIterator->current()->getValue())) {
                        break;
                    }
                    $data = [];
                    $data['title'] = $cellIterator->current()->getValue(); // A - Nombre
                    $cellIterator->next();
                    $data['type_of_recipe'] = $cellIterator->current()->getValue(); // B - Tipo de Receta
                    $cellIterator->next();
                    $timeValue = $cellIterator->current()->getValue();
                    $cellIterator->next();
                    $timeUnit = $cellIterator->current()->getValue(); // Time unit (minutes, hours, days)
                    $data['time_of_preparation'] = $timeValue . ' ' . $timeUnit;
                    $cellIterator->next();
                    $data['yield'] = $cellIterator->current()->getValue(); // E - Rendimiento
                    $cellIterator->next();
                    $data['yield_um'] = $cellIterator->current()->getValue(); // F - Unidad de medida final
                    $cellIterator->next();
                    $portionsValue = $cellIterator->current();
                    $data['portions'] = $portionsValue->getCalculatedValue(); // F - Porciones
                    if (!is_numeric($data['portions'])) {
                        // Try to clean/extract numeric value if it's not already numeric
                        $data['portions'] = preg_replace('/[^\d.]/', '', $data['portions']);
                        // If still empty or not numeric, default to 1
                        if (empty($data['portions']) || !is_numeric($data['portions'])) {
                            $data['portions'] = 1;
                        }
                    }
                    $cellIterator->next();
                    //var_dump($data['portions']);
                    $timeValue = $cellIterator->current()->getValue();
                    $cellIterator->next();
                    $timeUnit = $cellIterator->current()->getValue();
                    $data['lifetime'] = $timeValue . ' ' . $timeUnit; // G - Duración
                    $cellIterator->next();
                    $data['um'] = $cellIterator->current()->getValue(); // E - Rendimiento UM
                    $cellIterator->next();
                    
                    $data['business_id'] = $business->id;
                    
    
                    $recipeData[] = $data;
                }
                $rowIterator->next();
            }
            // Importar ingredientes agrupados por receta
            $rowIterator = $ingredientsSheet->getRowIterator();
            while (true) {
                $cellIterator = $rowIterator->current()->getCellIterator('A', 'F');
                if ($rowIterator->current()->getRowIndex() != 1) {
                    if (empty($cellIterator->current()->getValue())) {
                        break;
                    }
                    $data = [];
                    $data['recipe'] = $cellIterator->current()->getValue(); // A - Receta
                    $cellIterator->next();
                    $data['type'] = $cellIterator->current()->getValue(); // B - Tipo (INSUMO/SUBRECETA)
                    $cellIterator->next();
                    $data['item'] = $cellIterator->current()->getValue(); // C - Item (Insumo o Subreceta)
                    $cellIterator->next();
                    $data['quantity'] = $cellIterator->current()->getValue(); // D - Cantidad
                    $cellIterator->next();
                    $data['portion_um'] = $cellIterator->current()->getValue(); // E - UM
                    $cellIterator->next();
                    $data['lastPrice'] = $cellIterator->current()->getValue(); // F - Costo

                    $data['business_id'] = $business->id;

                    if (!isset($ingredientData[$data['recipe']])) {
                        $ingredientData[$data['recipe']] = [];
                    }
                    $ingredientData[$data['recipe']][] = $data;
                }
                $rowIterator->next();
            }


            
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                foreach ($recipeData as $data) {
                    $recipe = new StandardRecipe();
                    $recipe['type'] = \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB;
                    $recipe->load($data, '');
                    if (!$recipe->save()){
                        var_dump($recipe->errors);
                        throw new HttpException(400, "Error al guardar receta: " . json_encode($recipe->errors));
                    }
                    if ($recipe->load($data, '') && $recipe->validate() && $recipe->save()) {
                        //var_dump($data['title']);
                        if (isset($ingredientData[$data['title']])) {
                            foreach ($ingredientData[$data['title']] as $ingredient) {
                                if ($ingredient['type'] === 'INSUMO') {
                                    $ingredientStock = IngredientStock::find()
                                        ->where(['ingredient' => $ingredient['item'], 'business_id' => $business->id])
                                        ->one();

                                    if (!$ingredientStock) {
                                        throw new HttpException(400, "No se encontró el insumo \"{$ingredient['item']}\" en el negocio.");
                                    }

                                    $ingredientRelation = new IngredientStandardRecipe();
                                    $ingredientRelation->ingredient_id = $ingredientStock->id;
                                    $ingredientRelation->standard_recipe_id = $recipe->id;
                                    $ingredientRelation->quantity = $ingredient['quantity'];
                                    $ingredientRelation->save();
                                } else if ($ingredient['type'] === 'SUBRECETA') {
                                    $subrecipe = StandardRecipe::find()
                                        ->where([
                                            'title' => $ingredient['item'],
                                            'business_id' => $business->id,
                                            'type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB
                                        ])
                                        ->one();

                                    if (!$subrecipe) {
                                        throw new HttpException(400, "No se encontró la subreceta \"{$ingredient['item']}\" en el negocio.");
                                    }

                                    // Insert the relation between the main recipe and subrecipe directly to the database
                                    Yii::$app->db->createCommand()
                                        ->insert(
                                            'standard_recipe_sub_standard_recipe',
                                            [
                                                'sub_standard_recipe_id' => $subrecipe->id,
                                                'quantity' => $ingredient['quantity'],
                                                'standard_recipe_id' => $recipe->id
                                            ]
                                        )
                                        ->execute();
                                }
                            }
                        }
                    } else {
                        Yii::error("Error al guardar receta: " . json_encode($recipe->errors));
                    }
                }
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
    
            return;
        } catch (\Exception $e) {
            Yii::error("Error al importar subrecetas: " . $e->getMessage());
            Yii::error($e->getTraceAsString());
            throw new HttpException(500, "Error al importar subrecetas: " . $e->getMessage());
        }
    }

}

