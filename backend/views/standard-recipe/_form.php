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
$formatConfig = [
    'decimalSeparator' => $businessObj->decimal_separator,
    'thousandSeparator' => $businessObj->thousands_separator,
    'currencySymbol' => $currencySymbol,
];
$this->registerJsVar('userFormatConfig', $formatConfig);
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
                        ])->textInput([
                            'id' => 'title-input', 
                            'placeholder' => $model->type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN 
                                ? Yii::t('app', 'Ingrese el nombre de la receta') 
                                : Yii::t('app', 'Ingrese el nombre de la subreceta')
                        ])->label(
                            $model->type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN 
                                ? 'Nombre de la receta' 
                                : 'Nombre de la subreceta', 
                            ['class' => 'col-sm-4 text-start']
                        ) ?>
                    <?php endif; ?>
                    <?= $form->field($model, 'type_of_recipe', [
                        'template' => "<div class='row mb-3'>{label}<div class='col-sm-8'>{input}</div></div>"
                    ])->dropDownList($recipesCategoriesMap)->label(null, ['class' => 'col-sm-4 text-start']) ?>
                    <?= $form->field($model, 'time_of_preparation', [
                        'template' => "<div class='row mb-3'>{label}<div class='col-sm-8'>{input}</div></div>"
                    ])->textInput()->label(null, ['class' => 'col-sm-4 text-start']) ?>
                    <?php $inputUm = $form->field($model, 'yield_um', ['template' => "{input}"])->dropDownList(\yii\helpers\ArrayHelper::map(\common\models\UnitOfMeasurement::getOwn()->all(), 'name', 'name'), ['class' => 'form-control','id' => 'standardrecipe-yield_um'])->label(false) ?>
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
                    <?php if ($model->type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN): ?>
                    <?= $form->field($model, 'is_food')->widget(\kartik\switchinput\SwitchInput::class, [
                        'pluginOptions' => [
                            'onText' => "Alimentos",
                            'offText' => "Bebidas"
                        ]
                    ])->label("Alimentos o bebidas?") ?>
                    <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                <div id="portions-container">
                    <?= $form->field($model, 'portions', [
                        'template' => "<div class='row mb-3'>{label}<div class='col-sm-9'>{input}{error}</div></div>"
                    ])->textInput()->label(null, ['class' => 'col-sm-3 text-start']) ?>
                </div>
                    <?= $form->field($model, 'lifetime', [
                        'template' => "<div class='row mb-3'>{label}<div class='col-sm-9'>{input}</div></div>"
                    ])->textInput()->label(null, ['class' => 'col-sm-3 text-start']) ?>
                    <?php if ($model->type == $model::STANDARD_RECIPE_TYPE_MAIN): ?>
                        <?= $form->field($model, 'price', [
                            'template' => "<div class='row mb-3'>{label}<div class='col-sm-9'><div class='input-group'><span class='input-group-text'>$currencySymbol</span>{input}</div>{error}</div></div>"
                        ])->textInput([
                            'onkeyup' => 'this.value = this.value.replace(/[^0-9.,]/g, "")',
                            'id' => 'price-input',
                            'value' => $model->price !== null && $model->price !== '' ? $businessObj->formatter->asCurrency($model->price) : '',
                            'class' => 'form-control number-input',
                            'data-raw-value' => $model->price
                        ])->label(null, ['class' => 'col-sm-3 text-start']) ?>
                        <div class="row mb-3">
                            <div class="col-sm-3 text-start">
                                <?= Yii::t('app', "Cost") ?>
                            </div>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <span class="input-group-text"><?= $currencySymbol ?></span>
                                    <span class="form-control" id="cost-value"
                                        data-price="<?= $model->lastPrice ?>"><?= $businessObj->formatter->asCurrency($model->lastPrice) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3 text-start">
                                <?= Yii::t('app', "Cost %") ?>
                            </div>
                            <div class="col-sm-9">
                                <span class="form-control" 
                                    id="cost-percent"
                                    data-raw-value="<?= $model->costPercent ?? 0 ?>">
                                    <?= Yii::$app->formatter->asPercent($model->costPercent, 0)?>
                                </span>
                                <small class="form-text text-muted">
                                    <?= Yii::t('app', "Porcentaje del costo en relación al precio de venta") ?>
                                </small>
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
                    'maxFileSize' => 3072,
                    'showRotate' => false,
                    'deleteUrl' => \yii\helpers\Url::to(['standard-recipe/delete-image', 'id' => $model->id]),
                    'msgSizeTooLarge' => Yii::t('app', 'El archivo seleccionado es demasiado grande. El tamaño máximo permitido es de 3MB.'),
                    'showUpload' => false, // Disable upload button
                    'browseLabel' => Yii::t('app', 'Seleccionar imagen (máx. 3MB)'), // Custom label for the browse button
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
\yii\bootstrap5\Modal::begin([
    'title' => Yii::t('app', 'Modificar ingrediente o subreceta'),
    'id' => 'modal-update-ingredient'
]);
?>
<div class="modal-body">
    <div class="form-group mb-3">
        <?= Html::label(Yii::t('app', 'Ingrediente o Subreceta'), 'ingredient-select', ['class' => 'form-label']) ?>
        <select class="form-select" id="ingredient-select"></select>
        <div id="ingredient-select-container" class="mb-3">
            <!-- Los ingredientes o subrecetas se cargarán aquí -->
        </div>
    </div>

    <div class="form-group mb-3">
        <?= Html::label(Yii::t('app', 'Cantidad'), 'ingredient-update-quantity', ['class' => 'form-label']) ?>
        <?= Html::textInput('ingredient-update-quantity', '', [
            'class' => 'form-control',
            'placeholder' => Yii::t('app', 'Ingrese la cantidad'),
            'id' => 'ingredient-update-quantity',
            'type' => 'number',
            'step' => 'any'
        ]) ?>
        <div id="quantity-update-warning" class="text-danger small mt-1" style="display: none;"></div>
    </div>
