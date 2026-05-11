<style>
.standard-recipe-form label .asterisk,
.standard-recipe-form label .required {
    color: #dc3545 !important;
    font-weight: bold;
}
.pending-checkbox {
    /* display: inline-block !important;  <-- Eliminado para que d-none funcione */
    position: static !important;
    transform: none !important;
    width: 15px;
    height: 15px;
    margin-left: 8px;
    vertical-align: middle;
    cursor: pointer;
    accent-color: #007bff;
    border: 2px solid #007bff !important;
    box-shadow: none !important;
}
.pending-mode .pending-field-group > label,
.pending-mode .pending-field-group label {
    display: inline-flex !important;
    align-items: center !important;
}
</style>

<?php

use kartik\editors\Summernote;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->registerCss("
    label.required:after {
        content: '*';
        color: red;
    }
    
    /* Estilos para secciones colapsables */
    .card-header .btn-link {
        color: #333;
        font-weight: 500;
        font-size: 1rem;
    }
    
    .card-header .btn-link:hover {
        color: #007bff;
    }
    
    .card-header .btn-link .fa-chevron-down {
        transition: transform 0.3s ease;
    }
    
    .card-header .btn-link:not(.collapsed) .fa-chevron-down {
        transform: rotate(180deg);
    }
    
    .card-header .btn-link:focus {
        box-shadow: none;
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
    'depends' => [\yii\web\JqueryAsset::class, \yii\web\YiiAsset::class]
]);
$this->registerCssFile(Yii::getAlias("@web/css/flowchart.css"));

$recipesCategories = \common\models\RecipeCategory::find()
    ->where(['business_id' => $business['id'], 'type' => $model->type])
    ->orderBy(['name' => SORT_ASC])
    ->all();

$businessObj = \common\models\Business::findOne(['id' => $business['id']]);

// Obtener símbolo de moneda
$currencySymbol = \common\helpers\NumberFormatter::getFormatConfig()['currency_symbol'];

$recipesCategoriesMap = \yii\helpers\ArrayHelper::map($recipesCategories, 'name', 'name');

$recipesCategoriesMap['add'] = Yii::t('app', "+ Agregar");

$this->registerJsVar('createNewCategoryUrl', \yii\helpers\Url::to(['recipe-category/index']));

// Use global number formatter configuration
$formatConfig = \common\helpers\NumberFormatter::getJsConfig();
$this->registerJsVar('userFormatConfig', $formatConfig);

$convoys = \common\models\Convoy::findAll(['business_id' => $business['id']]);
$convoyAmounts = [];
foreach ($convoys as $convoy) {
    $convoyAmounts[$convoy->id] = $convoy->amount;
}
// Registrar correctamente la variable global para JS
$this->registerJs('window.convoyAmounts = ' . json_encode($convoyAmounts) . ';', \yii\web\View::POS_HEAD);
?>

