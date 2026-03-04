<?php

namespace backend\controllers;

use common\models\ConvoyIngredient;
use common\models\StandardRecipe;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use common\models\Convoy;
use common\models\ConvoySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ConvoyController implements the CRUD actions for Convoy model.
 */
class ConvoyController extends Controller
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'backupReminder' => [
                'class' => \backend\components\BackupReminderBehavior::class,
            ],
        ];
    }

    /**
     * Lists all Convoy models.
     * @return mixed
     */
    public function actionIndex()
    {
        $business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
        $searchModel = new ConvoySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['business_id' => $business['id']]);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Convoy model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Convoy model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $user = Yii::$app->user->identity;
        if ($user->hasRestrictions('convoy')) {
            Yii::$app->session->setFlash('warning', "Haz alcanzado el límite de convoys");
            return $this->redirect(['convoy/index']);
        }
        $business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
        $model = new Convoy(['business_id' => $business['id']]);
        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }

        if ($model->load($post) && $model->save()) {
            return $this->redirect(['update', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Convoy model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';

        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }

        if ($model->load($post) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionAddIngredient($id)
    {
        $convoy = $this->findModel($id);

        $model = new ConvoyIngredient([
            'convoy_id' => $convoy->id,
            'quantity' => 1
        ]);

        $post = Yii::$app->request->post();

        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }

        if ($model->load($post) && $model->save(false)) {
            return $this->asJson(true);
        }

        return $this->asJson(false);

    }

    public function actionRemoveIngredient($id, $ingredientId)
    {
        Yii::$app->db->createCommand()
            ->delete('convoy_ingredient', [
                'convoy_id' => $id,
                'id' => $ingredientId
            ])->execute();

        return $this->asJson(true);
    }

    public function actionUpdateIngredient($id, $ingredientId)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        try {
            $convoy = $this->findModel($id);
            if (!$convoy) {
                return ['success' => false, 'message' => 'Convoy not found'];
            }
            
            $convoyIngredient = ConvoyIngredient::findOne(['id' => $ingredientId, 'convoy_id' => $convoy->id]);
            if (!$convoyIngredient) {
                return ['success' => false, 'message' => 'Ingredient not found'];
            }

            $post = Yii::$app->request->post();
            if (empty($post)) {
                return ['success' => false, 'message' => 'No POST data received'];
            }

            $quantity = $post['quantity'] ?? null;
            $selectedEntity = $post['selectedEntity'] ?? null;

            if ($quantity === null || $selectedEntity === null) {
                return ['success' => false, 'message' => 'Missing quantity or selectedEntity', 'received' => $post];
            }

            $convoyIngredient->quantity = (float)$quantity;
            $convoyIngredient->selectedEntity = $selectedEntity;
            
            if ($convoyIngredient->save()) {
                return ['success' => true, 'message' => 'Ingredient updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Validation errors', 'errors' => $convoyIngredient->errors];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
        }
    }

    public function actionGetAvailableOptions()
    {
        $businessData = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
        
        $ingredients = \common\models\IngredientStock::find()
            ->andWhere(['ingredient_stock.business_id' => $businessData['id']])
            ->all();

        $recipes = \common\models\StandardRecipe::find()
            ->andWhere(['standard_recipe.business_id' => $businessData['id']])
            ->andWhere(['type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB])
            ->andWhere(['in_construction' => false])
            ->all();

        $ingredients = array_map(function ($ingredient) {
            $ingredient->id = "ingredient_" . $ingredient->id;
            return $ingredient;
        }, $ingredients);

        $recipes = array_map(function ($recipe) {
            $recipe->id = "recipe_" . $recipe->id;
            return $recipe;
        }, $recipes);

        $data = \yii\helpers\ArrayHelper::map(array_merge($ingredients, $recipes), 'id', function($i){
            return sprintf("%s (%s)", $i->name, ($i instanceof \common\models\IngredientStock) ? $i->portion_um : $i->yield_um);
        });

        return $this->asJson($data);
    }

    /**
     * Deletes an existing Convoy model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Convoy model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Convoy the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Convoy::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    /**
     * Exporta plantilla Excel para importar convoys
     * @return mixed
     */
    public function actionExportConvoyTemplate()
    {
        $business = \backend\helpers\RedisKeys::getBusiness();

        // Configuración para mejorar rendimiento
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // 1. Configurar hojas principales
        $convoysSheet = $spreadsheet->getActiveSheet();
        $convoysSheet->setTitle('CONVOYS');

        // 2. Configurar cabeceras de convoys
        $convoysHeaders = [
            'Nombre*',
            'Platillos*',
            'Observaciones'
        ];

        $col = 'A';
        foreach ($convoysHeaders as $header) {
            $convoysSheet->setCellValue($col.'1', $header);
            $convoysSheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        // 3. Crear otras hojas necesarias
        $ingredientsSheet = $spreadsheet->createSheet();
        $ingredientsSheet->setTitle('INGREDIENTES_CONVOY');

        $insumosSheet = $spreadsheet->createSheet();
        $insumosSheet->setTitle('INSUMOS');

        $recipesSheet = $spreadsheet->createSheet();
        $recipesSheet->setTitle('RECETAS');

        $umSheet = $spreadsheet->createSheet();
        $umSheet->setTitle('UMs');

        // 4. Configurar cabeceras de todas las hojas
        $headers = [
            'INGREDIENTES_CONVOY' => ['Convoy', 'Tipo*', 'Item*', 'Cantidad*', 'UM', 'Costo', 'Costo Total'],
            'INSUMOS' => ['Insumo', 'Cantidad', 'UM', 'Costo'],
            'RECETAS' => ['Nombre de la Receta', 'Porciones', 'Unidad de medida final', 'Costo'],
            'UMs' => ['Unidad de Medida']
        ];

        foreach ($headers as $sheetName => $sheetHeaders) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $col = 'A';
            foreach ($sheetHeaders as $header) {
                $sheet->setCellValue($col.'1', $header);
                $sheet->getStyle($col)->getAlignment()->setWrapText(true);
                $col++;
            }
            $sheet->freezePane('A2');
        }

        // 5. Cargar datos para las hojas de referencia
        $batchSize = 350;

        // Unidades de medida
        $allUMs = \common\models\UnitOfMeasurement::find()
            ->select('name')
            ->where(['business_id' => $business['id']])
            ->groupBy('name')
            ->limit($batchSize)
            ->all();

        // Insumos
        $ingredientStock = \common\models\IngredientStock::find()
            ->where(['business_id' => $business['id']])
            ->all();

        // Recetas principales
        $recipes = \common\models\StandardRecipe::find()
            ->where(['business_id' => $business['id']])
            ->andWhere(['type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB])
            ->andWhere(['in_construction' => false])
            ->limit($batchSize)
            ->all();

        // 6. Llenar hojas de referencia
        // Unidades de medida
        $rowUM = 2;
        foreach ($allUMs as $um) {
            $umSheet->setCellValue("A$rowUM", $um->name);
            $rowUM++;
        }

        // Insumos
        $insumosRow = 2;
        foreach ($ingredientStock as $ingredient) {
            $insumosSheet->setCellValue('A'.$insumosRow, strtolower($ingredient->ingredient));
            $insumosSheet->setCellValue('B'.$insumosRow, $ingredient->quantity);
            $insumosSheet->setCellValue('C'.$insumosRow, $ingredient->portion_um);
            $insumosSheet->setCellValue('D'.$insumosRow, number_format($ingredient->lastUnitPrice / $ingredient->portions_per_unit, 2, '.', ''));
            $insumosRow++;
        }

        // Recetas
        $recipesRow = 2;
        foreach ($recipes as $recipe) {
            $recipesSheet->setCellValue('A'.$recipesRow, strtoupper($recipe->title));
            $recipesSheet->setCellValue('B'.$recipesRow, $recipe->portions);
            $recipesSheet->setCellValue('C'.$recipesRow, $recipe->um);
            $recipesSheet->setCellValue('D'.$recipesRow, $recipe->custom_cost);
            $recipesRow++;
        }

        // 7. Configurar estilos
        $centerStyle = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];

        $convoysSheet->getStyle('A1:D300')->applyFromArray($centerStyle);
        $ingredientsSheet->getStyle('A1:G500')->applyFromArray($centerStyle);
        $insumosSheet->getStyle('A1:D350')->applyFromArray($centerStyle);
        $recipesSheet->getStyle('A1:D350')->applyFromArray($centerStyle);
        $umSheet->getStyle('A1:A100')->applyFromArray($centerStyle);

        $convoysSheet->getColumnDimension('A')->setAutoSize(true)->setWidth(30);
        $convoysSheet->getStyle('A2:A500')->getAlignment()->setWrapText(true);
        $ingredientsSheet->getColumnDimension('A')->setAutoSize(true)->setWidth(30);
        $ingredientsSheet->getStyle('A2:A500')->getAlignment()->setWrapText(true);

        $convoysSheet->freezePane('B2');
        $ingredientsSheet->freezePane('B2');
        $insumosSheet->freezePane('B2');


        // b) Validación para tipo de ingrediente en INGREDIENTES_CONVOY
        $ingredientTypeValidation = $ingredientsSheet->getCell('B2')->getDataValidation();
        $ingredientTypeValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $ingredientTypeValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $ingredientTypeValidation->setAllowBlank(false);
        $ingredientTypeValidation->setShowInputMessage(true);
        $ingredientTypeValidation->setShowErrorMessage(true);
        $ingredientTypeValidation->setShowDropDown(true);
        $ingredientTypeValidation->setErrorTitle('Error de entrada');
        $ingredientTypeValidation->setError('Seleccione INSUMO o SUBRECETA');
        $ingredientTypeValidation->setPromptTitle('Tipo de ingrediente');
        $ingredientTypeValidation->setPrompt('Seleccione si es un insumo o una subreceta');
        $ingredientTypeValidation->setFormula1('"INSUMO,SUBRECETA"');

        // c) Validación para unidades de medida
        $umValidation = $ingredientsSheet->getCell('E2')->getDataValidation();
        $umValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $umValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $umValidation->setAllowBlank(false);
        $umValidation->setShowInputMessage(true);
        $umValidation->setShowErrorMessage(true);
        $umValidation->setShowDropDown(true);
        $umValidation->setErrorTitle('Error de entrada');
        $umValidation->setError('Seleccione una unidad de medida válida');
        $umValidation->setPromptTitle('Unidad de medida');
        $umValidation->setPrompt('Seleccione la unidad de medida');

        if (!empty($allUMs)) {
            $allUMNames = array_column($allUMs, 'name');
            $umList = '"' . implode(',', $allUMNames) . '"';
            $umValidation->setFormula1($umList);
        } else {
            $umValidation->setFormula1('""');
        }

        // d) Validación para valores numéricos en platillos
        $platesValidation = $convoysSheet->getCell('B2')->getDataValidation();
        $platesValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DECIMAL);
        $platesValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $platesValidation->setAllowBlank(false);
        $platesValidation->setShowInputMessage(true);
        $platesValidation->setShowErrorMessage(true);
        $platesValidation->setErrorTitle('Error de entrada');
        $platesValidation->setError('Este campo solo acepta valores numéricos');
        $platesValidation->setPromptTitle('Número de platillos');
        $platesValidation->setPrompt('Ingrese el número de platillos');
        $platesValidation->setFormula1(1);
        $platesValidation->setFormula2(999999);

        // e) Validación para cantidad en ingredientes
        $quantityValidation = $ingredientsSheet->getCell('D2')->getDataValidation();
        $quantityValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DECIMAL);
        $quantityValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $quantityValidation->setAllowBlank(false);
        $quantityValidation->setShowInputMessage(true);
        $quantityValidation->setShowErrorMessage(true);
        $quantityValidation->setErrorTitle('Error de entrada');
        $quantityValidation->setError('Este campo solo acepta valores numéricos');
        $quantityValidation->setPromptTitle('Cantidad');
        $quantityValidation->setPrompt('Ingrese la cantidad');
        $quantityValidation->setFormula1(0.01);
        $quantityValidation->setFormula2(999999);

        // 9. Aplicar validaciones a las filas
        for ($i = 2; $i <= 500; $i++) {
            $convoysSheet->getCell("B$i")->setDataValidation(clone $platesValidation);
            $ingredientsSheet->getCell("B$i")->setDataValidation(clone $ingredientTypeValidation);
            $ingredientsSheet->getCell("E$i")->setDataValidation(clone $umValidation);
            $ingredientsSheet->getCell("D$i")->setDataValidation(clone $quantityValidation);
        }

        // 10. Configurar validación dinámica para nombres de convoys en INGREDIENTES_CONVOY
        $convoyValidation = $ingredientsSheet->getCell('A2')->getDataValidation();
        $convoyValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $convoyValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $convoyValidation->setAllowBlank(false);
        $convoyValidation->setShowInputMessage(true);
        $convoyValidation->setShowErrorMessage(true);
        $convoyValidation->setShowDropDown(true);
        $convoyValidation->setErrorTitle('Error de entrada');
        $convoyValidation->setError('Debe seleccionar un convoy existente');
        $convoyValidation->setPromptTitle('Seleccionar convoy');
        $convoyValidation->setPrompt('Seleccione un convoy de la lista');

        $dynamicConvoyFormula = "=OFFSET(CONVOYS!A\$2,0,0,COUNTA(CONVOYS!A:A)-1,1)";
        $convoyValidation->setFormula1($dynamicConvoyFormula);

        for ($row = 2; $row <= 500; $row++) {
            $ingredientsSheet->getCell('A'.$row)->setDataValidation(clone $convoyValidation);
        }

        // 11. Configurar validación dinámica para items basada en el tipo seleccionado
        for ($i = 2; $i <= 500; $i++) {
            // Fórmula para mostrar opciones según el tipo
            $ingredientsSheet->setCellValue(
                "C$i",
                "=IF(B$i=\"INSUMO\",INDIRECT(\"INSUMOS!A2:A\"&COUNTA(INSUMOS!A:A)),IF(B$i=\"RECETA\",INDIRECT(\"RECETAS!A2:A\"&COUNTA(RECETAS!A:A)),\"\"))"
            );

            $itemValidation = $ingredientsSheet->getCell("C$i")->getDataValidation();
            $itemValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $itemValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $itemValidation->setAllowBlank(false);
            $itemValidation->setShowInputMessage(true);
            $itemValidation->setShowErrorMessage(true);
            $itemValidation->setShowDropDown(true);
            $itemValidation->setErrorTitle('Error de entrada');
            $itemValidation->setError('Seleccione un item válido');
            $itemValidation->setPromptTitle('Seleccionar item');
            $itemValidation->setPrompt('Seleccione un insumo o receta según el tipo');

            $itemValidation->setFormula1("=IF(B$i=\"INSUMO\",INDIRECT(\"INSUMOS!A2:A\"&COUNTA(INSUMOS!A:A)),IF(B$i=\"RECETA\",INDIRECT(\"RECETAS!A2:A\"&COUNTA(RECETAS!A:A)),\"\"))");
            $ingredientsSheet->getCell("C$i")->setDataValidation($itemValidation);

            // Fórmula para UM automática
            $ingredientsSheet->setCellValue(
                "E$i",
                "=IF(B$i=\"INSUMO\",VLOOKUP(C$i,INSUMOS!A:D,3,FALSE),IF(B$i=\"RECETA\",VLOOKUP(C$i,RECETAS!A:D,3,FALSE),\"\"))"
            );

            // Fórmula para Costo automático
            $ingredientsSheet->setCellValue(
                "F$i",
                "=IF(B$i=\"INSUMO\",VLOOKUP(C$i,INSUMOS!A:D,4,FALSE),IF(B$i=\"RECETA\",VLOOKUP(C$i,RECETAS!A:D,4,FALSE),\"\"))"
            );

            // Fórmula para Costo Total automático
            $ingredientsSheet->setCellValue(
                "G$i",
                "=IF(F$i=\"\",\"\",TEXT(F$i*D$i,\"$#,##0.00\"))"
            );
        }

        // 12. Nota informativa
        $ingredientsSheet->setCellValue('H1', 'NOTA: El nombre del convoy debe existir primero en la hoja "CONVOYS"');
        $ingredientsSheet->mergeCells('H1:L1');
        $ingredientsSheet->getStyle('H1')->getFont()
            ->setItalic(true)
            ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_DARKRED));

        // 13. Ajustar anchos de columna
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $worksheet->calculateColumnWidths();
        }

        // Configurar formato de moneda para la columna de costo
        $ingredientsSheet->getStyle('F2:F500')->getNumberFormat()->setFormatCode('$#,##0.00');
        $ingredientsSheet->getStyle('G2:G500')->getNumberFormat()->setFormatCode('$#,##0.00');

        // Ajustar anchos específicos para INGREDIENTES_CONVOY
        $ingredientsSheet->getColumnDimension('A')->setAutoSize(true)->setWidth(30);
        $ingredientsSheet->getStyle('A2:A500')->getAlignment()->setWrapText(true);
        $ingredientsSheet->getColumnDimension('C')->setWidth(25);
        $ingredientsSheet->getColumnDimension('D')->setWidth(12);
        $ingredientsSheet->getColumnDimension('G')->setWidth(15);

        // Ocultar columnas de cálculo (UM y Costo unitario) para mostrar solo el costo total
        $ingredientsSheet->getColumnDimension('E')->setVisible(false);
        $ingredientsSheet->getColumnDimension('F')->setVisible(false);

        // 14. Configurar primera hoja como activa
        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getActiveSheet()->setSelectedCell('A2');
        $ingredientsSheet->setSelectedCell('A2');

        // 15. Generar y descargar el archivo
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(true);

        $filename = 'Plantilla para importar Convoys.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $filename);
        $writer->save($tempFile);

        return Yii::$app->response->sendFile($tempFile, $filename);
    }

    /**
     * Importa convoys desde un archivo Excel
     * @return mixed
     */
    public function actionImportConvoyExcel()
    {
        if (Yii::$app->request->isPost) {
            $uploadedFile = \yii\web\UploadedFile::getInstanceByName('convoy_file');

            if ($uploadedFile) {
                try {
                    $business = \backend\helpers\RedisKeys::getBusiness();

                    // Cargar el archivo Excel
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                    $spreadsheet = $reader->load($uploadedFile->tempName);

                    // Procesar hoja de CONVOYS
                    $convoysSheet = $spreadsheet->getSheetByName('CONVOYS');
                    if (!$convoysSheet) {
                        Yii::$app->session->setFlash('error', 'No se encontró la hoja "CONVOYS" en el archivo.');
                        return $this->redirect(['index']);
                    }

                    $convoysData = [];
                    $highestRow = $convoysSheet->getHighestRow();

                    for ($row = 2; $row <= $highestRow; $row++) {
                        $name = trim($convoysSheet->getCell('A'.$row)->getValue());
                        $plates = trim($convoysSheet->getCell('B'.$row)->getValue());
                        $observations = trim($convoysSheet->getCell('C'.$row)->getValue());

                        if (!empty($name) && !empty($plates)) {
                            $convoysData[$name] = [
                                'name' => $name,
                                'type' => 'family',
                                'plates' => $plates,
                                'observations' => $observations,
                                'ingredients' => []
                            ];
                        }
                    }

                    // Procesar hoja de INGREDIENTES_CONVOY
                    $ingredientsSheet = $spreadsheet->getSheetByName('INGREDIENTES_CONVOY');
                    if ($ingredientsSheet) {
                        $highestRowIngredients = $ingredientsSheet->getHighestRow();

                        for ($row = 2; $row <= $highestRowIngredients; $row++) {
                            $convoyName = trim($ingredientsSheet->getCell('A'.$row)->getValue());
                            $type = trim($ingredientsSheet->getCell('B'.$row)->getValue());
                            $item = trim($ingredientsSheet->getCell('C'.$row)->getValue());
                            $quantity = trim($ingredientsSheet->getCell('D'.$row)->getValue());
                            $um = trim($ingredientsSheet->getCell('E'.$row)->getValue());

                            if (!empty($convoyName) && !empty($type) && !empty($item) && !empty($quantity) && isset($convoysData[$convoyName])) {
                                $convoysData[$convoyName]['ingredients'][] = [
                                    'type' => $type,
                                    'item' => $item,
                                    'quantity' => $quantity,
                                    'um' => $um
                                ];
                            }
                        }
                    }

                    // Guardar los convoys en la base de datos
                    $importedCount = 0;
                    $errors = [];

                    foreach ($convoysData as $convoyData) {
                        $transaction = Yii::$app->db->beginTransaction();

                        try {
                            // Verificar si el convoy ya existe
                            $existingConvoy = Convoy::find()
                                ->where(['business_id' => $business['id'], 'name' => $convoyData['name']])
                                ->one();

                            if ($existingConvoy) {
                                $errors[] = "El convoy '{$convoyData['name']}' ya existe.";
                                $transaction->rollBack();
                                continue;
                            }

                            // Crear el convoy
                            $convoy = new Convoy([
                                'business_id' => $business['id'],
                                'name' => $convoyData['name'],
                                'type' => $convoyData['type'],
                                'plates' => $convoyData['plates'],
                                'observations' => $convoyData['observations']
                            ]);

                            if (!$convoy->save()) {
                                $errors[] = "Error al guardar el convoy '{$convoyData['name']}': " . implode(', ', $convoy->getErrorSummary(true));
                                $transaction->rollBack();
                                continue;
                            }

                            // Crear los ingredientes del convoy
                            foreach ($convoyData['ingredients'] as $ingredientData) {
                                $convoyIngredient = new ConvoyIngredient([
                                    'convoy_id' => $convoy->id,
                                    'quantity' => $ingredientData['quantity']
                                ]);

                                // Determinar el tipo de entidad y buscar el ID correspondiente
                                if ($ingredientData['type'] === 'INSUMO') {
                                    // Buscar por nombre exacto primero, luego por like
                                    $entity = \common\models\IngredientStock::find()
                                        ->where(['business_id' => $business['id']])
                                        ->andWhere(['ingredient' => $ingredientData['item']])
                                        ->one();

                                    if (!$entity) {
                                        $entity = \common\models\IngredientStock::find()
                                            ->where(['business_id' => $business['id']])
                                            ->andWhere(['like', 'ingredient', $ingredientData['item']])
                                            ->one();
                                    }

                                    if ($entity) {
                                        $convoyIngredient->selectedEntity = 'ingredient_' . $entity->id;
                                    } else {
                                        $errors[] = "Insumo '{$ingredientData['item']}' no encontrado para el convoy '{$convoyData['name']}'.";
                                        $transaction->rollBack();
                                        continue 2;
                                    }
                                } elseif ($ingredientData['type'] === 'RECETA') {
                                    // Buscar por título exacto primero, luego por like
                                    $entity = \common\models\StandardRecipe::find()
                                        ->where(['business_id' => $business['id']])
                                        ->andWhere(['title' => $ingredientData['item']])
                                        ->one();

                                    if (!$entity) {
                                        $entity = \common\models\StandardRecipe::find()
                                            ->where(['business_id' => $business['id']])
                                            ->andWhere(['like', 'title', $ingredientData['item']])
                                            ->one();
                                    }

                                    if ($entity) {
                                        $convoyIngredient->selectedEntity = 'recipe_' . $entity->id;
                                    } else {
                                        $errors[] = "Receta '{$ingredientData['item']}' no encontrada para el convoy '{$convoyData['name']}'.";
                                        $transaction->rollBack();
                                        continue 2;
                                    }
                                }

                                if (!$convoyIngredient->save(false)) {
                                    $errors[] = "Error al guardar ingrediente para el convoy '{$convoyData['name']}': " . implode(', ', $convoyIngredient->getErrorSummary(true));
                                    $transaction->rollBack();
                                    continue 2;
                                }
                            }

                            $transaction->commit();
                            $importedCount++;

                        } catch (\Exception $e) {
                            $transaction->rollBack();
                            $errors[] = "Error procesando convoy '{$convoyData['name']}': " . $e->getMessage();
                        }
                    }

                    if ($importedCount > 0) {
                        Yii::$app->session->setFlash('success', "Se importaron {$importedCount} convoy(s) exitosamente.");
                    }

                    if (!empty($errors)) {
                        Yii::$app->session->setFlash('warning', 'Errores encontrados: ' . implode('<br>', $errors));
                    }

                } catch (\Exception $e) {
                    Yii::$app->session->setFlash('error', 'Error al procesar el archivo: ' . $e->getMessage());
                }
            } else {
                Yii::$app->session->setFlash('error', 'No se seleccionó ningún archivo.');
            }
        }

        return $this->redirect(['index']);
    }
}