</div>

<div class="modal-footer">
    <?= Html::button(Yii::t('app', 'Cancelar'), [
        'class' => 'btn btn-secondary',
        'data-bs-dismiss' => 'modal'
    ]) ?>
    <?= Html::submitButton(Yii::t('app', 'Guardar'), [
        'class' => 'btn btn-success',
        'id' => 'btn-update-ingredient'
    ]) ?>
</div>
<?php \yii\bootstrap5\Modal::end(); ?>


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

<script>
    document.getElementById('title-input').addEventListener('blur', function (e) {
    const value = e.target.value;
    const businessId = '<?= $business['id'] ?>'; // Assuming $business['id'] contains the business ID
    const type = '<?= $model->type ?>'; // Assuming $model->type contains the recipe type
    if (value) {

        // Create a FormData object and append the data
        const formData = new FormData();
        formData.append('title', value);
        formData.append('business_id', businessId);
        formData.append('type', type);

        fetch('<?= \yii\helpers\Url::to(['standard-recipe/check-title']) ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
            },
            body: formData
        })
        .then(response => {
            return response.json();
        })
        .then(data => {
            if (data.exists) {
                const errorElement = document.createElement('div');
                errorElement.className = 'invalid-feedback';
                errorElement.innerText = 'El nombre está en uso. Por favor, elige otro.';
                e.target.classList.add('is-invalid');
                e.target.parentNode.appendChild(errorElement);
                e.target.value = '';
            } else {
                e.target.classList.remove('is-invalid');
                const errorElement = e.target.parentNode.querySelector('.invalid-feedback');
                if (errorElement) {
                    errorElement.remove();
                }
            }
        })
        .catch(error => {
            console.error('Error:', error); // Log any errors
        });
    }
});
</script>