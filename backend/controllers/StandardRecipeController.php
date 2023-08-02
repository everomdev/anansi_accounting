<?php

namespace backend\controllers;

use backend\helpers\RedisKeys;
use backend\models\StandardRecipeIngredientForm;
use common\models\RecipeStep;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use rico\yii2images\models\Image;
use Yii;
use common\models\StandardRecipe;
use common\models\StandardRecipeSearch;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * StandardRecipeController implements the CRUD actions for StandardRecipe model.
 */
class StandardRecipeController extends Controller
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
    public function actionIndex($type = StandardRecipe::STANDARD_RECIPE_TYPE_MAIN)
    {
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $searchModel = new StandardRecipeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->andWhere([
            'business_id' => $business['id'],
            'in_construction' => 0,
            'type' => $type
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
    public function actionCreate($type = StandardRecipe::STANDARD_RECIPE_TYPE_MAIN)
    {
        Url::remember(['standard-recipe/create', 'type' => $type], 'create-recipe');
        $business = RedisKeys::getValue(RedisKeys::BUSINESS_KEY);
        $model = StandardRecipe::findOne(['business_id' => $business['id'], 'in_construction' => true, 'type' => $type]);
        if (empty($model)) {
            $model = new StandardRecipe([
                'business_id' => $business['id'],
                'type' => $type,
                'title' => Yii::t('app', "A good recipe"),
                'in_construction' => true,
            ]);
        }

        if (Yii::$app->request->isGet) {
            $model->save();
        }

        $post = Yii::$app->request->post();
        if (!empty($post)) {
            $post['StandardRecipe']['in_construction'] = false;
        }

        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$model])->validate();
        }

        if ($model->load($post) && $model->save()) {
            if ($model->type == $model::STANDARD_RECIPE_TYPE_MAIN) {
                return $this->redirect(['standard-recipe/index', 'type' => $model->type]);
            } else {
                return $this->redirect(['sub-standard-recipe/index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionSelectIngredients($id)
    {
        $this->layout = 'blank';
        $model = $this->findModel($id);
        $form = new StandardRecipeIngredientForm();

        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$form])->validate();
        }
        if (Yii::$app->request->isPost && $form->load($post) && $form->validate()) {
            if (empty($form->subRecipeId)) {
                $model->addUpdateIngredient($form->ingredientId, $form->quantity);
            } else {
                $model->addUpdateSubRecipe($form->subRecipeId, $form->quantity);
            }
        }

        $previous = '';
        if ($model->in_construction) {
            $previous = 'create-recipe';
        } else {
            $previous = 'update-recipe';
        }
        return $this->redirect(Url::previous($previous));
    }

    public function actionUnselectIngredient($id, $ingredientId, $isRecipe = false)
    {
        $model = $this->findModel($id);

        if ($isRecipe) {
            $model->removeSubRecipe($ingredientId);
        } else {
            $model->removeIngredient($ingredientId);
        }

        return $this->redirect(['standard-recipe/update', 'id' => $id]);
    }

    public function actionFinishRecipeCreation($id)
    {
        $this->layout = 'blank';
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

    public function actionAddStep($id)
    {
        $model = $this->findModel($id);
        $step = new RecipeStep([
            'recipe_id' => $model->id,
        ]);

        $post = Yii::$app->request->post();
        if (array_key_exists('ajax', $post)) {
            $this->make(AjaxRequestModelValidator::class, [$step])->validate();
        }

        $step->load($post);
        $step->save();

        $previous = '';
        if ($model->in_construction) {
            $previous = 'create-recipe';
        } else {
            $previous = 'update-recipe';
        }
        return $this->redirect(Url::previous($previous));
    }

    public function actionRemoveStep($recipeId, $id)
    {
        $model = $this->findModel($recipeId);
        $step = RecipeStep::findOne(['id' => $id]);

        if (!empty($step)) {
            $step->delete();
        }

        $previous = '';
        if ($model->in_construction) {
            $previous = 'create-recipe';
        } else {
            $previous = 'update-recipe';
        }
        return $this->redirect(Url::previous($previous));
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
        Url::remember(['standard-recipe/update', 'id' => $id], 'update-recipe');
        $model = $this->findModel($id);

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

    public function actionDeleteImage($id)
    {
        $post = Yii::$app->request->post();
        if (isset($post['key'])) {
            $imageId = $post['key'];
            $model = $this->findModel($id);

            $images = $model->getImages();
            foreach ($images as $image) {
                if ($image->getPrimaryKey() == $imageId) {
                    $model->removeImage($image);
                    break;
                }
            }

            return $this->asJson([]);
        }

        return $this->asJson(['error' => true, 'data' => $post]);
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
