<?php
/** @var $model \common\models\StandardRecipe */

/** @var $this \yii\web\View */

use yii\helpers\ArrayHelper;

?>
<?php
\yii\widgets\Pjax::begin(['id' => 'pjax-list-steps', 'timeout' => false])
?>
<div class="row gap-3 mt-5">
    <div class="col-12">
        <h4><?= Yii::t('app', "Procedure") ?></h4>
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
                                data-bs-target="#modal-add-step">
                            <?= Yii::t('app', 'Add') ?>
                        </button>
                    </th>
                </tr>
                <?php foreach ($model->getRecipeSteps()->andWhere(['type' => \common\models\RecipeStep::STEP_TYPE_PROCEDURE])->all() as $step): ?>
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
                            <?= \yii\bootstrap5\Html::a(Yii::t('app', "Remove"), \yii\helpers\Url::to(['standard-recipe/remove-step', 'recipeId' => $model->id, 'id' => $step->id]), [
                                'class' => "btn btn-sm btn-danger delete",
                                'data' => [
                                    'confirm-message' => Yii::t('app', 'Are you sure you want to delete this step?'),
                                    'pjax' => "#pjax-list-steps"
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
<div class="d-flex flex-wrap align-content-center mt-5 mb-5">
    <div class="align-self-center"><span
                class="flowchart-circle"><?= Yii::t('app', "Start") ?></span></div>
    <div class="align-self-center"><i class="bx bx-right-arrow"></i></div>
    <?php foreach ($model->getRecipeSteps()->andWhere(['type' => \common\models\RecipeStep::STEP_TYPE_PROCEDURE])->all() as $step): ?>
        <?= $this->render('../_step', ['step' => $step]) ?>
        <div class="align-self-center"><i class="bx bx-right-arrow"></i></div>
    <?php endforeach; ?>
    <div class="align-self-center"><span class="flowchart-circle"><?= Yii::t('app', "End") ?></span>
    </div>
</div>
<?php \yii\widgets\Pjax::end(); ?>
