<?php

/* @var $this yii\web\View */

$this->title = Yii::$app->name;
$business = \backend\helpers\RedisKeys::getBusiness();

$css = <<< CSS
.d-flex .card-body{
    height: 155px;
    overflow-y: auto;
    
    overflow-x: auto;
}
@media screen and (max-width: 767px) {
      .d-flex .card-body{
        max-width: 350px;
        min-width: 350px;
        overflow-x: auto;
    }
}

CSS;

$this->registerCss($css);
?>
<div class="d-flex flex-wrap w-100">
    <?php if (Yii::$app->user->can('recipe_list')): ?>
        <?= $this->render('cards/_number_of_subrecipe_recipes_combos', ['business' => $business]) ?>
    <?php endif; ?>
    <?php if (Yii::$app->user->can('recipe_list')): ?>
        <?= $this->render('cards/_families', ['business' => $business]) ?>
    <?php endif; ?>
    <?php if (Yii::$app->user->can('theoretical_profitability_view')): ?>
        <?= $this->render('cards/_theoretical_yield', ['business' => $business]) ?>
    <?php endif; ?>
    <?php if (Yii::$app->user->can('real_profitability_view')): ?>
        <?= $this->render('cards/_real_yield', ['business' => $business]) ?>
    <?php endif; ?>
    <?php if (Yii::$app->user->can('storage_list')): ?>
        <?= $this->render('cards/_value_in_storage', ['business' => $business]) ?>
    <?php endif; ?>
    <?php if (Yii::$app->user->can('recipe_list')): ?>
        <?= $this->render('cards/_by_cost_center', ['business' => $business]) ?>
    <?php endif; ?>
    <?php if (Yii::$app->user->can('movements_list')): ?>
        <?= $this->render('cards/_last_three_days', ['business' => $business]) ?>
    <?php endif; ?>
    <?php if (Yii::$app->user->can('ingredients_list')): ?>
        <?= $this->render('cards/_ingredients_with_one_provider', ['business' => $business]) ?>
    <?php endif; ?>
    <?php if (Yii::$app->user->can('matrix_bcg')): ?>
        <?= $this->render('cards/_matrix_bcg', ['business' => $business]) ?>
    <?php endif; ?>
    <?php if (Yii::$app->user->can('movements_list')): ?>
        <?= $this->render('cards/_movements', ['business' => $business]) ?>
    <?php endif; ?>
    <?php if (Yii::$app->user->can('providers_list')): ?>
        <?= $this->render('cards/_providers', ['business' => $business]) ?>
    <?php endif; ?>


</div>

