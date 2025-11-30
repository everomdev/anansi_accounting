<?php

namespace backend\controllers;

use backend\helpers\ExcelHelper;
use backend\helpers\RedisKeys;
use common\models\Business;
use common\models\Category;
use common\models\StockPrice;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use http\Url;
use Yii;
use common\models\IngredientStock;
use common\models\IngredientStockSearch;
use common\models\Convoy;
use common\models\ConvoyIngredient;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * IngredientStockController implements the CRUD actions for IngredientStock model.
 */
class IngredientStockController extends Controller
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
                    'import-ingredients' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => [
                            'create',
                            'generate-key'
                        ],
                        'allow' => true,
                        'roles' => ['ingredients_create'],
                    ],
                    [
                        'actions' => [
                            'update',
                            'generate-key'
                        ],
                        'allow' => true,
                        'roles' => ['ingredients_update'],
                    ],
                    [
                        'actions' => [
                            'create',
                        ],
                        'allow' => true,
                        'roles' => ['ingredients_delete'],
                    ],
                    [
                        'actions' => [
                            'index',
                            'download-references',
                            'download-template',
                            'export',
                            'import-ingredients',
                            'duplicate-insumos',
                            'view',
                            'check-usage',
                            'stock-alerts'
                        ],
                        'allow' => true,
                        'roles' => ['ingredients_list','ingredients_view'],
                    ],
                    [
                        'actions' => [
                            'delete',
                            'bulk-remove'
                        ],
                        'allow' => true,
                        'roles' => ['ingredients_delete'],
                    ],
                    [
                        'actions' => [
                            'price-trend',
                        ],
                        'allow' => true,
                        'roles' => ['price_trend_view'],
                    ],
                    [
                        'actions' => [
                            'storage',
                        ],
                        'allow' => true,
                        'roles' => ['storage', 'storage_list', 'storage_view'],
                    ],

                ],
            ],
            'backupReminder' => [
                'class' => \backend\components\BackupReminderBehavior::class,
            ],
        ];
    }

    /**
     * Lists all IngredientStock models.
     * @return mixed
     */
    public function actionIndex()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $searchModel = new IngredientStockSearch();
        // Obtener el valor de la cookie si existe
        $savedPageSize = (int)Yii::$app->request->cookies->getValue('ingredient_stock_page_size', 10);
        
        // Personalizar elementos por página - solo si viene en la URL
        $perPage = Yii::$app->request->get('per-page');
        
        // Si perPage no viene en la URL o no es válido, usar el valor guardado en la cookie
        if (!$perPage || !in_array((int)$perPage, [10, 25, 50, 100])) {
            $perPage = $savedPageSize;
        } else {
            // Solo guardar una nueva cookie si el valor es diferente al que ya tenemos
            if ((int)$perPage !== $savedPageSize) {
                $cookie = new \yii\web\Cookie([
                    'name' => 'ingredient_stock_page_size',
                    'value' => (int)$perPage,
                    'expire' => time() + 86400 * 30,
                ]);
                Yii::$app->response->cookies->add($cookie);
            }
        }
        
        // Usar perPage como la cantidad de elementos por página
        $pageSize = (int)$perPage;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = $pageSize;
        $dataProvider->query->andWhere([
            'business_id' => $business['id']
        ]);
        $count = [];
        $ingredients = $dataProvider->getModels();
        foreach ($ingredients as $ingredient) {
            $count[$ingredient->id] = [
                'recipes' => $ingredient->getRecipes()->count(),
                'subRecipes' => $ingredient->getSubRecipes()->count(),
            ];
        }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'count' => $count
        ]);
    }

    public function actionStorage()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $searchModel = new IngredientStockSearch();
        
        // Obtener el valor de la cookie si existe
        $savedPageSize = (int)Yii::$app->request->cookies->getValue('ingredient_stock_storage_page_size', 10);
        
        // Personalizar elementos por página - solo si viene en la URL
        $perPage = Yii::$app->request->get('per-page');
        
        // Si perPage no viene en la URL o no es válido, usar el valor guardado en la cookie
        if (!$perPage || !in_array((int)$perPage, [10, 25, 50, 100])) {
            $perPage = $savedPageSize;
        } else {
            // Solo guardar una nueva cookie si el valor es diferente al que ya tenemos
            if ((int)$perPage !== $savedPageSize) {
                $cookie = new \yii\web\Cookie([
                    'name' => 'ingredient_stock_storage_page_size',
                    'value' => (int)$perPage,
                    'expire' => time() + 86400 * 30,
                ]);
                Yii::$app->response->cookies->add($cookie);
            }
        }
        
        // Usar perPage como la cantidad de elementos por página
        $pageSize = (int)$perPage;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = $pageSize;

        $dataProvider->query->andWhere([
            'business_id' => $business['id']
        ]);

        return $this->render('storage', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionGenerateKey($categoryId)
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);

        return $this->asJson(IngredientStock::keyGenerator($categoryId, $business['id']));
    }

    /**
     * Displays a single IngredientStock model.
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
     * Creates a new IngredientStock model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $model = new IngredientStock([
            'business_id' => $business['id']
        ]);
        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', "Ingredient has been added to storage"));
            
            // Verificar si hay un returnUrl específico (desde formulario de movimientos)
            $returnUrl = Yii::$app->request->get('returnUrl');
            
            if (!empty($returnUrl) && strpos($returnUrl, 'movement/create') !== false) {
                // Parsear la URL para asegurar que tenga el parámetro type
                $urlParts = parse_url($returnUrl);
                
                if (isset($urlParts['query'])) {
                    parse_str($urlParts['query'], $queryParams);
                    // Si no tiene parámetro type, agregarlo
                    if (!isset($queryParams['type']) || empty($queryParams['type'])) {
                        $queryParams['type'] = 'input';
                        $urlParts['query'] = http_build_query($queryParams);
                        $returnUrl = $urlParts['path'] . '?' . $urlParts['query'];
                    }
                } else {
                    // Si no tiene query string, agregar el parámetro type
                    $returnUrl .= '?type=input';
                }
                
                // Redirigir a la URL de movimiento
                return $this->redirect($returnUrl);
            }
            
            // Si no hay returnUrl específico pero viene de un movimiento (verificar referer)
            $referer = Yii::$app->request->referrer;
            if (!empty($referer) && strpos($referer, 'movement/create') !== false) {
                // Solo redirigir si el referer contiene un tipo válido
                if (strpos($referer, 'type=') !== false) {
                    // Verificar que tenga el parámetro type, agregarlo si no lo tiene
                    $urlParts = parse_url($referer);
                    
                    if (isset($urlParts['query'])) {
                        parse_str($urlParts['query'], $queryParams);
                        if (!isset($queryParams['type']) || empty($queryParams['type'])) {
                            $queryParams['type'] = 'input';
                            $urlParts['query'] = http_build_query($queryParams);
                            $referer = $urlParts['path'] . '?' . $urlParts['query'];
                        }
                    } else {
                        $referer .= '?type=input';
                    }
                    
                    return $this->redirect($referer);
                }
            }
            
            // En cualquier otro caso, ir al index
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing IngredientStock model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } elseif ($model->hasErrors()) {
            var_dump($model->errors);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing IngredientStock model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        // Remove any convoy ingredients that reference this ingredient within the same business
        try {
            $convoyIds = Convoy::find()->select('id')->where(['business_id' => $model->business_id])->column();
            if (!empty($convoyIds)) {
                ConvoyIngredient::deleteAll([
                    'convoy_id' => $convoyIds,
                    'entity_class' => IngredientStock::class,
                    'entity_id' => $model->id,
                ]);
            }
        } catch (\Throwable $e) {
            Yii::warning('Failed to clean convoy ingredients for ingredient delete: ' . $e->getMessage(), __METHOD__);
        }

        $model->delete();

        return $this->redirect(['index']);
    }

    public function actionPriceTrend($ingredientId = null, $categoryId = null, $from = null, $to = null)
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);

        if (empty($from) && empty($to)) {
            $to = (new \DateTime())->format("Y-m-d");
            $from = (new \DateTime())->modify("-3 months")->format('Y-m-d');
        }
        $category = null;
        $model = IngredientStock::find()->where(['business_id' => $business['id']])->orderBy("RAND()")->one();
        if (empty($ingredientId) && empty($categoryId) && !empty($model)) {
            $ingredientId = $model->id;
        } elseif (empty($categoryId)) {
            $model = IngredientStock::findOne(['id' => $ingredientId]);
        } else {
            $category = Category::find()
                ->where([
                    'or',
                    ['business_id' => $business['id']],
                    ['business_id' => null],
                ])->andWhere(['id' => $categoryId])
                ->one();

        }
        $prices = [];
        if (!empty($model)) {

            $prices = $model->getStockPrices()
                ->andWhere([
                    'and',
                    ['>=', 'date', $from],
                    ['<=', 'date', $to],
                ])
                ->orderBy(['date' => SORT_ASC])
                ->all();
        } elseif (!empty($category)) {
            $prices = StockPrice::find()
                ->innerJoin('ingredient_stock is', 'is.id=stock_price.stock_id')
                ->where(['is.category_id' => $category->id])
                ->andWhere([
                    'and',
                    ['>=', 'date', $from],
                    ['<=', 'date', $to],
                ])
                ->orderBy(['date' => SORT_ASC])
                ->all();
        }

        return $this->render('price_trend', [
            'prices' => $prices,
            'from' => $from,
            'to' => $to,
            'categoryId' => $categoryId,
            'ingredientId' => $ingredientId
        ]);
    }

    public function actionDownloadReferences($id)
    {
        $business = Business::findOne(['id' => $id]);

        ExcelHelper::generateReferenceTemplate($business);
    }

    public function actionDownloadTemplate($id)
    {
        ExcelHelper::generateIngredientsTemplate($id);
    }

    public function actionImportIngredients($id)
    {
        $business = Business::findOne(['id' => $id]);

        $file = UploadedFile::getInstanceByName('ingredient-file');

        if ($file) {
            try {
                ExcelHelper::importIngredients($business, $file->tempName);
            }catch (\Exception $e) {
                $errors = json_decode($e->getMessage(), true);
                foreach ($errors as $field => $fieldErrors) {
                    Yii::$app->session->setFlash('error', implode("\n", $fieldErrors));
                }
            }
        }

        return $this->redirect(['ingredient-stock/index']);
    }

    public function actionExport()
    {
        $businessData = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $business = Business::findOne(['id' => $businessData['id']]);

        ExcelHelper::exportIngredients($business);
    }

    public function actionBulkRemove()
    {
        $businessData = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $business = Business::findOne(['id' => $businessData['id']]);
        $ids = Yii::$app->request->post('keys');
        if (!empty($ids) && $ids != 'all') {
            // Remove convoy references for these ingredients within this business
            try {
                $convoyIds = Convoy::find()->select('id')->where(['business_id' => $business->id])->column();
                if (!empty($convoyIds)) {
                    ConvoyIngredient::deleteAll([
                        'convoy_id' => $convoyIds,
                        'entity_class' => IngredientStock::class,
                        'entity_id' => $ids,
                    ]);
                }
            } catch (\Throwable $e) {
                Yii::warning('Failed to clean convoy ingredients during bulk remove: ' . $e->getMessage(), __METHOD__);
            }

            IngredientStock::deleteAll(['id' => $ids, 'business_id' => $business->id]);
        } elseif ($ids == 'all') {
            // Delete convoy references for all ingredients in this business
            try {
                $ingredientIds = IngredientStock::find()->select('id')->where(['business_id' => $business->id])->column();
                $convoyIds = Convoy::find()->select('id')->where(['business_id' => $business->id])->column();
                if (!empty($ingredientIds) && !empty($convoyIds)) {
                    ConvoyIngredient::deleteAll([
                        'convoy_id' => $convoyIds,
                        'entity_class' => IngredientStock::class,
                        'entity_id' => $ingredientIds,
                    ]);
                }
            } catch (\Throwable $e) {
                Yii::warning('Failed to clean convoy ingredients during bulk remove (all): ' . $e->getMessage(), __METHOD__);
            }

            IngredientStock::deleteAll(['business_id' => $business->id]);
        }


        return $this->asJson(['success' => true]);
    }

    /**
     * Finds the IngredientStock model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return IngredientStock the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = IngredientStock::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
    public function actionDuplicateInsumos()
    {
        $post = Yii::$app->request->post();
        $insumos = IngredientStock::find()->where(['id' => $post['insumos']])->all();
        foreach ($insumos as $insumo) {
            $insumo->duplicate();
        }

        return $this->redirect(['index']);
    }
    
    /**
     * Verificar si un ingrediente está siendo usado en recetas
     * Usado para advertir sobre cambios de unidad de compra
     */
    public function actionCheckUsage()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        if (!\Yii::$app->request->isPost) {
            return ['error' => 'Método no permitido'];
        }
        
        $data = json_decode(\Yii::$app->request->getRawBody(), true);
        $ingredientId = $data['ingredientId'] ?? null;
        
        if (!$ingredientId) {
            return ['isUsed' => false];
        }
        
        // Buscar el ingrediente
        $ingredient = IngredientStock::findOne($ingredientId);
        if (!$ingredient) {
            return ['isUsed' => false];
        }
        
        // Usar la misma lógica que en el índice
        $recipesCount = $ingredient->getRecipes()->count();
        $subRecipesCount = $ingredient->getSubRecipes()->count();
        
        return [
            'isUsed' => ($recipesCount > 0 || $subRecipesCount > 0),
            'usedInRecipes' => $recipesCount > 0,
            'usedInSubRecipes' => $subRecipesCount > 0,
            'recipesCount' => $recipesCount,
            'subRecipesCount' => $subRecipesCount
        ];
    }

    /**
     * Obtener resumen de ingredientes con alertas de stock
     */
    public function actionStockAlerts()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        
        // Ingredientes con stock bajo
        $lowStockQuery = IngredientStock::find()
            ->where(['business_id' => $business['id']])
            ->andWhere(['not', ['min_stock' => null]])
            ->andWhere('quantity <= min_stock');
        
        // Ingredientes con stock alto
        $highStockQuery = IngredientStock::find()
            ->where(['business_id' => $business['id']])
            ->andWhere(['not', ['max_stock' => null]])
            ->andWhere('quantity >= max_stock');
        
        $lowStockIngredients = $lowStockQuery->all();
        $highStockIngredients = $highStockQuery->all();
        
        return [
            'lowStock' => [
                'count' => count($lowStockIngredients),
                'ingredients' => array_map(function($ingredient) {
                    return [
                        'id' => $ingredient->id,
                        'name' => $ingredient->ingredient,
                        'key' => $ingredient->key,
                        'quantity' => $ingredient->quantity,
                        'min_stock' => $ingredient->min_stock,
                        'um' => $ingredient->um,
                        'percentage' => $ingredient->getStockPercentage()
                    ];
                }, $lowStockIngredients)
            ],
            'highStock' => [
                'count' => count($highStockIngredients),
                'ingredients' => array_map(function($ingredient) {
                    return [
                        'id' => $ingredient->id,
                        'name' => $ingredient->ingredient,
                        'key' => $ingredient->key,
                        'quantity' => $ingredient->quantity,
                        'max_stock' => $ingredient->max_stock,
                        'um' => $ingredient->um,
                        'percentage' => $ingredient->getStockPercentage()
                    ];
                }, $highStockIngredients)
            ]
        ];
    }
}
