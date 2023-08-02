<?php

use kartik\editors\Summernote;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\StandardRecipe */
/* @var $form yii\widgets\ActiveForm */

\yii\web\YiiAsset::register($this);
$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$stock = \yii\helpers\ArrayHelper::map(
    (new \yii\db\Query())
        ->select(['i.*', "CONCAT(i.ingredient, ' (', i.um, ' -> ', i.portion_um,')') as label"])
        ->from('ingredient_stock i')
        ->leftJoin('ingredient_standard_recipe isr', 'i.id=isr.ingredient_id')
        ->leftJoin('standard_recipe sr', 'isr.standard_recipe_id = sr.id')
        ->where(['or', ['sr.id' => null], ['<>', 'sr.id', $model->id]])
        ->andWhere(['i.business_id' => $business['id']])
        ->all(),
    'id', 'label'
);

$subRecipes = \yii\helpers\ArrayHelper::map(
    (new \yii\db\Query())
        ->select(["id", "sr.title as label"])
        ->from("standard_recipe sr")
        ->where([
            'sr.business_id' => $business['id'],
            'sr.type' => \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB
        ])
        ->all(),
    'id', 'label'
);

$ingredients = (new \yii\db\Query())
    ->select("*")
    ->from('ingredient')
    ->all();
$autocompleteUm = array_values(array_unique(\yii\helpers\ArrayHelper::getColumn($ingredients, 'um')));

$this->registerJsFile(Yii::getAlias("@web/js/standard-recipe/form.js"), [
    'position' => $this::POS_END,
    'depends' => [\yii\web\YiiAsset::class]
]);
$this->registerCssFile(Yii::getAlias("@web/css/flowchart.css"));


?>

