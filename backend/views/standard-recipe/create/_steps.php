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
                    <th class="text-center">Imagen</th>
                    <th>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                data-bs-target="#modal-add-step">
                            <?= Yii::t('app', 'Add') ?>
                        </button>
                    </th>
                </tr>
                <?php foreach ($model->getRecipeSteps()->andWhere(['type' => \common\models\RecipeStep::STEP_TYPE_PROCEDURE])->all() as $step): ?>
                    <tr>
                        <td class="text-center">
                            <?= $step->number ?>
                        </td>
                        <td class="text-center">
                            <?= $step->activity ?>
                        </td>
                        <td class="text-center">
                            <?= $step->time ?>
                        </td>
                        <td class="text-center">
                            <?= $step->indicator ?>
                        </td>
                        <td class="text-center">
                            <?php $image = $step->getImage(); ?>
                            <?php
                                $imgUrl = $image ? $image->getUrl() : null;
                                $imgThumb = $image ? $image->getUrl('200x200') : null;
                                $isRealImage = $imgUrl && strpos($imgUrl, 'no-image') === false && strpos($imgThumb, 'no-image') === false && strpos($imgThumb, 'placeHolder') === false;
                            ?>
                            <?php if ($isRealImage): ?>
                                <a href="#" class="procedure-step-img-link" data-img="<?= $imgUrl ?>">
                                    <img src="<?= $imgThumb ?>" alt="Imagen" style="max-width: 80px; max-height: 80px; border-radius: 6px; cursor:pointer;" />
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Sin imagen</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group" style="gap: 5px;">
                                <button type="button"
                                    class="btn btn-sm btn-danger btn-delete-step"
                                    data-url="<?= \yii\helpers\Url::to(['standard-recipe/remove-step', 'recipeId' => $model->id, 'id' => $step->id]) ?>">
                                    <?= Yii::t('app', "Remove") ?>
                                </button>
                                <?= \yii\bootstrap5\Html::a(Yii::t('app', "Modificar"), '#', [
                                    'class' => "btn btn-sm btn-warning edit-step",
                                    'data-bs-toggle' => "modal",
                                    'data-bs-target' => "#modal-edit-step",
                                    'data-id' => $step->id,
                                    'data-number' => $step->number,
                                    'data-activity' => $step->activity,
                                    'data-time' => $step->time,
                                    'data-indicator' => $step->indicator,
                                    'data-img' => $isRealImage ? $imgUrl : '',
                                ]) ?>
                            </div>
                        </td>
                        <?php /*
                        <td class="text-center">
                            <?php if ($step->number > 1): ?>
                                <?= \yii\bootstrap5\Html::a('<i class="bx bx-up-arrow"></i>', ['standard-recipe/move-step', 'recipeId' => $model->id, 'id' => $step->id, 'direction' => 'up'], [
                                    'class' => "btn btn-sm btn-secondary move-step",
                                    'data-pjax' => "#pjax-list-steps",
                                ]) ?>
                            <?php endif; ?>
                            <?= \yii\bootstrap5\Html::a('<i class="bx bx-down-arrow"></i>', ['standard-recipe/move-step', 'recipeId' => $model->id, 'id' => $step->id, 'direction' => 'down'], [
                                'class' => "btn btn-sm btn-secondary move-step",
                                'data-pjax' => "#pjax-list-steps",
                            ]) ?>
                        </td>
                        */?>
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
<div class="modal fade" id="modal-edit-step" tabindex="-1" aria-labelledby="modal-edit-step-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-edit-step-label"><?= Yii::t('app', 'Editar paso') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-edit-step">
                    <input type="hidden" id="edit-step-id" name="id">
                    <div class="mb-3">
                        <label for="edit-step-activity" class="form-label"><?= Yii::t('app', 'Activity') ?></label>
                        <input type="text" class="form-control" id="edit-step-activity" name="activity">
                    </div>
                    <div class="mb-3">
                        <label for="edit-step-time" class="form-label"><?= Yii::t('app', 'Time') ?></label>
                        <input type="text" class="form-control" id="edit-step-time" name="time">
                    </div>
                    <div class="mb-3">
                        <label for="edit-step-indicator" class="form-label"><?= Yii::t('app', 'Indicator') ?></label>
                        <input type="text" class="form-control" id="edit-step-indicator" name="indicator">
                    </div>
                    <div class="mb-3">
                        <label for="edit-step-image" class="form-label"><?= Yii::t('app', 'Imagen') ?></label>
                        <div id="edit-step-image-preview-container" class="mb-2 position-relative" style="display:none;">
                            <img id="edit-step-image-preview" src="" alt="Imagen actual" style="max-width: 120px; max-height: 120px; border-radius: 6px; display:block; margin-bottom:8px;" />
                            <button type="button" id="edit-step-remove-image-btn" class="btn btn-sm btn-danger position-absolute" style="top:0; right:0; border-radius:50%; width:28px; height:28px; padding:0; display:flex; align-items:center; justify-content:center; z-index:2;" title="Eliminar imagen actual">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <input type="hidden" id="edit-step-remove-image" name="remove_image" value="0">
                        </div>
                        <input type="file" class="form-control" id="edit-step-image" name="_image" accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= Yii::t('app', 'Cancelar') ?></button>
                <button type="button" class="btn btn-success" id="save-edit-step"><?= Yii::t('app', 'Guardar') ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Modal imagen grande de paso -->
<div class="modal fade" id="procedureStepImageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Imagen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
        <img id="procedure-step-modal-img" src="" alt="Imagen" style="max-width:100%;max-height:70vh;border-radius:8px;" />
      </div>
    </div>
  </div>
</div>

<?php \yii\widgets\Pjax::end(); ?>

<?php
$this->registerJsFile('@web/js/standard-recipe/index.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>