<?php

use common\models\Category;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\IngredientStock */
/* @var $form yii\widgets\ActiveForm */

$ingredients = (new \yii\db\Query())
    ->select("*")
    ->from('ingredient')
    ->all();

$autocompleteName = \yii\helpers\ArrayHelper::getColumn($ingredients, 'name');
$autocompleteUm = array_values(array_unique(\yii\helpers\ArrayHelper::getColumn($ingredients, 'um')));
$autocompletePortions = array_values(array_unique(\yii\helpers\ArrayHelper::getColumn($ingredients, 'portions_per_unit')));
$autocompletePortionsUm = array_values(array_unique(\yii\helpers\ArrayHelper::getColumn($ingredients, 'portion_um')));

$autocompleteCategories = array_values(array_unique(\yii\helpers\ArrayHelper::getColumn(Category::all(), 'name')));

$this->registerJsFile(Yii::getAlias("@web/js/ingredient-stock/form.js"), [
    'depends' => \yii\web\YiiAsset::class,
    'position' => $this::POS_END
]);

$business = \backend\helpers\RedisKeys::getValue(\backend\helpers\RedisKeys::BUSINESS_KEY);
$ums = \common\models\UnitOfMeasurement::findAll(['business_id' => $business['id']]);
$currencySymbol = \Symfony\Component\Intl\Currencies::getSymbol(strtoupper($business['currency_code']));
?>

<div class="ingredient-stock-form">

    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => true
    ]); ?>

    <div class="card">
        <div class="card-body">
            <?= $form->field($model, 'final_quantity')->hiddenInput()->label(false) ?>
            <div class="row gap-2">

                <div class="col-sm-12 col-md-6 col-lg-4 col-xl-4">
                    <?= $form->field($model, 'ingredient')->widget(\kartik\typeahead\Typeahead::class, [
                        'scrollable' => true,
                        'defaultSuggestions' => $autocompleteName,
                        'dataset' => [
                            [
                                'local' => $autocompleteName,
                                'limit' => 10,

                            ]
                        ],

                    ]) ?>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-4 col-xl-4">
                    <?= $form->field($model, 'category_id')->dropDownList(\yii\helpers\ArrayHelper::map(Category::find()->all(), 'id', 'name'), ['prompt' => '----', 'data-url' => Url::to(['ingredient-stock/generate-key'])]) ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                    <?= $form->field($model, 'quantity')->textInput() ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                    <?= $form->field($model, 'um')->dropDownList(\yii\helpers\ArrayHelper::map($ums, 'name', 'name')) ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                    <?= $form->field($model, 'portions_per_unit')->widget(\kartik\typeahead\Typeahead::class, [
                        'scrollable' => true,
                        'defaultSuggestions' => $autocompletePortions,
                        'dataset' => [
                            [
                                'local' => $autocompletePortions,
                                'limit' => 10,

                            ]
                        ],

                    ]) ?>
                </div>
                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                    <?= $form->field(
                        $model,
                        'yield',
                        [
                            'template' => "{label}<br><div class='input-group'>{input} <span class='input-group-text'>%</span><button type='button' class='btn btn-outline-primary' id='compute-yield'>Calcular</button></div>"
                        ]
                    )
                        ->textInput() ?>
                </div>

                <div class="col-sm-12 col-md-4 col-lg-3 col-xl-3">
                    <?= $form->field($model, 'portion_um')->dropDownList(\yii\helpers\ArrayHelper::map($ums, 'name', 'name')) ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                    <?= $form->field($model, 'key')->textInput() ?>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-2 col-xl-2">
                    <?= $form->field($model, 'price',
                        [
                            'template' => "{label}<br><div class='input-group'><span class='input-group-text'>${currencySymbol}</span>{input} </div>"
                        ]
                    )->textInput() ?>
                </div>
                <div class="col-12">
                    <?= $form->field($model, 'observations')->textarea(['rows' => 6]) ?>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
            </div>
        </div>
    </div>


    <?php ActiveForm::end(); ?>

</div>
<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-yield',
]);

echo \yii\bootstrap5\Html::label(Yii::t('app', 'Final Quantity'), 'final-quantity');
echo \yii\bootstrap5\Html::input(
    'number',
    'final-quantity',
    '',
    ['class' => 'form-control', 'id' => 'final-quantity']
);

echo \yii\bootstrap5\Html::button(Yii::t('app', 'Calcular rendimiento'), [
    'class' => 'btn btn-outline-primary mt-3',
    'id' => 'btn-compute-yield'
]);

\yii\bootstrap5\Modal::end();

?>
