<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use common\models\Business;
use common\models\MenuBundle;
use common\models\MenuBundleProduct;
use common\models\StandardRecipe;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use common\models\Menu;
use common\models\MenuSearch;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * MenuController implements the CRUD actions for Menu model.
 */
class MenuController extends Controller
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
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'view',
                            'remove-from-menu',
                            'remove-from-menu-in-bulk',
                            'save-menu',
                            'saved-menus',
                        ],
                        'allow' => true,
                        'roles' => [
                            'combo_list',
                            'combo_view',

                        ]
                    ],
                    [
                        'actions' => [
                            'create',
                        ],
                        'allow' => true,
                        'roles' => [
                            'combo_create',
                        ]
                    ],
                    [
                        'actions' => [
                            'update',
                            'save-sales'
                        ],
                        'allow' => true,
                        'roles' => [
                            'combo_update',
                        ]
                    ],
                    [
                        'actions' => [
                            'delete'
                        ],
                        'allow' => true,
                        'roles' => [
                            'combo_delete'
                        ]
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all Menu models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MenuSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Menu model.
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
     * Creates a new Menu model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $user = Yii::$app->user->identity;
        if ($user->hasRestrictions('combos')) {
            Yii::$app->session->setFlash('warning', "Haz alcanzado el límite de combos");
            return $this->redirect(['menu/index']);
        }
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $model = new Menu([
            'business_id' => $business['id']
        ]);
        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Menu model.
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
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionSaveSales($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save(false)) {
            return $this->asJson(['success' => true]);
        }

        return $this->asJson([
            'success' => false,
            'errors' => array_values(array_values($model->errors))
        ]);
    }

    /**
     * Deletes an existing Menu model.
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

    public function actionRemoveFromMenu(int $id, string $type, $bundle = null)
    {
        if ($bundle !== null) {
            $bundleModel = MenuBundle::findOne(['id' => $bundle]);
            if (!empty($bundleModel)) {
                Yii::$app->db->createCommand()
                    ->delete('menu_bundle_product', [
                        'entity_id' => $id,
                        'bundle_id' => $bundle,
                        'entity_type' => $type
                    ])
                    ->execute();

            }
        } else {
            $model = $type::findOne(['id' => $id]);
            $model->in_menu = 0;
            $model->save();
        }
        $url = Url::previous('menu-recipes');

        return $this->redirect($url ?? ['/standard-recipe/menu-recipes']);
    }

    public function actionRemoveFromMenuInBulk()
    {
        $data = Yii::$app->request->post('data');

        foreach ($data as $item) {
            $model = $item['type']::findOne(['id' => $item['id']]);
            $model->in_menu = 0;
            $model->save();
        }

        $url = Url::previous('menu-recipes');

        return $this->redirect($url ?? ['/standard-recipe/menu-recipes']);

    }

    public function actionSaveMenu()
    {
        $post = Yii::$app->request->post();
        $business = Business::findOne(['user_id' => Yii::$app->user->identity->id]);

        $bundle = new MenuBundle([
            'business_id' => $business->id,
            'date' => $post['date'],
        ]);

        $recipes = StandardRecipe::find()->where([
            'business_id' => $business['id'],
            'in_construction' => 0,
            'type' => StandardRecipe::STANDARD_RECIPE_TYPE_MAIN,
            'in_menu' => true
        ])->all();

        $combos = Menu::find()->where([
            'business_id' => $business['id'],
            'in_menu' => true
        ])->all();

        $models = array_merge($recipes, $combos);

        if ($bundle->save()) {
            foreach ($models as $model) {
                $link = new MenuBundleProduct([
                    'entity_id' => $model->id,
                    'entity_type' => get_class($model),
                    'bundle_id' => $bundle->id
                ]);

                $link->save();
            }
        } else if ($bundle->hasErrors()) {
            Yii::$app->session->setFlash('danger', json_encode($bundle->errors));
        }

        return $this->redirect(['standard-recipe/menu-recipes']);
    }

    public function actionSavedMenus()
    {
        $business = Business::findOne(['user_id' => Yii::$app->user->identity->id]);
        $bundles = MenuBundle::find()
            ->where(['business_id' => $business->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $bundles,
        ]);

        return $this->render('saved_menus', [
            'dataProvider' => $dataProvider
        ]);
    }


    /**
     * Finds the Menu model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Menu the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected
    function findModel($id)
    {
        if (($model = Menu::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
