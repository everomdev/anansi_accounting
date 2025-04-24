<?php

use kartik\editors\Summernote;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->registerCss("
    label.required:after {
        content: ' *';
        color: red;
    }
");

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
    <div class="card-header bg-light">
            <div class="alert alert-info mb-0 py-2 px-3">
                <i class="fas fa-info-circle me-2"></i>
                <?= Yii::t('app', 'Los campos marcados con <span class="required">*</span> son obligatorios.') ?>
            </div>
        </div>
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
                            ['class' => 'col-sm-4 text-start required']
                        ) ?>
                    <?php endif; ?>
                    <?= $form->field($model, 'type_of_recipe', [
                        'template' => "<div class='row mb-3'>{label}<div class='col-sm-8'>{input}</div></div>"
                    ])->dropDownList($recipesCategoriesMap)->label(null, ['class' => 'col-sm-4 text-start required']) ?>
                    <div class="row mb-3">
                        <label class="col-sm-4 text-start"><?= $model->getAttributeLabel('time_of_preparation') ?></label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <?= Html::textInput('time_value', 
                                    $model->time_of_preparation ? preg_replace('/[^0-9]/', '', $model->time_of_preparation) : '', 
                                    [
                                        'id' => 'time-value-input',
                                        'class' => 'form-control',
                                        'placeholder' => Yii::t('app', 'Tiempo'),
                                        'onkeypress' => 'return event.charCode >= 48 && event.charCode <= 57',
                                        'style' => 'max-width: 100px;'
                                    ]) 
                                ?>
                                <?= Html::dropDownList('time_unit', 
                                    $model->time_of_preparation ? 
                                        (strpos($model->time_of_preparation, 'día') !== false ? 'días' :
                                            (strpos($model->time_of_preparation, 'hora') !== false ? 'horas' : 'minutos')) : 
                                        'minutos',
                                    [
                                        'minutos' => Yii::t('app', 'minutos'),
                                        'horas' => Yii::t('app', 'horas'),
                                        'días' => Yii::t('app', 'días')
                                    ],
                                    [
                                        'id' => 'time-unit-select',
                                        'class' => 'form-select',
                                        'style' => 'max-width: 150px;'
                                    ]) 
                                ?>
                                <?= $form->field($model, 'time_of_preparation', ['template' => '{input}{error}'])->hiddenInput(['id' => 'time-of-preparation-hidden'])->label(false) ?>
                            </div>
                        </div>
                    </div>
                    <?php $inputUm = $form->field($model, 'yield_um', ['template' => "{input}"])->dropDownList(\yii\helpers\ArrayHelper::map(\common\models\UnitOfMeasurement::getOwn()->all(), 'name', 'name'), ['class' => 'form-control','id' => 'standardrecipe-yield_um'])->label(false) ?>
                    <?= $form->field($model, 'yield', [
                        'template' => "<div class='row mb-3'>{label}<div class='col-sm-8'><div class='input-group'>{input}$inputUm</div>{error}</div></div>"
                    ])->textInput()->label(null, ['class' => 'col-sm-4 text-start required']) ?>
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
                    ])->textInput()->label(null, ['class' => 'col-sm-3 text-start required']) ?>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 text-start"><?= $model->getAttributeLabel('lifetime') ?></label>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <?= Html::textInput('lifetime_value', 
                                $model->lifetime ? preg_replace('/[^0-9]/', '', $model->lifetime) : '', 
                                [
                                    'id' => 'lifetime-value-input',
                                    'class' => 'form-control',
                                    'placeholder' => Yii::t('app', 'Duración'),
                                    'onkeypress' => 'return event.charCode >= 48 && event.charCode <= 57',
                                    'style' => 'max-width: 100px;'
                                ]) 
                            ?>
                            <?= Html::dropDownList('lifetime_unit', 
                                $model->lifetime ? 
                                    (strpos($model->lifetime, 'día') !== false ? 'días' :
                                        (strpos($model->lifetime, 'hora') !== false ? 'horas' : 'minutos')) : 
                                    'días',
                                [
                                    'minutos' => Yii::t('app', 'minutos'),
                                    'horas' => Yii::t('app', 'horas'),
                                    'días' => Yii::t('app', 'días')
                                ],
                                [
                                    'id' => 'lifetime-unit-select',
                                    'class' => 'form-select',
                                    'style' => 'max-width: 150px;'
                                ]) 
                            ?>
                            <?= $form->field($model, 'lifetime', ['template' => '{input}{error}'])->hiddenInput(['id' => 'lifetime-hidden'])->label(false) ?>
                        </div>
                    </div>
                </div>
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
                    <?= $form->field($model, 'observation')->widget(Summernote::class, [
                        'useKrajeePresets' => true,
                        'useKrajeeStyle' => false,
                        'pluginOptions' => [
                            'height' => 100,
                            'toolbar' => [
                                ['style', ['bold', 'italic', 'underline', 'clear']],
                                ['font', ['strikethrough']],
                                ['para', ['ul', 'ol']],
                                ['insert', ['link']]
                            ]
                        ]
                    ]) ?>
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
// Función para validar y formatear el precio según la configuración del usuario
function validateAndFormatPrice(input, forceFormat = false) {
    const decimalSeparator = userFormatConfig.decimalSeparator;
    const thousandSeparator = userFormatConfig.thousandSeparator;

    // Guardar posición del cursor
    const cursorPosition = input.selectionStart;

    // Obtener valor sin símbolo de moneda
    let value = input.value.replace(userFormatConfig.currencySymbol, '').trim();
    
    // Eliminar caracteres no válidos (excepto el separador decimal configurado)
    const validChars = new RegExp(`[^0-9\\${decimalSeparator}]`, 'g');
    value = value.replace(validChars, '');

    // Reemplazar múltiples separadores decimales por uno solo
    const decimalCount = (value.match(new RegExp(`\\${decimalSeparator}`, 'g')) || []).length;
    if (decimalCount > 1) {
        const parts = value.split(decimalSeparator);
        value = parts[0] + decimalSeparator + parts.slice(1).join('');
    }

    // Si está escribiendo, no formatear aún (excepto para limitar decimales)
    if (!forceFormat && document.activeElement === input) {
        // Limitar a 2 decimales si ya hay separador
        const parts = value.split(decimalSeparator);
        if (parts.length > 1) {
            parts[1] = parts[1].slice(0, 2);
            value = parts.join(decimalSeparator);
        }
        input.value = value;
        input.setAttribute('data-raw-value', value.replace(decimalSeparator, '.'));
        
        // Restaurar posición del cursor
        setTimeout(() => {
            input.setSelectionRange(cursorPosition, cursorPosition);
        }, 0);
        return;
    }

    // Formato completo al perder el foco
    let parts = value.split(decimalSeparator);
    let wholePart = parts[0].replace(/\D/g, '') || '0'; // Solo dígitos
    let decimalPart = parts.length > 1 ? parts[1].replace(/\D/g, '').slice(0, 2) : '00';

    // Agregar separadores de miles solo al final
    if (wholePart.length > 3) {
        wholePart = wholePart.replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);
    }

    // Construir valor formateado
    let formattedValue = wholePart;
    if (decimalPart.length > 0) {
        formattedValue += decimalSeparator + decimalPart;
    } else if (forceFormat) {
        formattedValue += decimalSeparator + '00';
    }

    // Actualizar campo
    input.value = formattedValue;
    input.setAttribute('data-raw-value', 
        formattedValue.replace(new RegExp(`\\${thousandSeparator}`, 'g'), '')
                     .replace(decimalSeparator, '.')
    );
}

