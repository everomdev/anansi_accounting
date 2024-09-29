<?php

use kartik\editors\Summernote;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\StandardRecipe */
/* @var $form yii\widgets\ActiveForm */

\yii\web\YiiAsset::register($this);
$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);

$this->registerJsVar('currency', $business['currency_code']);
$this->registerJsVar('locale', str_replace('_', '-', $business['locale']));

$ingredients = (new \yii\db\Query())
    ->select("*")
    ->from('ingredient')
    ->all();
$autocompleteUm = array_values(array_unique(\yii\helpers\ArrayHelper::getColumn($ingredients, 'um')));
$this->registerJsVar('formUrl', \yii\helpers\Url::to(['standard-recipe/form-select-ingredient', 'id' => $model->id]));
$this->registerJsFile(Yii::getAlias("@web/js/standard-recipe/form.js"), [
    'position' => $this::POS_END,
    'depends' => [\yii\web\YiiAsset::class]
]);
$this->registerCssFile(Yii::getAlias("@web/css/flowchart.css"));

$recipesCategories = \common\models\RecipeCategory::find()->where(['business_id' => $business['id'], 'type' => $model->type])->all();

$businessObj = \common\models\Business::findOne(['id' => $business['id']]);

$recipesCategoriesMap = \yii\helpers\ArrayHelper::map($recipesCategories, 'name', 'name');

$recipesCategoriesMap['add'] = Yii::t('app', "+ Agregar");

$this->registerJsVar('createNewCategoryUrl', \yii\helpers\Url::to(['recipe-category/index']));

$currencySymbol = \Symfony\Component\Intl\Currencies::getSymbol(strtoupper($businessObj->currency_code));
$currencySymbol = preg_replace('/[a-zA-Z]/', '', $currencySymbol);

?>