<div class="standard-recipe-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-recipe',
        'options' => [
            'enctype' => 'multipart/form-data'
        ]
    ]); ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-borderless">
                    <tbody>
                    <tr>
                        <td class="bg-primary text-white" style="font-weight: bold">
                            <?= Yii::t('app', 'Recipe Type') ?>
                        </td>
                        <td class="bg-primary text-white" style="font-weight: bold">
                            <?= Yii::t('app', 'Time of preparation') ?>
                        </td>
                        <td class="bg-primary text-white" style="font-weight: bold">
                            <?= Yii::t('app', 'Yield') ?>
                        </td>
                        <td class="bg-primary text-white" style="font-weight: bold">
                            <?= Yii::t('app', 'Portions') ?>
                        </td>
                        <td class="bg-primary text-white" style="font-weight: bold">
                            <?= Yii::t('app', 'Lifetime') ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?= $model->formattedType ?>
                        </td>
                        <td>
                            <?= $form->field($model, 'time_of_preparation')->textInput()->label(false) ?>
                        </td>
                        <td>
                            <div class="d-flex">
                                <?= $form->field($model, 'yield')->textInput()->label(false) ?>
                                <?= $form->field($model, 'yield_um')->widget(\kartik\typeahead\Typeahead::class, [
                                    'scrollable' => true,
                                    'defaultSuggestions' => $autocompleteUm,
                                    'dataset' => [
                                        [
                                            'local' => $autocompleteUm,
                                            'limit' => 10,

                                        ]
                                    ],
                                    'options' => [
                                        'autocomplete' => "off"
                                    ]
                                ])->label(false) ?>
                            </div>

                        </td>
                        <td>
                            <?= $form->field($model, 'portions')->textInput()->label(false) ?>
                        </td>
                        <td>
                            <?= $form->field($model, 'lifetime')->textInput()->label(false) ?>
                        </td>

                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless">
                    <tr>
                        <td class="bg-primary text-white text-center" style="font-weight: bold">
                            <div class="row justify-content-center align-items-center">
                                <div class="col-sm-12 col-md-6 col-lg-3 col-xl-4">
                                    <?= $form->field($model, 'title')->textInput([
                                        'style' => "background-color: transparent; border: none; color: white; font-weight: bold; text-align: center",
                                        'placeholder' => Yii::t('app', "A good recipe")
                                    ])->label(false) ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="row">
                <div class="col-12 mt-3">
                    <?= $this->render('create/_ingredients_selection', [
                        'model' => $model
                    ]) ?>
                </div>
                <div class="col-12 mt-3">
                    <?= $this->render('create/_steps', [
                        'model' => $model
                    ]) ?>
                </div>
                <div class="col-12 mt-3">
                    <table class="table table-borderless">
                        <tbody>
                        <tr>
                            <td class="bg-primary text-center text-white">
                                <?= $model->getAttributeLabel('equipment') ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?= $form->field($model, 'equipment')->widget(Summernote::class, [
                                    'useKrajeePresets' => true,
                                    'useKrajeeStyle' => false,
                                    'pluginOptions' => [
                                        'height' => 200
                                    ]
                                    // other widget settings
                                ])->label(false) ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-12 mt-3">
                    <table class="table table-borderless">
                        <tbody>
                        <tr>
                            <td class="bg-primary text-center text-white">
                                <?= $model->getAttributeLabel('allergies') ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?= $form->field($model, 'allergies')->widget(Summernote::class, [
                                    'useKrajeePresets' => true,
                                    'useKrajeeStyle' => false,
                                    'pluginOptions' => [
                                        'height' => 200
                                    ]
                                    // other widget settings
                                ])->label(false) ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </div>
                <div class="col-12">
                    <table class="table table-borderless">
                        <tbody>
                        <tr>
                            <td class="bg-primary text-center text-white">
                                <?= Yii::t('app', 'Flowchart') ?>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                    <div class="d-flex flex-wrap align-content-center">
                        <div class="align-self-center"><span class="flowchart-circle"><?= Yii::t('app', "Start") ?></span></div>
                        <div class="align-self-center"><i class="bx bx-right-arrow"></i></div>
                        <?php foreach ($model->recipeSteps as $step): ?>
                            <?= $this->render('_step', ['step' => $step]) ?>
                            <div class="align-self-center"><i class="bx bx-right-arrow"></i></div>
                        <?php endforeach; ?>
                        <div class="align-self-center"><span class="flowchart-circle"><?= Yii::t('app', "End") ?></span></div>
                    </div>
                    <table class="table table-borderless">
                        <tbody>
                        <tr>
                            <td class="bg-primary text-center text-white">
                                <?= Yii::t('app', 'Pictures') ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <?= $form->field($model, 'mainImage')->widget(\kartik\file\FileInput::class, [
                        'id' => 'mainImageInput',
                        'options' => [
                            'multiple' => false,
                            'accept' => 'image/*'
                        ],
                        'pluginOptions' => [
                            'initialPreview' => [$model->getMainImageUrl()],
                            'initialPreviewConfig' => [$model->getMainImageId()],
                            'initialPreviewAsData' => true,
                            'overwriteInitial' => false,
                            'maxFileSize' => 2800,
                            'showRotate' => false,
                            'deleteUrl' => \yii\helpers\Url::to(['standard-recipe/delete-image', 'id' => $model->id])

                        ],
                    ]) ?>
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

            </div>
        </div>
        <div class="card-footer">
            <?= \yii\bootstrap5\Html::submitButton(Yii::t('app', "Save"), ['class' => 'btn btn-success']) ?>
        </div>
    </div>


    <?php ActiveForm::end(); ?>

</div>
<?php

\yii\bootstrap5\Modal::begin(['title' => Yii::t('app', 'Add ingredient'),
    'id' => 'modal-add-ingredient',]);

echo $this->render('create/_form_ingredient', ['stock' => $stock, 'recipe' => $model, 'model' => new \backend\models\StandardRecipeIngredientForm(), 'subRecipes' => $subRecipes]);

\yii\bootstrap5\Modal::end();

?>

<?php
\yii\bootstrap5\Modal::begin(['title' => Yii::t('app', 'Add step'),
    'id' => 'modal-add-step',]);

echo $this->render('create/_form_steps', ['recipe' => $model, 'model' => new \common\models\RecipeStep()]);

\yii\bootstrap5\Modal::end();
?>