<div class="standard-recipe-form">
    <div class="d-flex justify-content-end mb-2">
        <button type="button" id="btn-toggle-pending" class="btn btn-warning">
            <i class="bi bi-exclamation-circle"></i> Pendientes
        </button>
    </div>

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
                    <div class="pending-field-group" data-field="title">
                        <?= $form->field($model, 'title', [
                            'template' => "<div class='row mb-3'>{label}<div class='col-sm-8'>{input}{error}</div></div>"
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
                    </div>
                    <?php endif; ?>
                    <div class="pending-field-group" data-field="type_of_recipe">
                        <?= $form->field($model, 'type_of_recipe', [
                            'template' => "<div class='row mb-3'>{label}<div class='col-sm-8'>{input}{error}</div></div>"
                        ])->dropDownList($recipesCategoriesMap, ['prompt' => Yii::t('app', 'Seleccione una categoría...')])->label(
                            $model->type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB
                                ? Yii::t('app', 'Categoría de subreceta')
                                : Yii::t('app', 'Categoría de receta'),
                            ['class' => 'col-sm-4 text-start required']
                        ) ?>
                    </div>
                    <div class="pending-field-group" data-field="time_of_preparation">
                        <div class="row mb-3">
                            <label class="col-sm-4 text-start"><?= $model->getAttributeLabel('time_of_preparation') ?></label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <?= Html::textInput(
                                        'time_value',
                                        $model->time_of_preparation ? preg_replace('/[^0-9]/', '', $model->time_of_preparation) : '',
                                        [
                                            'id' => 'time-value-input',
                                            'class' => 'form-control',
                                            'placeholder' => Yii::t('app', 'Tiempo'),
                                            'onkeypress' => 'return event.charCode >= 48 && event.charCode <= 57',
                                            'style' => 'max-width: 100px;'
                                        ]
                                    )
                                    ?>
                                    <?php
                                    // Determinar el valor por defecto para time_unit
                                    $timeUnitValue = '';
                                    if ($model->time_of_preparation) {
                                        // Si hay datos, detectar la unidad
                                        if (strpos($model->time_of_preparation, 'día') !== false) {
                                            $timeUnitValue = 'días';
                                        } elseif (strpos($model->time_of_preparation, 'hora') !== false) {
                                            $timeUnitValue = 'horas';
                                        } elseif (strpos($model->time_of_preparation, 'minuto') !== false) {
                                            $timeUnitValue = 'minutos';
                                        }
                                    }
                                    ?>
                                    <?= Html::dropDownList(
                                        'time_unit',
                                        $timeUnitValue,
                                        [
                                            '' => Yii::t('app', 'Seleccionar...'),
                                            'minutos' => Yii::t('app', 'minutos'),
                                            'horas' => Yii::t('app', 'horas'),
                                            'días' => Yii::t('app', 'días')
                                        ],
                                        [
                                            'id' => 'time-unit-select',
                                            'class' => 'form-select',
                                            'style' => 'max-width: 150px;'
                                        ]
                                    )
                                    ?>
                                    <?= $form->field($model, 'time_of_preparation', ['template' => '{input}{error}'])->hiddenInput(['id' => 'time-of-preparation-hidden'])->label(false) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    // Convertir null a vacío para preseleccionar "Sin unidades"
                    if ($model->yield_um === null) {
                        $model->yield_um = '';
                    }
                    $yieldUms = \common\models\UnitOfMeasurement::getOwn()->all();
                    if ($model->type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN) {
                        $yieldUms = array_filter($yieldUms, function($um) { return ($um->is_recipe_yield ?? 0) == 1; });
                    } elseif ($model->type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB) {
                        $yieldUms = array_filter($yieldUms, function($um) { return ($um->is_subrecipe_yield ?? 0) == 1; });
                    }
                    $yieldUmOptions = \yii\helpers\ArrayHelper::map($yieldUms, 'name', 'name');
                    $yieldUmOptions = ['' => 'Sin unidades'] + $yieldUmOptions;
                    //var_dump($model);
                    $selectYieldUmHtml = \yii\helpers\Html::dropDownList(
                        'StandardRecipe[yield_um]',
                        $model->yield_um,
                        $yieldUmOptions,
                        ['class' => 'form-control', 'id' => 'standardrecipe-yield_um-visible']
                    );
                    $yieldUmErrorDiv = '<div id="yield-um-error" class="text-danger small mt-1" style="display:none">' . Yii::t('app', 'Unidad de medida no puede estar vacío.') . '</div>';
                    ?>
                    <div class="pending-field-group" data-field="yield">
                        <?= $form->field($model, 'yield', [
                            'template' => "<div class='row mb-3'>{label}<div class='col-sm-8'><div class='input-group'>{input}$selectYieldUmHtml</div>{error}$yieldUmErrorDiv</div></div>"
                        ])->textInput()->label(null, ['class' => 'col-sm-4 text-start required']) ?>
                    </div>
                    <?php if ($model->type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN): ?>
                        <div class="pending-field-group" data-field="convoy_id">
                            <?= $form->field($model, 'convoy_id', [
                                'template' => "<div class='row mb-3'>{label}<div class='col-sm-8'>{input}</div></div>"
                            ])->dropDownList(\yii\helpers\ArrayHelper::map(\common\models\Convoy::findAll(['business_id' => $business['id']]), 'id', 'label'), ['prompt' => Yii::t('app', "No convoy")])->label(null, ['class' => 'col-sm-4 text-start']) ?>
                        </div>
                    <?php endif; ?>
                    <?php
                    // Convertir null a vacío para preseleccionar "Sin unidades"
                    if ($model->um === null) {
                        $model->um = '';
                    }
                    $finalUms = \common\models\UnitOfMeasurement::findAll(['business_id' => $business['id']]);
                    if ($model->type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN) {
                        $finalUms = array_filter($finalUms, function($um) { return ($um->is_recipe_final_um ?? 0) == 1; });
                    } elseif ($model->type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB) {
                        $finalUms = array_filter($finalUms, function($um) { return ($um->is_subrecipe_um ?? 0) == 1; });
                    }
                    $finalUmOptions = \yii\helpers\ArrayHelper::map($finalUms, 'name', 'name');
                    $finalUmOptions = ['' => 'Sin unidades'] + $finalUmOptions;
                    echo '<div class="pending-field-group" data-field="um">';
                    echo $form->field($model, 'um', [
                        'template' => "<div class='row mb-3'>{label}<div class='col-sm-8'>{input}{error}</div></div>"
                    ])->dropDownList($finalUmOptions)->label(
                        $model->type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB
                            ? Yii::t('app', 'Unidad de medida')
                            : Yii::t('app', 'Unidad final'),
                        ['class' => 'col-sm-4 text-start required']
                    );
                    echo '</div>';
                    ?>
                    <?php if ($model->type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_MAIN): ?>
                        <?php
                        // Build map: category name → is_food for JS dynamic update
                        $categoryIsFoodMap = [];
                        foreach ($recipesCategories as $_cat) {
                            $categoryIsFoodMap[$_cat->name] = $_cat->is_food; // null | 0 | 1
                        }
                        // Determine current category
                        $_currentCat = null;
                        if (!empty($model->type_of_recipe)) {
                            foreach ($recipesCategories as $_cat) {
                                if ($_cat->name === $model->type_of_recipe) {
                                    $_currentCat = $_cat;
                                    break;
                                }
                            }
                        }
                        function _isFoodBadgeHtml($cat) {
                            if ($cat === null || $cat->is_food === null) {
                                return '<span class="badge bg-warning text-dark">Sin definir en la categoría</span>';
                            }
                            return $cat->is_food
                                ? '<span class="badge bg-primary">Alimentos</span>'
                                : '<span class="badge bg-info text-dark">Bebidas</span>';
                        }
                        ?>
                        <div class="row mb-3">
                            <label class="col-sm-4 text-start"><?= Yii::t('app', 'Alimentos o Bebidas') ?></label>
                            <div class="col-sm-8">
                                <div class="form-control-plaintext" id="is-food-badge">
                                    <?= _isFoodBadgeHtml($_currentCat) ?>
                                </div>
                            </div>
                        </div>
                        <?php $this->registerJs('
                        (function() {
                            var categoryIsFoodMap = ' . json_encode($categoryIsFoodMap) . ';
                            function updateIsFoodBadge(categoryName) {
                                var badge = document.getElementById("is-food-badge");
                                if (!badge) return;
                                if (categoryName && categoryIsFoodMap.hasOwnProperty(categoryName) && categoryIsFoodMap[categoryName] !== null) {
                                    badge.innerHTML = categoryIsFoodMap[categoryName] == 1
                                        ? \'<span class="badge bg-primary">Alimentos</span>\'
                                        : \'<span class="badge bg-info text-dark">Bebidas</span>\';
                                } else {
                                    badge.innerHTML = \'<span class="badge bg-warning text-dark">Sin definir en la categoría</span>\';
                                }
                            }
                            $(document).ready(function() {
                                $("#standardrecipe-type_of_recipe").on("change", function() {
                                    updateIsFoodBadge(this.value);
                                });
                            });
                        })();
                        '); ?>
                    <?php endif; ?>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                    <div id="portion-size-container" class="row mb-3 d-none">
                        <label class="col-sm-3 text-start">Tamaño de porción</label>
                        <div class="col-sm-9">
                            <div class="input-group">
                                <input type="text" id="portion-size" class="form-control" placeholder="Ej: 0.150">
                                <span class="input-group-text portion-size-unit"></span>
                            </div>
                            <small class="form-text text-muted">Define cuánto pesa o mide cada porción</small>
                        </div>
                    </div>

                    <!-- Campo de porciones (ahora segundo) -->
                    <div id="portions-container">
                        <div class="pending-field-group" data-field="portions">
                            <?= $form->field($model, 'portions', [
                                'template' => "<div class='row mb-3'>{label}<div class='col-sm-9'>{input}{error}</div></div>"
                            ])->textInput()->label(null, ['class' => 'col-sm-3 text-start required']) ?>
                        </div>
                    </div>
                    <div class="pending-field-group" data-field="lifetime">
                        <div class="row mb-3">
                            <label class="col-sm-3 text-start"><?= $model->getAttributeLabel('lifetime') ?></label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <?= Html::textInput(
                                        'lifetime_value',
                                        $model->lifetime ? preg_replace('/[^0-9]/', '', $model->lifetime) : '',
                                        [
                                            'id' => 'lifetime-value-input',
                                            'class' => 'form-control',
                                            'placeholder' => Yii::t('app', 'Duración'),
                                            'onkeypress' => 'return event.charCode >= 48 && event.charCode <= 57',
                                            'style' => 'max-width: 100px;'
                                        ]
                                    )
                                    ?>
                                    <?php
                                    // Determinar el valor por defecto para lifetime_unit
                                    $lifetimeUnitValue = '';
                                    if ($model->lifetime) {
                                        // Si hay datos, detectar la unidad
                                        if (strpos($model->lifetime, 'día') !== false) {
                                            $lifetimeUnitValue = 'días';
                                        } elseif (strpos($model->lifetime, 'hora') !== false) {
                                            $lifetimeUnitValue = 'horas';
                                        } elseif (strpos($model->lifetime, 'minuto') !== false) {
                                            $lifetimeUnitValue = 'minutos';
                                        }
                                    }
                                    ?>
                                    <?= Html::dropDownList(
                                        'lifetime_unit',
                                        $lifetimeUnitValue,
                                        [
                                            '' => Yii::t('app', 'Seleccionar...'),
                                            'minutos' => Yii::t('app', 'minutos'),
                                            'horas' => Yii::t('app', 'horas'),
                                            'días' => Yii::t('app', 'días')
                                        ],
                                        [
                                            'id' => 'lifetime-unit-select',
                                            'class' => 'form-select',
                                            'style' => 'max-width: 150px;'
                                        ]
                                    )
                                    ?>
                                    <?= $form->field($model, 'lifetime', ['template' => '{input}{error}'])->hiddenInput(['id' => 'lifetime-hidden'])->label(false) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($model->type == $model::STANDARD_RECIPE_TYPE_MAIN): ?>
                        <div class="pending-field-group" data-field="price">
                            <?= $form->field($model, 'price', [
                                    'template' => "<div class='row mb-3'>{label}<div class='col-sm-9'><div class='input-group'><span class='input-group-text'>$currencySymbol</span>{input}</div>{error}</div></div>"
                                ])->textInput([
                                    'id' => 'price-input',
                                    'value' => $model->price !== null && $model->price !== '' ? formatPrice($model->price) : '',
                                    'class' => 'form-control format-price-input',
                                    'data-raw-value' => $model->price
                                ])->label('Precio sin IVA', ['class' => 'col-sm-3 text-start']) ?>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3 text-start">
                                <?= Yii::t('app', "Cost") ?>
                            </div>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <span class="input-group-text"><?= $currencySymbol ?></span>
                                    <span class="form-control format-cost" id="cost-value"
                                        data-value="<?= $model->lastPrice ?>"><?= formatCost($model->lastPrice) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3 text-start">
                                <?= Yii::t('app', "Cost %") ?>
                            </div>
                            <div class="col-sm-9">
                                <span class="form-control format-percentage"
                                    id="cost-percent"
                                    data-value="<?= $model->costPercent ?? 0 ?>">
                                    <?= formatPercentage($model->costPercent*100) ?>
                                </span>
                                <small class="form-text text-muted">
                                    <?= Yii::t('app', "Porcentaje del costo en relación al precio de venta") ?>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="pending-field-group" data-field="observation">
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
            </div>
            <?= $this->render('create/_ingredients_selection', [
                'model' => $model
            ]) ?>
            <input type="hidden" name="pending_fields" id="pending-fields-input" value="">
            <br>
<?php
$pendingFields = isset($model) && method_exists($model, 'getPendingFields') ? $model->getPendingFields() : [];
$pendingFieldsJson = json_encode($pendingFields);
$js = <<<JS
(function($) {
    var pendingMode = false;
    var pendingFields = $pendingFieldsJson;

    function updatePendingCheckboxes() {
        $('.pending-field-group').each(function() {
            var field = $(this).data('field');
            var \$existing = \$(this).find('.pending-checkbox');

            if (pendingMode) {
                if (\$existing.length === 0) {
                    var checked = pendingFields && pendingFields.includes(field) ? 'checked' : '';
                    var \$label = \$(this).find('label').first();
                    var cbHtml = ' <input type="checkbox" class="form-check-input pending-checkbox" data-field="' + field + '" ' + checked + ' title="Marcar como pendiente">';
                    if (\$label.length) {
                        \$label.append(cbHtml);
                    } else {
                        $(this).prepend(cbHtml);
                    }
                }
            } else {
                \$existing.remove();
            }
        });
    }

    $('#btn-toggle-pending').on('click', function() {
        pendingMode = !pendingMode;
        $('.standard-recipe-form').toggleClass('pending-mode', pendingMode);
        updatePendingCheckboxes();
        // Disparar evento global para ingredientes
        document.dispatchEvent(new CustomEvent('pending-mode-toggle', { detail: { enabled: pendingMode } }));
    });

    $('.standard-recipe-form form').on('submit', function() {
        var fields = [];
        // Solo checkboxes de campos principales, NO de ingredientes
        $('.pending-checkbox:checked:not([data-ingredient-id])').each(function() {
            fields.push($(this).data('field'));
        });
        $('#pending-fields-input').val(JSON.stringify(fields));
    });

    if (pendingFields && pendingFields.length > 0) {
        $('#btn-toggle-pending').addClass('btn-danger').removeClass('btn-warning');
    }
})(jQuery);
JS;
$this->registerJs($js);

// Validación visual de yield_um y botón de carga al guardar
$this->registerJs(<<<JS
(function() {
    var select  = document.getElementById('standardrecipe-yield_um-visible');
    var errorEl = document.getElementById('yield-um-error');
    var saveBtn = document.getElementById('btn-save-recipe');
    var saveBtnOriginalHtml = saveBtn ? saveBtn.innerHTML : '';

    function validateYieldUm() {
        if (!select || !errorEl) return true;
        if (!select.value) {
            select.classList.add('is-invalid');
            errorEl.style.display = 'block';
            return false;
        } else {
            select.classList.remove('is-invalid');
            errorEl.style.display = 'none';
            return true;
        }
    }

    if (select) {
        select.addEventListener('change', validateYieldUm);
    }

    \$(document).on('beforeSubmit', '#form-recipe', function() {
        var yieldUmOk = validateYieldUm();
        if (!yieldUmOk) {
            return false;
        }
        // Validation passed — put button in loading state
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Guardando...';
        }
    });
})();
JS, \yii\web\View::POS_READY);

// --- JS de pasos de procedimiento (aquí para que pjax no lo duplique) ---
$confirmStepMsg = Yii::t('app', 'Are you sure you want to delete this step?');
$addBtnLabel = Yii::t('app', 'Add');
$editStepUrl = \yii\helpers\Url::to(['standard-recipe/edit-step']);
$this->registerJs(<<<JS
(function() {
    function cleanModalBackdrop() {
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.querySelectorAll('.modal-backdrop').forEach(function(el) { el.remove(); });
    }

    // --- Agregar paso con AJAX ---
    \$(document).on('submit', '#form_step', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        var \$form = \$(this);
        var \$btn = \$form.find('#btn-submit-step');
        var originalHtml = \$btn.html();
        \$btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span>');
        \$.ajax({
            url: \$form.attr('action'),
            type: 'POST',
            data: new FormData(\$form[0]),
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var container = \$form[0].getAttribute('data-pjax-container') || '#pjax-list-steps';
                    if (container === '#pjax-list-steps') window._stepJustAdded = true;
                    \$form[0].reset();
                    \$.pjax.reload({ container: container, timeout: 10000 });
                } else {
                    alert(response.message || 'Error al agregar el paso');
                    \$btn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function() {
                alert('Error al agregar el paso');
                \$btn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // --- Restaurar botón al cerrar modal ---
    \$(document).on('hidden.bs.modal', '#modal-add-step, #modal-add-special-step', function() {
        var \$btn = \$(this).find('#btn-submit-step');
        \$btn.prop('disabled', false).html('$addBtnLabel');
        var \$form = \$(this).find('#form_step');
        if (\$form.length) \$form[0].reset();
        cleanModalBackdrop();
    });

    // --- Eliminar paso con AJAX ---
    \$(document).on('click', '.btn-delete-step', function() {
        var url = \$(this).data('url');
        if (!confirm('$confirmStepMsg')) return;
        \$.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: { _csrf: yii.getCsrfToken() },
            success: function(response) {
                if (response.success) {
                    \$.pjax.reload({ container: '#pjax-list-steps', timeout: 10000 });
                } else {
                    alert(response.message || 'Error al eliminar el paso');
                }
            },
            error: function() { alert('Error al eliminar el paso'); }
        });
    });

    // --- Abrir collapse y scroll tras agregar paso ---
    \$(document).off('pjax:complete.steps').on('pjax:complete.steps', '#pjax-list-steps', function() {
        if (!window._stepJustAdded) return;
        window._stepJustAdded = false;
        var collapseEl = document.getElementById('collapseSteps');
        if (!collapseEl) return;
        collapseEl.style.transition = 'none';
        collapseEl.classList.remove('collapsing');
        collapseEl.classList.add('show');
        collapseEl.style.height = '';
        var toggleBtn = document.querySelector('[data-bs-target="#collapseSteps"]');
        if (toggleBtn) {
            toggleBtn.classList.remove('collapsed');
            toggleBtn.setAttribute('aria-expanded', 'true');
        }
        setTimeout(function() {
            collapseEl.style.transition = '';
            collapseEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 50);
    });

    \$(document).off('pjax:complete.special-steps').on('pjax:complete.special-steps', '#pjax-list-special-steps', function() {
        window._stepJustAdded = false;
        var collapseEl = document.getElementById('collapseSpecialSteps');
        if (!collapseEl) return;
        collapseEl.style.transition = 'none';
        collapseEl.classList.remove('collapsing');
        collapseEl.classList.add('show');
        collapseEl.style.height = '';
        var toggleBtn = document.querySelector('[data-bs-target="#collapseSpecialSteps"]');
        if (toggleBtn) {
            toggleBtn.classList.remove('collapsed');
            toggleBtn.setAttribute('aria-expanded', 'true');
        }
        setTimeout(function() {
            collapseEl.style.transition = '';
            collapseEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 50);
    });

    // --- Cerrar modal y limpiar backdrop tras pjax ---
    \$(document).on('pjax:end', '#pjax-list-steps, #pjax-list-special-steps', function() {
        ['modal-add-step', 'modal-add-special-step'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) { var inst = bootstrap.Modal.getInstance(el); if (inst) inst.hide(); }
        });
        cleanModalBackdrop();
    });

    // --- Modal imagen grande de paso ---
    \$(document).on('click', '.procedure-step-img-link', function(e) {
        e.preventDefault();
        document.getElementById('procedure-step-modal-img').src = \$(this).data('img');
        new bootstrap.Modal(document.getElementById('procedureStepImageModal')).show();
    });
    \$(document).on('hidden.bs.modal', '#procedureStepImageModal', function() { cleanModalBackdrop(); });

    // --- Modal editar paso ---
    \$(document).on('hidden.bs.modal', '#modal-edit-step', function() {
        cleanModalBackdrop();
        var c = document.getElementById('edit-step-image-preview-container');
        var p = document.getElementById('edit-step-image-preview');
        var r = document.getElementById('edit-step-remove-image');
        if (c) c.style.display = 'none';
        if (p) p.src = '';
        if (r) r.value = '0';
    });
    \$(document).on('click', '#save-edit-step', function() {
        var modal = bootstrap.Modal.getInstance(document.getElementById('modal-edit-step'));
        if (modal) modal.hide();
        setTimeout(cleanModalBackdrop, 500);
    });
    \$(document).on('click', '.edit-step', function() {
        var imgUrl = this.getAttribute('data-img');
        var previewContainer = document.getElementById('edit-step-image-preview-container');
        var previewImg = document.getElementById('edit-step-image-preview');
        var removeInput = document.getElementById('edit-step-remove-image');
        var removeBtn = document.getElementById('edit-step-remove-image-btn');
        if (removeInput.value !== '1') removeInput.value = '0';
        if (imgUrl && removeInput.value !== '1') {
            previewImg.src = imgUrl;
            previewContainer.style.display = 'block';
            if (removeBtn) removeBtn.style.display = 'flex';
        } else {
            previewImg.src = '';
            previewContainer.style.display = 'none';
            if (removeBtn) removeBtn.style.display = 'none';
        }
    });
    \$(document).on('click', '#edit-step-remove-image-btn', function(e) {
        e.preventDefault();
        document.getElementById('edit-step-image-preview-container').style.display = 'none';
        document.getElementById('edit-step-image-preview').src = '';
        document.getElementById('edit-step-remove-image').value = '1';
    });

    // --- Modal editar paso especial ---
    \$(document).on('click', '.edit-special-step', function() {
        var imgUrl           = this.getAttribute('data-img');
        var previewContainer = document.getElementById('edit-special-step-image-preview-container');
        var previewImg       = document.getElementById('edit-special-step-image-preview');
        var removeInput      = document.getElementById('edit-special-step-remove-image');
        var removeBtn        = document.getElementById('edit-special-step-remove-image-btn');
        var fileInput        = document.getElementById('edit-special-step-image');
        document.getElementById('edit-special-step-id').value        = this.getAttribute('data-id');
        document.getElementById('edit-special-step-activity').value  = this.getAttribute('data-activity');
        document.getElementById('edit-special-step-time').value      = this.getAttribute('data-time');
        document.getElementById('edit-special-step-indicator').value = this.getAttribute('data-indicator');
        if (removeInput) removeInput.value = '0';
        if (fileInput) fileInput.value = '';
        if (imgUrl) {
            if (previewImg) previewImg.src = imgUrl;
            if (previewContainer) previewContainer.style.display = 'block';
            if (removeBtn) removeBtn.style.display = 'flex';
        } else {
            if (previewImg) previewImg.src = '';
            if (previewContainer) previewContainer.style.display = 'none';
            if (removeBtn) removeBtn.style.display = 'none';
        }
    });
    \$(document).on('click', '#edit-special-step-remove-image-btn', function(e) {
        e.preventDefault();
        document.getElementById('edit-special-step-image-preview-container').style.display = 'none';
        document.getElementById('edit-special-step-image-preview').src = '';
        document.getElementById('edit-special-step-remove-image').value = '1';
    });
    \$(document).on('click', '#save-edit-special-step', function() {
        var modal = bootstrap.Modal.getInstance(document.getElementById('modal-edit-special-step'));
        if (modal) modal.hide();
        setTimeout(cleanModalBackdrop, 500);
    });
    \$(document).on('hidden.bs.modal', '#modal-edit-special-step', function() { cleanModalBackdrop(); });
})();
JS, \yii\web\View::POS_READY);
?>
            
            <!-- Sección colapsable: Imagen de la receta -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <button class="btn btn-link text-decoration-none w-100 text-start p-0 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMainImage" aria-expanded="false" aria-controls="collapseMainImage">
                        <i class="fas fa-image me-2"></i><?= Yii::t('app', 'Imagen de la receta') ?>
                        <i class="fas fa-chevron-down float-end mt-1"></i>
                    </button>
                </div>
                <div id="collapseMainImage" class="collapse">
                    <div class="card-body">
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
                                'showUpload' => false,
                                'browseLabel' => Yii::t('app', 'Seleccionar imagen (máx. 3MB)'),
                            ],
                        ]) ?>
                    </div>
                </div>
            </div>
            
            <!-- Sección colapsable: Procedimiento -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <button class="btn btn-link text-decoration-none w-100 text-start p-0 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSteps" aria-expanded="false" aria-controls="collapseSteps">
                        <i class="fas fa-list-ol me-2"></i><?= Yii::t('app', 'Procedimiento') ?>
                        <i class="fas fa-chevron-down float-end mt-1"></i>
                    </button>
                </div>
                <div id="collapseSteps" class="collapse">
                    <div class="card-body">
                        <?= $this->render('create/_steps', [
                            'model' => $model
                        ]) ?>
                    </div>
                </div>
            </div>

            <!-- Sección colapsable: Equipos y utensilios -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <button class="btn btn-link text-decoration-none w-100 text-start p-0 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEquipment" aria-expanded="false" aria-controls="collapseEquipment">
                        <i class="fas fa-utensils me-2"></i><?= Yii::t('app', 'Equipos y utensilios') ?>
                        <i class="fas fa-chevron-down float-end mt-1"></i>
                    </button>
                </div>
                <div id="collapseEquipment" class="collapse">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <button type="button" class="btn btn-sm btn-primary" id="add-equipment-btn">
                                <i class="fas fa-plus me-1"></i> <?= Yii::t('app', 'Añadir equipo') ?>
                            </button>
                        </div>
                        <div id="equipment-container" class="mb-3">
                            <!-- Los equipos se mostrarán aquí -->
                        </div>
                        <div class="alert alert-info" id="no-equipment-message">
                            <i class="fas fa-info-circle me-2"></i> <?= Yii::t('app', 'Añada los equipos y utensilios necesarios para la receta') ?>
                        </div>

                        <!-- Vista previa del texto completo -->
                        <div class="mt-4 border-top pt-3">
                            <h6><i class="fas fa-eye me-2"></i><?= Yii::t('app', 'Vista previa') ?></h6>
                            <div id="equipment-preview" class="p-3 bg-light rounded">
                                <!-- La vista previa se mostrará aquí -->
                            </div>
                        </div>

                        <!-- Campo oculto para almacenar los equipos -->
                        <?= $form->field($model, 'equipment')->hiddenInput(['id' => 'equipment-hidden'])->label(false) ?>
                    </div>
                </div>
            </div>

            <!-- Modal para añadir/editar equipo -->
            <div class="modal fade" id="equipment-modal" tabindex="-1" aria-labelledby="equipmentModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="equipmentModalLabel"><?= Yii::t('app', 'Añadir equipo') ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="equipment-name" class="form-label"><?= Yii::t('app', 'Equipo o utensilio') ?></label>
                                <input type="text" class="form-control" id="equipment-name" name="equipment-name" placeholder="<?= Yii::t('app', 'Ej: Tabla de cortar, Cuchillo, Bowl') ?>">
                            </div>
                            <div class="mb-3">
                                <label for="equipment-section" class="form-label"><?= Yii::t('app', 'Sección (opcional)') ?></label>
                                <input type="text" class="form-control" id="equipment-section" name="equipment-section" placeholder="<?= Yii::t('app', 'Agrupa los equipos por áreas de uso (ej. Cocción, Emplatado)') ?>">
                                <small class="form-text text-muted"><?= Yii::t('app', 'Agrupe los equipos por sección o deje en blanco') ?></small>
                            </div>
                            <div class="mb-3">
                                <label for="equipment-description" class="form-label"><?= Yii::t('app', 'Descripción (opcional)') ?></label>
                                <textarea class="form-control" id="equipment-description" rows="2" placeholder="<?= Yii::t('app', 'Ej: Para picar la cebolla y el ajo') ?>"></textarea>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="equipment-essential" name="equipment-essential">
                                <label class="form-check-label" for="equipment-essential"><?= Yii::t('app', 'Equipo esencial') ?></label>
                                <small class="d-block text-muted"><?= Yii::t('app', 'Marca esta opción si sin este equipo no se podría preparar la receta.') ?></small>
                            </div>
                            <input type="hidden" id="equipment-index" name="equipment-index" value="-1">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= Yii::t('app', 'Cancelar') ?></button>
                            <button type="button" class="btn btn-success" id="save-equipment"><?= Yii::t('app', 'Guardar equipo') ?></button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección colapsable: Cuidados y medidas especiales -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <button class="btn btn-link text-decoration-none w-100 text-start p-0 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpecialSteps" aria-expanded="false" aria-controls="collapseSpecialSteps">
                        <i class="fas fa-exclamation-triangle me-2"></i><?= Yii::t('app', 'Cuidados y medidas especiales') ?>
                        <i class="fas fa-chevron-down float-end mt-1"></i>
                    </button>
                </div>
                <div id="collapseSpecialSteps" class="collapse">
                    <div class="card-body">
                        <?= $this->render('create/_special_steps', [
                            'model' => $model
                        ]) ?>
                    </div>
                </div>
            </div>
            
            <!-- Sección colapsable: Otras especificaciones -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <button class="btn btn-link text-decoration-none w-100 text-start p-0 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOtherSpecs" aria-expanded="false" aria-controls="collapseOtherSpecs">
                        <i class="fas fa-clipboard-list me-2"></i><?= Yii::t('app', 'Otras especificaciones') ?>
                        <i class="fas fa-chevron-down float-end mt-1"></i>
                    </button>
                </div>
                <div id="collapseOtherSpecs" class="collapse">
                    <div class="card-body">
                        <?= $form->field($model, 'other_specs')->widget(Summernote::class, [
                            'useKrajeePresets' => true,
                            'useKrajeeStyle' => false,
                            'pluginOptions' => [
                                'height' => 200
                            ]
                        ]) ?>
                    </div>
                </div>
            </div>
            
            <!-- Sección colapsable: Alérgenos -->
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <button class="btn btn-link text-decoration-none w-100 text-start p-0 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAllergies" aria-expanded="false" aria-controls="collapseAllergies">
                        <i class="fas fa-allergies me-2"></i><?= Yii::t('app', 'Alérgenos') ?>
                        <i class="fas fa-chevron-down float-end mt-1"></i>
                    </button>
                </div>
                <div id="collapseAllergies" class="collapse">
                    <div class="card-body">
                        <?= $this->render('create/allergies', ['model' => $model, 'form' => $form]) ?>
                    </div>
                </div>
            </div>


            <br>
            <?php /*
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
            */ ?>

        </div>
        <div class="card-footer">
            <div class="form-group">
                <?= \yii\bootstrap5\Html::submitButton(Yii::t('app', "Save"), ['class' => 'btn btn-success', 'id' => 'btn-save-recipe']) ?>
                <?= \yii\bootstrap5\Html::a(Yii::t('app', "Cancel"), [Yii::$app->request->get('type') == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB ? 'sub-standard-recipe/index' : 'standard-recipe/index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>


    <?php ActiveForm::end(); ?>

</div>

<?php

\yii\bootstrap5\Modal::begin([
    'title' => Yii::t('app', 'Add ingredient or sub-recipe'),
    'id' => 'modal-add-ingredient'
]);

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
\yii\bootstrap5\Modal::begin([
    'title' => Yii::t('app', 'Add step'),
    'id' => 'modal-add-step',
]);

echo $this->render('create/_form_steps', ['recipe' => $model, 'model' => new \common\models\RecipeStep(['type' => \common\models\RecipeStep::STEP_TYPE_PROCEDURE]), 'pjaxId' => '#pjax-list-steps']);

\yii\bootstrap5\Modal::end();
?>

<?php
\yii\bootstrap5\Modal::begin([
    'title' => Yii::t('app', 'Add special step'),
    'id' => 'modal-add-special-step',
]);

echo $this->render('create/_form_steps', ['recipe' => $model, 'model' => new \common\models\RecipeStep(['type' => \common\models\RecipeStep::STEP_TYPE_SPECIAL]), 'pjaxId' => '#pjax-list-special-steps']);

\yii\bootstrap5\Modal::end();
?>

<script>
    document.getElementById('title-input').addEventListener('blur', function(e) {
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
    document.addEventListener('DOMContentLoaded', function() {
        // Obtener referencia al formulario
        const recipeForm = document.getElementById('form-recipe');

        if (recipeForm) {
            recipeForm.addEventListener('submit', function(e) {
                // Obtener referencia al campo de precio
                const priceInput = document.getElementById('price-input');

                if (priceInput) {
                    // Usar el valor raw guardado en el atributo data-raw-value
                    const rawValue = priceInput.getAttribute('data-raw-value');
                    if (rawValue) {
                        // Usar el valor numérico puro para el envío
                        priceInput.value = rawValue;
                    }
                }
            });
        }
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
    // Control dinámico de porciones según unidad final (um)
    document.addEventListener('DOMContentLoaded', function() {
        const umField = document.getElementById('standardrecipe-um');
        const yieldField = document.getElementById('standardrecipe-yield');
        const yieldUmField = document.getElementById('standardrecipe-yield_um-visible');
        const portionsField = document.getElementById('standardrecipe-portions');
        const portionsContainer = document.getElementById('portions-container');
        const portionSizeContainer = document.getElementById('portion-size-container');
        const portionSizeField = document.getElementById('portion-size');
        const portionSizeUnitSpan = document.querySelector('.portion-size-unit');

        // Lista de unidades que requieren campo de tamaño de porción
        const unidadesConTamañoPorción = [
            'pieza', 'piezas', 'porción', 'porciones', 'portion', 'rebanada', 'rebanadas',
            'taza', 'tazas', 'cucharada', 'cucharadas', 'cucharadita', 'cucharaditas',
            'botella', 'botellas', 'lata', 'latas', 'bote', 'botes'
        ];

        // Mapa para normalizar las unidades de entrada del usuario
        const unitNormalizer = {
            // Kilogramo y variantes
            'kilogramo': ['kilogramo', 'kilogramos', 'kg', 'kilo', 'kilos', 'kgs'],
            // Volumen
            'litro': ['litro', 'litros', 'l', 'lt', 'lts'],
            // Porciones y unidades
            'pieza': ['pieza', 'piezas', 'pza', 'pzas'],
            'paquete': ['paquete', 'paquetes', 'paq', 'paqs'],
            'rebanada': ['rebanada', 'rebanadas', 'slice', 'slices', 'corte', 'cortes'],
            'porción': ['porción', 'porciones', 'porcion', 'porciones', 'ración', 'raciones', 'racion'],
            'onza': ['onza', 'onzas', 'oz'],
            'libra': ['libra', 'libras', 'lb', 'lbs'],
            // Medidas de cocina
            'taza': ['taza', 'tazas', 'cup', 'cups'],
            'cucharadita': ['cucharadita', 'cucharaditas', 'cdta', 'cdtas', 'tsp', 'tsps'],
            'cucharada': ['cucharada', 'cucharadas', 'cda', 'cdas', 'tbsp', 'tbsps', 'cucharada sopera'],
            'pizca': ['pizca', 'pizcas', 'pinch'],
            // Otros
            'botella': ['botella', 'botellas', 'bt', 'bts'],
            'gota': ['gota', 'gotas', 'drop', 'drops'],
            'lata': ['lata', 'latas', 'can', 'cans'],
            'bote': ['bote', 'botes', 'jar', 'jars']
        };

        // Función para normalizar unidades
        function normalizeUnit(unitText) {
            if (!unitText) return null;

            const normalized = unitText.toLowerCase().trim()
                .normalize("NFD").replace(/[\u0300-\u036f]/g, ""); // Eliminar acentos

            for (const [baseUnit, variants] of Object.entries(unitNormalizer)) {
                if (variants.some(variant => normalized === variant || normalized.includes(variant))) {
                    return baseUnit;
                }
            }

            return null; // Unidad no reconocida
        }

        // Función para comprobar si una unidad requiere tamaño de porción
        function requiresPortionSize(unitText) {
            if (!unitText) return false;

            // Normalizar el texto (quitar acentos, convertir a minúsculas)
            const normalized = unitText.toLowerCase().trim()
                .normalize("NFD").replace(/[\u0300-\u036f]/g, "");

            // Lista explícita de unidades que deben mostrar el tamaño de porción
            const unidades = [
                'pieza', 'piezas',
                'porcion', 'porciones', 'porción',
                'ración', 'raciones', 'racion',
                'portion', 'portions',
                'rebanada', 'rebanadas',
                'taza', 'tazas',
                'cucharada', 'cucharadas',
                'cucharadita', 'cucharaditas',
                'botella', 'botellas',
                'lata', 'latas',
                'bote', 'botes'
            ];

            // Verificar si alguna unidad coincide exactamente o está contenida
            return unidades.some(unidad =>
                normalized === unidad || normalized.includes(unidad));
        }

        // Función para obtener el texto adecuado según la unidad
        function getPortionSizeLabel(unitText) {
            if (!unitText) return "Tamaño de porción";

            // Normalizar el texto de la unidad
            const normalized = unitText.toLowerCase().trim()
                .normalize("NFD").replace(/[\u0300-\u036f]/g, "");

            // Configurar etiquetas específicas según la unidad
            if (normalized.includes('porcion') || normalized.includes('portion') ||
                normalized.includes('racion')) {
                return "Tamaño de cada porción";
            } else if (normalized.includes('rebanada') || normalized.includes('slice')) {
                return "Grosor de cada rebanada";
            } else if (normalized.includes('taza')) {
                return "Volumen de cada taza";
            } else if (normalized.includes('cucharada') || normalized.includes('cucharadita')) {
                return "Volumen de cada cucharada";
            } else if (normalized.includes('botella')) {
                return "Capacidad de cada botella";
            } else if (normalized.includes('lata') || normalized.includes('bote')) {
                return "Contenido de cada envase";
            } else if (normalized.includes('pieza')) {
                // Check if the yield unit is related to volume (liters)
                const yieldUnitNormalized = yieldUmField.value.trim() ? yieldUmField.value.trim().toLowerCase().trim()
                    .normalize("NFD").replace(/[\u0300-\u036f]/g, "") : "";
                
                if (yieldUnitNormalized.includes('litro') || 
                    yieldUnitNormalized === 'lt' || 
                    yieldUnitNormalized === 'l' ||
                    yieldUnitNormalized === 'ml' ||
                    yieldUnitNormalized === 'dl' ||
                    yieldUnitNormalized === 'cl') {
                    return "Volumen de cada pieza";
                }
                return "Peso de cada pieza";
            }

            // Valor predeterminado
            return "Tamaño de porción";
        }

        function updatePortionsField() {
            if (!umField || !yieldUmField || !portionsField || !yieldField) return;

            // Obtener valor y unidad de medida final
            const finalUnitRaw = umField.value.trim();
            const yieldUnitRaw = yieldUmField.value.trim();
            const yieldValue = parseFloat(yieldField.value) || 0;            // Resetear estado de los campos
            portionsField.removeAttribute('readonly');
            portionsField.classList.remove('bg-light');

            // Solo ocultar el campo de porciones si no hay valor y no se requiere tamaño de porción
            if (!portionsField.value && !portionSizeField?.value) {
                portionsContainer.classList.add('d-none');
            } else {
                portionsContainer.classList.remove('d-none');
            }

            // Ocultar campo de tamaño de porción por defecto
            if (portionSizeContainer) {
                portionSizeContainer.classList.add('d-none');
            }
            
            const isPiezaCase = (finalUnitRaw.includes('Pieza') && yieldUnitRaw.includes('Pieza'));
            // CASO ESPECIAL: Si las unidades de rendimiento y final son iguales
            if (finalUnitRaw.toLowerCase() === yieldUnitRaw.toLowerCase()  && !isPiezaCase) {                // Solo fijar el valor a 1 si no hay un valor existente
                if (!portionsField.value) {
                    portionsField.value = '1';
                }
                portionsField.setAttribute('readonly', 'readonly');
                portionsField.classList.add('bg-light');
                
                // Mostrar mensaje explicativo
                const formHelp = getOrCreateFormHelp();
                formHelp.className = 'form-text text-info mt-1';
                formHelp.textContent = `Las porciones se establecen automáticamente a 1 porque la unidad de rendimiento y la unidad ${<?= $model->type == \common\models\StandardRecipe::STANDARD_RECIPE_TYPE_SUB ? 
                    "'de medida'" : "'final'" ?>} son las mismas (${finalUnitRaw})` + '<?= "" ?>';
                
                // Ocultar el campo de tamaño de porción, ya que no aplica en este caso
                if (portionSizeContainer) {
                    portionSizeContainer.classList.add('d-none');
                }
                
                // Mostrar el campo de porciones siempre que tenga un valor
                if (portionsField.value) {
                    portionsContainer.classList.remove('d-none');
                }
                
                return; // Terminar la función aquí, no necesitamos más lógica
            }


            // Verificar si la unidad final requiere tamaño de porción
            if (requiresPortionSize(finalUnitRaw)  || isPiezaCase) {
                // Mostrar campo de tamaño de porción
                if (portionSizeContainer) {
                    // Personalizar el texto del label según la unidad
                    const portionSizeLabel = portionSizeContainer.querySelector('label');
                    if (portionSizeLabel) {
                        portionSizeLabel.textContent = getPortionSizeLabel(finalUnitRaw);
                    }

                    portionSizeContainer.classList.remove('d-none');

                    // Actualizar unidad en el campo de tamaño
                    if (portionSizeUnitSpan) {
                        portionSizeUnitSpan.textContent = yieldUnitRaw;
                    }
                    // Personalizar el texto de ayuda según la unidad
                    const helpText = portionSizeContainer.querySelector('.form-text.text-muted');
                    if (helpText) {
                        const normalizedUnit = finalUnitRaw.toLowerCase().trim()
                            .normalize("NFD").replace(/[\u0300-\u036f]/g, "");

                        if (normalizedUnit.includes('porcion') || normalizedUnit.includes('portion')) {
                            helpText.textContent = `Define cuántos ${yieldUmField.value} debe contener cada porción`;
                        } else if (normalizedUnit.includes('rebanada')) {
                            helpText.textContent = `Define el grosor o peso de cada rebanada en ${yieldUmField.value}`;
                        } else if (normalizedUnit.includes('taza')) {
                            helpText.textContent = `Define el volumen exacto de una taza en ${yieldUmField.value}`;
                        } else if (normalizedUnit.includes('cucharada')) {
                            helpText.textContent = `Define el volumen exacto de una cucharada en ${yieldUmField.value}`;
                        } else if (normalizedUnit.includes('cucharadita')) {
                            helpText.textContent = `Define el volumen exacto de una cucharadita en ${yieldUmField.value}`;
                        } else if (normalizedUnit.includes('botella')) {
                            helpText.textContent = `Define la capacidad de cada botella en ${yieldUmField.value}`;
                        } else if (normalizedUnit.includes('lata') || normalizedUnit.includes('bote')) {
                            helpText.textContent = `Define el contenido de cada ${finalUnitRaw} en ${yieldUmField.value}`;
                        } else if (normalizedUnit.includes('pieza')) {
                            // Check if yield unit is a volume unit
                            const yieldUnitNorm = yieldUmField.value.toLowerCase().trim()
                                .normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                            if (yieldUnitNorm.includes('litro') || 
                                yieldUnitNorm === 'lt' || 
                                yieldUnitNorm === 'l' ||
                                yieldUnitNorm === 'ml' ||
                                yieldUnitNorm === 'dl' ||
                                yieldUnitNorm === 'cl') {
                                helpText.textContent = `Define cuántos ${yieldUmField.value} contiene cada pieza`;
                            } else {
                                helpText.textContent = `Define el peso de cada pieza en ${yieldUmField.value}`;
                            }
                        } else {
                            helpText.textContent = `Define cuántos ${yieldUmField.value} corresponden a cada ${finalUnitRaw}`;
                        }
                    }            // Si hay valor en el campo de porciones o en el tamaño de porción, mostrar el campo de porciones
                    if (portionsField.value || portionSizeField.value) {
                        portionsContainer.classList.remove('d-none');
                    } else {
                        portionsContainer.classList.add('d-none');
                    }

                    // Calcular porciones cuando cambie el tamaño de porción
                    portionSizeField.addEventListener('input', function() {
                        // Mostrar el campo de porciones cuando se ingrese un valor en tamaño de porción
                        if (portionSizeField.value || portionsField.value) {
                            portionsContainer.classList.remove('d-none');
                        } else {
                            portionsContainer.classList.add('d-none');
                        }
                        calculatePortions();
                    });

                    // Calcular porciones inicialmente si hay valor
                    if (portionSizeField.value) {
                        calculatePortions();
                        portionsContainer.classList.remove('d-none');
                    }
                }
            } else {
                // Si no requiere tamaño de porción, quitar readonly del campo porciones
                portionsField.removeAttribute('readonly');
                removeFormHelp();
            }
        }
        // Función para calcular el número de porciones basado en rendimiento y tamaño
        function calculatePortions() {
            const yieldValue = parseFloat(yieldField.value) || 0;
            const portionSize = parseFloat(portionSizeField.value) || 0;

            if (yieldValue > 0 && portionSize > 0) {
                // Calcular número de porciones
                const portions = Math.round((yieldValue / portionSize) * 100) / 100;

                // Actualizar campo de porciones
                portionsField.value = portions;
                portionsField.setAttribute('readonly', 'readonly');
                portionsField.classList.add('bg-light');

                // Mostrar mensaje explicativo
                const formHelp = getOrCreateFormHelp();
                formHelp.className = 'form-text text-info mt-1';
                formHelp.textContent = `Cálculo automático: ${yieldValue} ${yieldUmField.value} ÷ ${portionSize} ${yieldUmField.value} por porción = ${portions} porciones`;
            } else if (portionSizeField.value) {
                // Si hay valor pero es inválido
                const formHelp = getOrCreateFormHelp();
                formHelp.className = 'form-text text-warning mt-1';
                formHelp.textContent = 'Ingresa valores numéricos válidos para calcular el número de porciones';
                portionsField.removeAttribute('readonly');
            } else {
                // Si no hay valor de tamaño de porción, quitar readonly
                portionsField.removeAttribute('readonly');
                removeFormHelp();
            }
        }

        // Función auxiliar para obtener o crear el elemento de ayuda
        function getOrCreateFormHelp() {
            let helpElement = portionsContainer.querySelector('.form-text');
            if (!helpElement) {
                helpElement = document.createElement('small');
                helpElement.className = 'form-text mt-1';
                portionsField.parentNode.appendChild(helpElement);
            }
            return helpElement;
        }

        // Función para eliminar mensaje de ayuda
        function removeFormHelp() {
            const helpElement = portionsContainer.querySelector('.form-text');
            if (helpElement) {
                helpElement.remove();
            }
        }

        // Registrar eventos
        if (umField) {
            umField.addEventListener('change', updatePortionsField);
        }

        if (yieldUmField) {
            yieldUmField.addEventListener('change', updatePortionsField);
        }

        if (yieldField) {
            yieldField.addEventListener('input', function() {
                // Solo recalcular si el tamaño de porción está visible
                if (!portionSizeContainer.classList.contains('d-none') && portionSizeField.value) {
                    calculatePortions();
                }
            });
        }

        // Ejecutar la función al cargar la página
        updatePortionsField();
    });
    document.addEventListener('DOMContentLoaded', function() {
        // Referencias a elementos del DOM
        const equipmentContainer = document.getElementById('equipment-container');
        const equipmentHiddenField = document.getElementById('equipment-hidden');
        const equipmentPreview = document.getElementById('equipment-preview');
        const noEquipmentMessage = document.getElementById('no-equipment-message');
        const addEquipmentBtn = document.getElementById('add-equipment-btn');
        const equipmentModal = new bootstrap.Modal(document.getElementById('equipment-modal'));

        // Campos del modal
        const equipmentSection = document.getElementById('equipment-section');
        const equipmentName = document.getElementById('equipment-name');
        const equipmentDescription = document.getElementById('equipment-description');
        const equipmentEssential = document.getElementById('equipment-essential');
        const equipmentIndex = document.getElementById('equipment-index');
        const saveEquipmentBtn = document.getElementById('save-equipment');
        const equipmentModalLabel = document.getElementById('equipmentModalLabel');

        // Lista de equipos
        let equipmentList = [];

        // Intentar cargar equipos existentes
        try {
            const existingEquipment = equipmentHiddenField.value;
            if (existingEquipment) {
                // Intentar parsear datos existentes
                if (existingEquipment.startsWith('[') && existingEquipment.endsWith(']')) {
                    // Es un formato JSON
                    equipmentList = JSON.parse(existingEquipment);
                } else {
                    // Es un formato de texto, convertirlo a estructura
                    const lines = existingEquipment.split('\n');
                    let currentSection = '';

                    lines.forEach(line => {
                        line = line.trim();
                        if (!line) return;

                        // Detectar si es un título de sección (en mayúsculas o con "Para" al inicio)
                        if (line === line.toUpperCase() || line.startsWith('Para')) {
                            currentSection = line;
                        }
                        // Detectar si es un equipo con descripción (formato: "Equipo – Descripción")
                        else if (line.includes('–') || line.includes('-')) {
                            const parts = line.split(/[-–]/);
                            if (parts.length >= 2) {
                                equipmentList.push({
                                    section: currentSection,
                                    name: parts[0].trim(),
                                    description: parts.slice(1).join('-').trim(),
                                    essential: false
                                });
                            }
                        }
                        // Si es solo un nombre de equipo
                        else {
                            equipmentList.push({
                                section: currentSection,
                                name: line,
                                description: '',
                                essential: false
                            });
                        }
                    });
                }
            }
        } catch (e) {
            console.error('Error al parsear equipos existentes:', e);
            equipmentList = [];
        }

        // Renderizar equipos iniciales
        renderEquipment();

        // Evento para añadir nuevo equipo
        addEquipmentBtn.addEventListener('click', function() {
            clearModal();
            equipmentModalLabel.textContent = '<?= Yii::t('app', 'Añadir equipo') ?>';
            equipmentModal.show();
        });

        // Evento para guardar equipo
        saveEquipmentBtn.addEventListener('click', function() {
            const index = parseInt(equipmentIndex.value);
            const equipment = {
                section: equipmentSection.value.trim(),
                name: equipmentName.value.trim(),
                description: equipmentDescription.value.trim(),
                essential: equipmentEssential.checked
            };

            // Validaciones
            if (!equipment.name) {
                alert('<?= Yii::t('app', 'Por favor, introduce el nombre del equipo.') ?>');
                return;
            }

            if (index === -1) {
                // Nuevo equipo - si no tiene sección, usar la última sección usada
                if (!equipment.section && equipmentList.length > 0) {
                    const lastEquipment = equipmentList[equipmentList.length - 1];
                    equipment.section = lastEquipment.section || '';
                }
                equipmentList.push(equipment);
            } else {
                // Editar equipo existente
                equipmentList[index] = equipment;
            }

            renderEquipment();
            equipmentModal.hide();
        });

        // Función para renderizar los equipos
        function renderEquipment() {
            equipmentContainer.innerHTML = '';

            // Mostrar/ocultar mensaje de no hay equipos
            if (equipmentList.length === 0) {
                noEquipmentMessage.style.display = 'block';
                equipmentPreview.innerHTML = '<em><?= Yii::t('app', 'No se han añadido equipos a la receta.') ?></em>';
                return;
            }

            noEquipmentMessage.style.display = 'none';

            // Pre-procesamiento para manejar equipos sin sección
            // Asignar secciones basadas en el equipo anterior
            let lastSection = '';
            for (let i = 0; i < equipmentList.length; i++) {
                if (!equipmentList[i].section) {
                    equipmentList[i].section = lastSection;
                } else {
                    lastSection = equipmentList[i].section;
                }
            }

            // Agrupar equipos por sección
            const groupedEquipment = {};
            equipmentList.forEach((equip, index) => {
                const section = equip.section || '';
                if (!groupedEquipment[section]) {
                    groupedEquipment[section] = [];
                }
                groupedEquipment[section].push({
                    ...equip,
                    index
                });
            });

            // Generar tarjetas por sección
            Object.entries(groupedEquipment).forEach(([section, equipments]) => {
                const sectionDiv = document.createElement('div');
                sectionDiv.className = 'mb-4';

                // Mostrar encabezado solo si hay una sección definida
                if (section) {
                    sectionDiv.innerHTML = `<h6 class="mb-3">${section}</h6>`;
                } else {
                    sectionDiv.innerHTML = `<h6 class="mb-3"><?= Yii::t('app', 'Otros utensilios') ?></h6>`;
                }

                // Lista de equipos en esta sección
                const equipmentsList = document.createElement('div');
                equipmentsList.className = 'ms-2';

                equipments.forEach(equip => {
                    const equipItem = document.createElement('div');
                    equipItem.className = 'card mb-2' + (equip.essential ? ' border-primary' : '');

                    // Construir contenido del equipo
                    equipItem.innerHTML = `
                    <div class="card-body p-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold">
                                    ${equip.essential ? '<i class="fas fa-star text-primary me-1" title="Esencial"></i>' : ''}
                                    ${equip.name}
                                </span>
                                ${equip.description ? `<span class="text-muted ms-2">– ${equip.description}</span>` : ''}
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-secondary edit-equipment-btn" data-index="${equip.index}">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-equipment-btn" data-index="${equip.index}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                    equipmentsList.appendChild(equipItem);
                });

                sectionDiv.appendChild(equipmentsList);
                equipmentContainer.appendChild(sectionDiv);
            });

            // Configurar eventos para los botones
            document.querySelectorAll('.edit-equipment-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    editEquipment(index);
                });
            });

            document.querySelectorAll('.delete-equipment-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const index = parseInt(this.getAttribute('data-index'));
                    if (confirm('<?= Yii::t('app', '¿Estás seguro de eliminar este equipo?') ?>')) {
                        equipmentList.splice(index, 1);
                        renderEquipment();
                    }
                });
            });

            // Generar vista previa del texto completo
            let previewHtml = '<h5 class="mb-3"><?= Yii::t('app', 'UTENSILIOS Y EQUIPO DE COCINA NECESARIOS') ?></h5>';

            Object.entries(groupedEquipment).forEach(([section, equipments]) => {
                if (section) {
                    previewHtml += `<p class="fw-bold mb-2">${section}:</p>`;
                } else {
                    previewHtml += `<p class="fw-bold mb-2"><?= Yii::t('app', 'Otros utensilios') ?>:</p>`;
                }

                previewHtml += '<ul class="mb-3">';
                equipments.forEach(equip => {
                    let itemHtml = `<li>${equip.name}`;

                    if (equip.description) {
                        itemHtml += ` – ${equip.description}`;
                    }

                    if (equip.essential) {
                        itemHtml = itemHtml.replace('<li>', '<li class="fw-bold text-primary">');
                    }

                    itemHtml += '</li>';
                    previewHtml += itemHtml;
                });
                previewHtml += '</ul>';
            });

            equipmentPreview.innerHTML = previewHtml;

            // Actualizar el campo oculto para el formulario
            updateHiddenField();
        }

        // Función para editar un equipo
        function editEquipment(index) {
            const equipment = equipmentList[index];

            equipmentSection.value = equipment.section || '';
            equipmentName.value = equipment.name || '';
            equipmentDescription.value = equipment.description || '';
            equipmentEssential.checked = equipment.essential || false;
            equipmentIndex.value = index;

            equipmentModalLabel.textContent = '<?= Yii::t('app', 'Editar equipo') ?>';
            equipmentModal.show();
        }

        // Función para limpiar el modal y sugerir la última sección
        function clearModal() {
            // Sugerir la última sección utilizada si existe algún equipo
            if (equipmentList.length > 0) {
                const lastEquipment = equipmentList[equipmentList.length - 1];
                equipmentSection.value = lastEquipment.section || '';
            } else {
                equipmentSection.value = '';
            }

            equipmentName.value = '';
            equipmentDescription.value = '';
            equipmentEssential.checked = false;
            equipmentIndex.value = -1;
        }

        // Función para actualizar el campo oculto
        function updateHiddenField() {
            // Guardar como JSON estructurado
            equipmentHiddenField.value = JSON.stringify(equipmentList);
        }

        // Asegurar que el formulario envía el valor combinado
        document.getElementById('form-recipe').addEventListener('submit', function() {
            updateHiddenField();
        });
    });
    // Asegurar que el formulario se envíe correctamente
    (function() {
        const formRecipe = document.getElementById('form-recipe');

        if (formRecipe) {
            // Remover todos los listeners de submit existentes
            const clonedForm = formRecipe.cloneNode(true);
            formRecipe.parentNode.replaceChild(clonedForm, formRecipe);

            // Añadir un nuevo listener limpio
            clonedForm.addEventListener('submit', function(e) {
                // Manejar precio
                const priceInput = document.getElementById('price-input');
                if (priceInput && priceInput.getAttribute('data-raw-value')) {
                    priceInput.value = priceInput.getAttribute('data-raw-value');
                }

                // Manejar equipment
                const equipmentHiddenField = document.getElementById('equipment-hidden');
                if (window.equipmentList && equipmentHiddenField) {
                    equipmentHiddenField.value = JSON.stringify(window.equipmentList || []);
                }

                // Manejar tiempo y duración
                updateTimeFields();

                // Continuar con el envío normal
                return true;
            });

            // Helper para actualizar campos de tiempo
            function updateTimeFields() {
                // Tiempo de preparación
                const timeValueInput = document.getElementById('time-value-input');
                const timeUnitSelect = document.getElementById('time-unit-select');
                const timeOfPreparationHidden = document.getElementById('time-of-preparation-hidden');

                if (timeValueInput && timeUnitSelect && timeOfPreparationHidden) {
                    const value = timeValueInput.value.trim();
                    const unit = timeUnitSelect.value;

                    if (value) {
                        timeOfPreparationHidden.value = value + ' ' + unit;
                    }
                }

                // Lifetime
                const lifetimeValueInput = document.getElementById('lifetime-value-input');
                const lifetimeUnitSelect = document.getElementById('lifetime-unit-select');
                const lifetimeHidden = document.getElementById('lifetime-hidden');

                if (lifetimeValueInput && lifetimeUnitSelect && lifetimeHidden) {
                    const value = lifetimeValueInput.value.trim();
                    const unit = lifetimeUnitSelect.value;

                    if (value) {
                        lifetimeHidden.value = value + ' ' + unit;
                    }
                }
            }
        }
    })();
</script>