<div class="standard-recipe-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-recipe',
        'enableAjaxValidation' => true,
        'options' => [
            'enctype' => 'multipart/form-data'
        ],
    ]); ?>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <?php if ($model->type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB): ?>
                        <?= $form->field($model, 'custom_cost')->hiddenInput()->label(false) ?>
                    <?php endif; ?>
                    <?php if (!$model->isNewRecord): ?>
                        <?= $form->field($model, 'title', [
                            'template' => "<div class='row mb-3'>{label}<div class='col-sm-8'>{input}</div></div>"
                        ])->textInput()->label(null, ['class' => 'col-sm-4 text-start']) ?>
                    <?php endif; ?>
                    <?= $form->field($model, 'type_of_recipe', [
                        'template' => "<div class='row mb-3'>{label}<div class='col-sm-8'>{input}</div></div>"
                    ])->dropDownList($recipesCategoriesMap)->label(null, ['class' => 'col-sm-4 text-start']) ?>
                    <?= $form->field($model, 'time_of_preparation', [
                        'template' => "<div class='row mb-3'>{label}<div class='col-sm-8'>{input}</div></div>"
                    ])->textInput()->label(null, ['class' => 'col-sm-4 text-start']) ?>
                    <?php $inputUm = $form->field($model, 'yield_um', ['template' => "{input}"])->dropDownList(\yii\helpers\ArrayHelper::map(\common\models\UnitOfMeasurement::getOwn()->all(), 'name', 'name'), ['class' => 'form-control'])->label(false) ?>
                    <?= $form->field($model, 'yield', [
                        'template' => "<div class='row mb-3'>{label}<div class='col-sm-8'><div class='input-group'>{input}$inputUm</div>{error}</div></div>"
                    ])->textInput()->label(null, ['class' => 'col-sm-4 text-start']) ?>
                    <?php if ($model->type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN): ?>
                        <?= $form->field($model, 'convoy_id', [
                            'template' => "<div class='row mb-3'>{label}<div class='col-sm-8'>{input}</div></div>"
                        ])->dropDownList(\yii\helpers\ArrayHelper::map(\common\models\Convoy::findAll(['business_id' => $business['id']]), 'id', 'label'), ['prompt' => Yii::t('app', "No convoy")])->label(null, ['class' => 'col-sm-4 text-start']) ?>
                    <?php endif; ?>
                    <?php if ($model->type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB): ?>
                        <?= $form->field($model, 'um', [
                            'template' => "<div class='row mb-3'>{label}<div class='col-sm-8'>{input}</div></div>"
                        ])->dropDownList(\yii\helpers\ArrayHelper::map(\common\models\UnitOfMeasurement::findAll(['business_id' => $business['id']]), 'name', 'name'))->label(null, ['class' => 'col-sm-4 text-start']) ?>
                    <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <?= $form->field($model, 'portions', [
                        'template' => "<div class='row mb-3'>{label}<div class='col-sm-9'>{input}{error}</div></div>"
                    ])->textInput()->label(null, ['class' => 'col-sm-3 text-start']) ?>
                    <?= $form->field($model, 'lifetime', [
                        'template' => "<div class='row mb-3'>{label}<div class='col-sm-9'>{input}</div></div>"
                    ])->textInput()->label(null, ['class' => 'col-sm-3 text-start']) ?>
                    <!--                    --><?php //= $form->field($model, 'title', [
                    //                        'template' => "<div class='row mb-3'>{label}<div class='col-sm-9'>{input}</div></div>"
                    //                    ])->textInput([
                    //                        'placeholder' => Yii::t('app', "Recipe Name")
                    //                    ])->label(null, ['class' => 'col-sm-3 text-start']) ?>
                    <?php if ($model->type == $model::STANDARD_RECIPE_TYPE_MAIN): ?>
                        <?= $form->field($model, 'price', [
                            'template' => "<div class='row mb-3'>{label}<div class='col-sm-9'><div class='input-group'><span class='input-group-text'>$currencySymbol</span>{input}</div></div></div>"
                        ])->textInput()->label(null, ['class' => 'col-sm-3 text-start']) ?>
                        <div class="row mb-3">
                            <div class="col-sm-3 text-start">
                                <?= Yii::t('app', "Cost") ?>
                            </div>
                            <div class="col-sm-9">
                            <span class="form-control" id="cost-value"
                                  data-price="<?= $model->lastPrice ?>"><?= $businessObj->formatter->asCurrency($model->lastPrice) ?></span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3 text-start">
                                <?= Yii::t('app', "Cost %") ?>
                            </div>
                            <div class="col-sm-9">
                            <span class="form-control"
                                  id="cost-percent"><?= Yii::$app->formatter->asPercent($model->costPercent, 0) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>


                </div>
            </div>
            <?= $this->render('create/_ingredients_selection', [
                'model' => $model
            ]) ?>
            <br>
            <?= $form->field($model, 'mainImage')->widget(\kartik\file\FileInput::class, [
                'id' => 'mainImageInput',
                'options' => [
                    'multiple' => false,
                    'accept' => 'image/*'
                ],
                'pluginOptions' => [
                    'initialPreview' => empty(($url = $model->getMainImageUrl())) ? [] : [$url],
                    'initialPreviewConfig' => empty(($id = $model->getMainImageId())) ? [] : [$id],
                    'initialPreviewAsData' => true,
                    'overwriteInitial' => false,
                    'maxFileSize' => 2800,
                    'showRotate' => false,
                    'deleteUrl' => \yii\helpers\Url::to(['standard-recipe/delete-image', 'id' => $model->id])
                ],
            ]) ?>
            <br>
            <?= $this->render('create/_steps', [
                'model' => $model
            ]) ?>

            <br>
            <?= $form->field($model, 'equipment')->widget(Summernote::class, [
                'useKrajeePresets' => true,
                'useKrajeeStyle' => false,
                'pluginOptions' => [
                    'height' => 200
                ]
                // other widget settings
            ]) ?>
            <?= $this->render('create/_special_steps', [
                'model' => $model
            ]) ?>
            <br>
            <?= $form->field($model, 'other_specs')->widget(Summernote::class, [
                'useKrajeePresets' => true,
                'useKrajeeStyle' => false,
                'pluginOptions' => [
                    'height' => 200
                ]
                // other widget settings
            ]) ?>
            <br>
            <?= $this->render('create/allergies', ['model' => $model, 'form' => $form]) ?>


            <br>
            <?= $form->field($model, 'stepsImages')->widget(\kartik\file\FileInput::class, [
                'id' => 'stepsImagesInput',
                'options' => [
                    'multiple' => true,
                    'accept' => 'image/*'
                ],
                'pluginOptions' => [
                    'initialPreview' => $model->getRecipeImagesUrl(),
                    'initialPreviewConfig' => $model->getRecipeImagesId(),
                    'initialPreviewAsData' => true,
                    'overwriteInitial' => false,
                    'maxFileSize' => 2800,
                    'showRotate' => false,
                    'deleteUrl' => \yii\helpers\Url::to(['standard-recipe/delete-image', 'id' => $model->id])

                ],
            ]) ?>

        </div>
        <div class="card-footer">
            <div class="form-group">
                <?= \yii\bootstrap5\Html::submitButton(Yii::t('app', "Save"), ['class' => 'btn btn-success']) ?>
                <?= \yii\bootstrap5\Html::a(Yii::t('app', "Cancel"), [Yii::$app->request->get('type') == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB ? 'sub-standard-recipe/index' : 'standard-recipe/index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>


    <?php ActiveForm::end(); ?>

</div>

<?php


\yii\bootstrap5\Modal::begin(['title' => Yii::t('app', 'Add ingredient or sub-recipe'),
    'id' => 'modal-add-ingredient']);

echo "<div id='container-form-ingredient'></div>";

\yii\bootstrap5\Modal::end();

?>
<?php


\yii\bootstrap5\Modal::begin(['title' => Yii::t('app', 'Modify ingredient or sub-recipe'),
    'id' => 'modal-update-ingredient']);

echo Html::label(Yii::t('app', 'Quantity'), 'ingredient-update-quantity');
echo \yii\bootstrap5\Html::input('text', 'ingredient-update-quantity', '', ['class' => 'form-control', 'placeholder' => Yii::t('app', 'Quantity'), 'id' => 'ingredient-update-quantity']);

echo \yii\bootstrap5\Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success mt-5', 'id' => 'btn-update-ingredient']);

\yii\bootstrap5\Modal::end();

?>


<?php
\yii\bootstrap5\Modal::begin(['title' => Yii::t('app', 'Add step'),
    'id' => 'modal-add-step',]);

echo $this->render('create/_form_steps', ['recipe' => $model, 'model' => new \common\models\RecipeStep(['type' => \common\models\RecipeStep::STEP_TYPE_PROCEDURE]), 'pjaxId' => '#pjax-list-steps']);

\yii\bootstrap5\Modal::end();
?>

<?php
\yii\bootstrap5\Modal::begin(['title' => Yii::t('app', 'Add special step'),
    'id' => 'modal-add-special-step',]);

echo $this->render('create/_form_steps', ['recipe' => $model, 'model' => new \common\models\RecipeStep(['type' => \common\models\RecipeStep::STEP_TYPE_SPECIAL]), 'pjaxId' => '#pjax-list-special-steps']);

\yii\bootstrap5\Modal::end();
?>
