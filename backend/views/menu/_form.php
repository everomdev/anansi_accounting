<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Menu */
/* @var $form yii\widgets\ActiveForm */
$availableRecipes = \common\models\StandardRecipe::findAll(['business_id' => $model->business_id, 'in_construction' => 0]);

$this->registerJsFile(Yii::getAlias("@web/js/menu/form-new.js"), [
    'position' => $this::POS_END,
    'depends' => [\yii\web\YiiAsset::class]
]);

$business = \backend\helpers\RedisKeys::getBusiness();
?>

<div class="menu-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-menu',
        'enableAjaxValidation' => true
    ]); ?>

    <div class="card">
        <div class="card-body">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'total_price')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'category_id')->dropDownList(
                    \yii\helpers\ArrayHelper::map($business->recipeCategoriesMain, 'id', 'name'),
            ) ?>
            
            <!-- Nueva sección para selección de recetas con duplicados -->
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label"><?= Yii::t('app', 'Add Recipe') ?></label>
                    <div class="input-group">
                        <?= Html::dropDownList('recipe-selector', '', 
                            \yii\helpers\ArrayHelper::map($availableRecipes, 'id', 'title'),
                            [
                                'class' => 'form-select',
                                'id' => 'recipe-selector',
                                'prompt' => Yii::t('app', 'Select a recipe...')
                            ]
                        ) ?>
                        <button type="button" class="btn btn-primary" id="add-recipe-btn">
                            <i class="fas fa-plus"></i> <?= Yii::t('app', 'Add') ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Tabla de recetas seleccionadas -->
            <div class="mt-4">
                <label class="form-label"><?= Yii::t('app', 'Selected Recipes') ?></label>
                <div class="table-responsive">
                    <table class="table table-striped" id="selected-recipes-table">
                        <thead>
                            <tr>
                                <th><?= Yii::t('app', 'Recipe') ?></th>
                                <th width="100"><?= Yii::t('app', 'Action') ?></th>
                            </tr>
                        </thead>                        <tbody id="selected-recipes-body">
                            <?php if (!$model->isNewRecord): ?>
                                <?php 
                                $menuRecipes = $model->getMenuStandardRecipes();
                                if (!empty($menuRecipes)): 
                                ?>
                                    <?php foreach ($menuRecipes as $index => $menuRecipe): ?>
                                        <tr data-recipe-id="<?= $menuRecipe->standard_recipe_id ?>">
                                            <td><?= Html::encode($menuRecipe->standardRecipe->title) ?></td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm remove-recipe-btn">
                                                    <i class="fas fa-trash"></i> <?= Yii::t('app', 'Remove') ?>
                                                </button>
                                            </td>
                                        </tr>
                                        <?= Html::hiddenInput('Menu[_recipes][]', $menuRecipe->standard_recipe_id, ['class' => 'recipe-input']) ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>                <div id="no-recipes-message" class="text-muted text-center py-3" <?php 
                    $hasRecipes = false;
                    if (!$model->isNewRecord) {
                        $menuRecipes = $model->getMenuStandardRecipes();
                        $hasRecipes = !empty($menuRecipes);
                    }
                    echo $hasRecipes ? 'style="display:none;"' : '';
                ?>>
                    <?= Yii::t('app', 'No recipes selected yet.') ?>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="form-group">
                <?= Html::submitButton('Guardar', ['class' => 'btn btn-success']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

