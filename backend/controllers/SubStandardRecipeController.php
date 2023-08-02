<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use backend\models\StandardRecipeIngredientForm;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use common\models\StandardRecipe;
use common\models\StandardRecipeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * StandardRecipeController implements the CRUD actions for StandardRecipe model.
 */
class SubStandardRecipeController extends Controller
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
        ];
    }

    /**
     * Lists all StandardRecipe models.
     * @return mixed
     */
    public function actionIndex()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $searchModel = new StandardRecipeSearch();

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere([
            'business_id' => $business['id'],
            'in_construction' => 0,
            'type' => StandardRecipe::STANDARD_RECIPE_TYPE_SUB
        ]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single StandardRecipe model.
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
     * Creates a new StandardRecipe model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $model = new StandardRecipe([
            'business_id' => $business['id'],
            'type' => StandardRecipe::STANDARD_RECIPE_TYPE_SUB
        ]);

        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }

        if ($model->load($post) && $model->save()) {
            return $this->redirect(['sub-standard-recipe/select-ingredients', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionSelectIngredients($id)
    {
        $model = $this->findModel($id);
        $form = new StandardRecipeIngredientForm();
        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$form])->validate();
        }
        if (Yii::$app->request->isPost && $form->load($post) && $form->validate()) {
            if (!empty($form->ingredientId)) {
                $model->addUpdateIngredient($form->ingredientId, $form->quantity);
            } else {
                $model->addUpdateSubRecipe($form->subRecipeId, $form->quantity);
            }
        }


        return $this->render('create/_ingredients_selection', [
            'model' => $model
        ]);
    }

    public function actionUnselectIngredient($id, $ingredientId)
    {
        $model = $this->findModel($id);

        $model->removeIngredient($ingredientId);

        return $this->redirect(['sub-standard-recipe/select-ingredients', 'id' => $id]);
    }

    public function actionFinishRecipeCreation($id)
    {
        $model = $this->findModel($id);

        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }

        if ($model->load($post) && $model->save(true, ['flowchart', 'equipment', 'steps', 'allergies'])) {
            return $this->redirect(['index']);
        }

        return $this->render('create/_finish_creation', [
            'model' => $model
        ]);
    }

    /**
     * Updates an existing StandardRecipe model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['sub-standard-recipe/index']);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing StandardRecipe model.
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
     * Finds the StandardRecipe model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return StandardRecipe the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = StandardRecipe::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
