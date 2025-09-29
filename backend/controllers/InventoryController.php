<?php
namespace backend\controllers;

use Yii;
use common\models\Inventory;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

class InventoryController extends Controller 
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }


    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Inventory::find(),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

public function actionCreate()
{
    $model = new Inventory();
    
    // Si es una petición GET (búsqueda o filtrado), solo mostrar la vista
    if (Yii::$app->request->isGet) {
        return $this->render('create', [
            'model' => $model,
        ]);
    }
    
    // Si es POST, procesar el guardado del inventario
    if (Yii::$app->request->isPost) {
        $post = Yii::$app->request->post();
        $fecha = $post['Inventory']['fecha'] ?? null;
        $inventarios = $post['inventario'] ?? [];
        $businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
        $businessId = $businessData['id'] ?? null;
        $errors = [];
        
        // Obtener todos los insumos del negocio
        $allStocks = \common\models\IngredientStock::find()->where(['business_id' => $businessId])->all();
        $allIds = array_map(function($stock) { return $stock->id; }, $allStocks);
        
        $dateEnd = date('Y-m-d H:i:s');
        foreach ($allIds as $insumoId) {
            $data = isset($inventarios[$insumoId]) ? $inventarios[$insumoId] : [];
            $inv = new Inventory();
            $inv->ingredient_stock_id = $insumoId;
            $inv->business_id = $businessId;
            $inv->fecha = $fecha;
            $inv->date_end = $dateEnd;
            $inv->inventario_almacen = isset($data['inventario_almacen']) && $data['inventario_almacen'] !== '' ? $data['inventario_almacen'] : 0;
            $inv->inventario_cocina = isset($data['inventario_cocina']) && $data['inventario_cocina'] !== '' ? $data['inventario_cocina'] : 0;
            $inv->inventario_barra = isset($data['inventario_barra']) && $data['inventario_barra'] !== '' ? $data['inventario_barra'] : 0;
            $inv->inventario_servicio = isset($data['inventario_servicio']) && $data['inventario_servicio'] !== '' ? $data['inventario_servicio'] : 0;
            $inv->inventario_otro = isset($data['inventario_otro']) && $data['inventario_otro'] !== '' ? $data['inventario_otro'] : 0;
            
            if (!$inv->save()) {
                $errors[$insumoId] = $inv->getErrors();
            }
        }
        
        if (empty($errors)) {
            Yii::$app->session->setFlash('success', 'Inventario guardado correctamente.');
            return $this->redirect(['index']);
        } else {
            Yii::$app->session->setFlash('error', 'Error al guardar el inventario.');
            // Puedes mostrar los errores específicos si lo necesitas
        }
    }
    
    return $this->render('create', [
        'model' => $model,
    ]);
}

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Inventory::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested Inventory does not exist.');
    }
    public function actionDetalle($fecha)
    {
        $searchModel = new \common\models\InventorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $fecha);
        // Obtener la fecha de finalización del primer registro de inventario para esa fecha
        $firstInventory = \common\models\Inventory::find()->where(['fecha' => $fecha])->orderBy(['id' => SORT_ASC])->one();
        $dateEnd = $firstInventory ? $firstInventory->date_end : null;
        return $this->render('detalle', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'fecha' => $fecha,
            'dateEnd' => $dateEnd,
        ]);
    }
    /**
     * Exporta una plantilla de inventario en Excel con todos los insumos y columnas de existencias.
     * La fecha actual se pone en la primera fila y se protege para que no se pueda modificar.
     */
   public function actionExportPlantillaInventario()
{
    $businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
    $businessId = $businessData['id'] ?? null;
    $insumos = \common\models\IngredientStock::find()->where(['business_id' => $businessId])->all();

    $fechaActual = date('Y-m-d H:i');

    // Crear el documento
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Inventario');

    // // ====== 1. TÍTULO GENERAL ======
    // $sheet->mergeCells('A1:H1');
    // $sheet->setCellValue('A1', '📦 Plantilla de Inventario');
    // $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    // $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

    // ====== 2. FECHA ======
    $sheet->setCellValue('A2', 'Fecha de generación:');
    $sheet->setCellValue('B2', $fechaActual);
    $sheet->getStyle('A2:B2')->getFont()->setBold(true);
    $sheet->getStyle('B2')->getProtection()->setLocked(true); // Solo la fecha protegida

    // ====== 3. ENCABEZADOS ======
    $headers = [
        'Insumo', 
        'Unidad', 
        'Categoría',
        'Existencia Almacén', 
        'Existencia Cocina', 
        'Existencia Barra', 
        'Existencia Servicio', 
        'Existencia Otro'
    ];
    $headerRow = 4;

    $col = 1;
    foreach ($headers as $header) {
        $sheet->setCellValueByColumnAndRow($col, $headerRow, $header);
        $col++;
    }

    // Estilo de encabezados
    $sheet->getStyle("A{$headerRow}:H{$headerRow}")->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FFFFFF']
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => 'CCCCCC']
            ]
        ]
    ]);

    // ====== 4. CONTENIDO ======
    $row = $headerRow + 1;
    foreach ($insumos as $insumo) {
        $sheet->setCellValue("A{$row}", $insumo->ingredient);
        $sheet->setCellValue("B{$row}", $insumo->um);
        $sheet->setCellValue("C{$row}", $insumo->category ? $insumo->category->name : '-');

        // Columnas vacías para llenado manual
        for ($col = 4; $col <= 8; $col++) {
            $sheet->setCellValueByColumnAndRow($col, $row, '');
        }
        $row++;
    }

    $lastRow = $row - 1;

    // Estilo para el contenido
    $sheet->getStyle("A" . ($headerRow + 1) . ":H{$lastRow}")->applyFromArray([
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => 'DDDDDD']
            ]
        ]
    ]);

    // ====== 5. AUTOAJUSTE DE COLUMNAS ======
    foreach (range('A', 'H') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    // ====== 6. CONGELAR FILAS DE ENCABEZADOS ======
    $sheet->freezePane("A5");

    // ====== 7. PROTECCIÓN (solo fecha bloqueada) ======
    $protection = $sheet->getProtection();
    $protection->setSheet(true);
    $protection->setPassword('inventario');
    $sheet->getStyle("A2:B2")->getProtection()->setLocked(true);
    $sheet->getStyle("A5:H{$lastRow}")->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);

    // ====== 8. EXPORTAR ARCHIVO ======
    $filename = 'plantilla_inventario_' . $fechaActual . '.xlsx';
    \Yii::$app->response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    \Yii::$app->response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');
    \Yii::$app->response->headers->set('Cache-Control', 'max-age=0');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    ob_start();
    $writer->save('php://output');
    $content = ob_get_clean();
    \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
    \Yii::$app->response->content = $content;
    return \Yii::$app->response;
}

 /**
     * Importa los datos de inventario desde la plantilla Excel exportada.
     */
    public function actionImportPlantillaInventario()
    {
        $file = UploadedFile::getInstanceByName('inventory-file');
        if (Yii::$app->request->isPost) {
            if ($file) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->tempName);
                $sheet = $spreadsheet->getActiveSheet();

                // Leer fecha de la celda B2
                $fecha = $sheet->getCell('B2')->getValue();
                if (!$fecha) {
                    Yii::$app->session->setFlash('error', 'No se encontró la fecha en la plantilla.');
                    return $this->redirect(['import-plantilla-inventario']);
                }

                // Leer insumos desde la fila 5 en adelante
                $row = 5;
                $businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
                $businessId = $businessData['id'] ?? null;
                $errores = [];
                $guardados = 0;
                $dateEnd = date('Y-m-d H:i:s');
                while (true) {
                    $insumoNombre = trim($sheet->getCell("A$row")->getValue());
                    if ($insumoNombre === null || $insumoNombre === '') {
                        break;
                    }
                    $unidad = $sheet->getCell("B$row")->getValue();
                    $categoria = $sheet->getCell("C$row")->getValue();
                    $existencia_almacen = $sheet->getCell("D$row")->getValue();
                    $existencia_cocina = $sheet->getCell("E$row")->getValue();
                    $existencia_barra = $sheet->getCell("F$row")->getValue();
                    $existencia_servicio = $sheet->getCell("G$row")->getValue();
                    $existencia_otro = $sheet->getCell("H$row")->getValue();

                    // Buscar el insumo por nombre, unidad y categoría
                    $insumo = \common\models\IngredientStock::find()
                        ->where([
                            'ingredient_stock.business_id' => $businessId,
                            'ingredient_stock.ingredient' => $insumoNombre,
                            'ingredient_stock.um' => $unidad
                        ])
                        ->joinWith('category')
                        ->andWhere(['category.name' => $categoria])
                        ->one();
                    if (!$insumo) {
                        $errores[] = "No se encontró el insumo: $insumoNombre ($unidad, $categoria)";
                        $row++;
                        continue;
                    }

                    // Guardar inventario
                    $inv = new \common\models\Inventory();
                    $inv->ingredient_stock_id = $insumo->id;
                    $inv->business_id = $businessId;
                    $inv->fecha = $fecha;
                    $inv->date_end = $dateEnd;
                    $inv->inventario_almacen = is_numeric($existencia_almacen) ? $existencia_almacen : 0;
                    $inv->inventario_cocina = is_numeric($existencia_cocina) ? $existencia_cocina : 0;
                    $inv->inventario_barra = is_numeric($existencia_barra) ? $existencia_barra : 0;
                    $inv->inventario_servicio = is_numeric($existencia_servicio) ? $existencia_servicio : 0;
                    $inv->inventario_otro = is_numeric($existencia_otro) ? $existencia_otro : 0;
                    if ($inv->save()) {
                        $guardados++;
                    } else {
                        $errores[] = "Error al guardar $insumoNombre: " . json_encode($inv->getErrors());
                    }
                    $row++;
                }

                if ($guardados > 0) {
                    Yii::$app->session->setFlash('success', "Inventario importado correctamente. Insumos guardados: $guardados");
                }
                if (!empty($errores)) {
                    Yii::$app->session->setFlash('error', implode('<br>', $errores));
                }
                return $this->redirect(['index']);
            }
        }
        return $this->redirect(['index']);
    }

}
