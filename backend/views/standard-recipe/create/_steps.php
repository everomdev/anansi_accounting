<?php
/** @var $model \common\models\StandardRecipe */

/** @var $this \yii\web\View */

use yii\helpers\ArrayHelper;

?>

<div class="row gap-3 mt-3">
    <div class="col-12">
        <table class="table">
            <thead class="bg-primary">

            </thead>
            <tbody>
            <tr>
                <td colspan="5" class="text-center bg-primary text-white"><?= Yii::t('app', "Procedure") ?></td>
            </tr>
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
            <?php foreach ($model->recipeSteps as $step): ?>
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
                            'class' => "btn btn-sm btn-danger",
                            'data' => [
                                'method' => 'post',
                                'confirm' => Yii::t('app', 'Are you sure you want to delete this step?')
                            ]
                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