// Configuración de eventos (igual que antes)
document.addEventListener('DOMContentLoaded', function() {
    const priceInput = document.getElementById('price-input');
    
    priceInput.addEventListener('focus', function() {
        const rawValue = this.getAttribute('data-raw-value') || '';
        this.value = rawValue.replace('.', userFormatConfig.decimalSeparator);
    });
    
    priceInput.addEventListener('blur', function() {
        validateAndFormatPrice(this, true);
    });
    
    priceInput.addEventListener('keydown', function(e) {
        const decimalSeparator = userFormatConfig.decimalSeparator;
        const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End', 'Enter'];
        
        if (allowedKeys.includes(e.key)) return;
        
        if (!/^[0-9]$/.test(e.key) && e.key !== decimalSeparator) {
            e.preventDefault();
        }
    });
    
    priceInput.addEventListener('input', function() {
        validateAndFormatPrice(this, false);
    });
});

// Configuración por defecto
window.userFormatConfig = window.userFormatConfig || {
    decimalSeparator: '.',
    thousandSeparator: ',',
    currencySymbol: '$',
    decimalPlaces: 2
};

// Manejo del tiempo de preparación
document.addEventListener('DOMContentLoaded', function() {
    const timeValueInput = document.getElementById('time-value-input');
    const timeUnitSelect = document.getElementById('time-unit-select');
    const timeOfPreparationHidden = document.getElementById('time-of-preparation-hidden');
    
    // Actualizar el campo oculto cuando cambie alguno de los campos visibles
    function updateTimeOfPreparation() {
        const value = timeValueInput.value.trim();
        const unit = timeUnitSelect.value;
        
        if (value) {
            timeOfPreparationHidden.value = value + ' ' + unit;
        } else {
            timeOfPreparationHidden.value = '';
        }
    }
    
    // Eventos para actualizar el campo oculto
    timeValueInput.addEventListener('input', updateTimeOfPreparation);
    timeValueInput.addEventListener('change', updateTimeOfPreparation);
    timeUnitSelect.addEventListener('change', updateTimeOfPreparation);
    
    // Inicializar el campo oculto con los valores actuales
    updateTimeOfPreparation();
    
    // Solo permitir números en el campo de valor
    timeValueInput.addEventListener('keypress', function(e) {
        if (e.charCode < 48 || e.charCode > 57) {
            e.preventDefault();
            return false;
        }
    });
    
    // Buscar el campo select para permitir entrada de texto
    const unitSelect = document.getElementById('time-unit-select');
    if (unitSelect) {
        // Opcionalmente, puedes usar un plugin como Select2 para permitir búsqueda
        // Si ya tienes Select2 en tu proyecto:
        // $(unitSelect).select2({
        //     minimumResultsForSearch: -1, // No mostrar búsqueda para pocas opciones
        //     width: '100%'
        // });
    }
    
    // Asegurar que el formulario envíe el valor combinado
    document.getElementById('form-recipe').addEventListener('submit', function() {
        updateTimeOfPreparation();
    });
});
// Manejo de la duración (lifetime)
document.addEventListener('DOMContentLoaded', function() {
    const lifetimeValueInput = document.getElementById('lifetime-value-input');
    const lifetimeUnitSelect = document.getElementById('lifetime-unit-select');
    const lifetimeHidden = document.getElementById('lifetime-hidden');
    
    // Actualizar el campo oculto cuando cambie alguno de los campos visibles
    function updateLifetime() {
        const value = lifetimeValueInput.value.trim();
        const unit = lifetimeUnitSelect.value;
        
        if (value) {
            lifetimeHidden.value = value + ' ' + unit;
        } else {
            lifetimeHidden.value = '';
        }
    }
    
    // Eventos para actualizar el campo oculto
    lifetimeValueInput.addEventListener('input', updateLifetime);
    lifetimeValueInput.addEventListener('change', updateLifetime);
    lifetimeUnitSelect.addEventListener('change', updateLifetime);
    
    // Inicializar el campo oculto con los valores actuales
    updateLifetime();
    
    // Solo permitir números en el campo de valor
    lifetimeValueInput.addEventListener('keypress', function(e) {
        if (e.charCode < 48 || e.charCode > 57) {
            e.preventDefault();
            return false;
        }
    });
    
    // Asegurar que el formulario envíe el valor combinado
    document.getElementById('form-recipe').addEventListener('submit', function() {
        updateLifetime();
    });
});
</script>