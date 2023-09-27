<?php
/** @var $model \common\models\StandardRecipe */

/** @var $this \yii\web\View */

use yii\helpers\ArrayHelper;

?>
<?php
\yii\widgets\Pjax::begin(['id' => 'pjax-list-special-steps', 'timeout' => false])
?>
<div class="row gap-3 mt-5">
    <div class="col-12">
        <h4><?= Yii::t('app', "Cares and special steps") ?></h4>
        <div class="table-responsive">
            <table class="table">
                <tbody>
                <tr>
                    <th class="text-center"><?= Yii::t('app', "#") ?></th>
                    <th class="text-center"><?= Yii::t('app', "Activity") ?></th>
                    <th class="text-center"><?= Yii::t('app', "Time") ?></th>
                    <th class="text-center"><?= Yii::t('app', "Indicator") ?></th>
                    <th>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                data-bs-target="#modal-add-special-step">
                            <?= Yii::t('app', 'Add') ?>
                        </button>
                    </th>
                </tr>
                <?php foreach ($model->getRecipeSteps()->andWhere(['type' => \common\models\RecipeStep::STEP_TYPE_SPECIAL])->all() as $step): ?>
                    <tr>
                        <td>
                            <?= $step->number ?>
                        </td>
                        <td>
                            <?= $step->activity ?>
                        </td>
                        <td>
                            <?= $step->time ?>
                        </td>
                        <td>
                            <?= $step->indicator ?>
                        </td>
                        <td>
                            <?= $step->getImage()->getUrl() ?>
                        </td>
                        <td>
                            <?= \yii\bootstrap5\Html::a(Yii::t('app', "Remove"), \yii\helpers\Url::to(['standard-recipe/remove-step', 'recipeId' => $model->id, 'id' => $step->id]), [
                                'class' => "btn btn-sm btn-danger delete",
                                'data' => [
                                    'confirm-message' => Yii::t('app', 'Are you sure you want to delete this step?'),
                                    'pjax' => "#pjax-list-special-steps"
                                ]
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php \yii\widgets\Pjax::end(); ?>
