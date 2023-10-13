<?php

/* @var $this yii\web\View */

$this->title = Yii::$app->name;
$business = \backend\helpers\RedisKeys::getBusiness();
?>
    <div class="d-flex flex-wrap w-100">
        <?= $this->render('cards/_number_of_subrecipe_recipes_combos', ['business' => $business]) ?>
        <?= $this->render('cards/_families', ['business' => $business]) ?>
        <?= $this->render('cards/_theoretical_yield', ['business' => $business]) ?>
        <?= $this->render('cards/_real_yield', ['business' => $business]) ?>
        <?= $this->render('cards/_value_in_storage', ['business' => $business]) ?>
        <?= $this->render('cards/_matrix_bcg', ['business' => $business]) ?>
        <?= $this->render('cards/_movements', ['business' => $business]) ?>
        <?= $this->render('cards/_by_cost_center', ['business' => $business]) ?>
        <?= $this->render('cards/_last_three_days', ['business' => $business]) ?>
        <?= $this->render('cards/_providers', ['business' => $business]) ?>
        <?= $this->render('cards/_ingredients_with_one_provider', ['business' => $business]) ?>
    </div>

