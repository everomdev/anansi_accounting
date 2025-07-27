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
                <thead>
                    <tr>
                        <th class="text-center" style="width: 8%;"><?= Yii::t('app', "#") ?></th>
                        <th class="text-center" style="width: 40%;"><?= Yii::t('app', "Activity") ?></th>
                        <th class="text-center" style="width: 15%;"><?= Yii::t('app', "Time") ?></th>
                        <th class="text-center" style="width: 25%;"><?= Yii::t('app', "Indicator") ?></th>
                        <th class="text-center" style="width: 12%;">
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#modal-add-special-step">
                                <?= Yii::t('app', 'Add') ?>
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($model->getRecipeSteps()->andWhere(['type' => \common\models\RecipeStep::STEP_TYPE_SPECIAL])->all() as $step): ?>
                    <tr>
                        <td class="text-center align-middle">
                            <?= $step->number ?>
                        </td>
                        <td class="text-center align-middle">
                            <?= $step->activity ?>
                        </td>
                        <td class="text-center align-middle">
                            <?= $step->time ?>
                        </td>
                        <td class="text-center align-middle">
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
                        <td class="text-center align-middle">
                            <div class="d-flex justify-content-center gap-2">
                                <?= \yii\bootstrap5\Html::a(Yii::t('app', "Remove"), \yii\helpers\Url::to(['standard-recipe/remove-step', 'recipeId' => $model->id, 'id' => $step->id]), [
                                    'class' => "btn btn-sm btn-danger delete",
                                    'data' => [
                                        'confirm-message' => Yii::t('app', 'Are you sure you want to delete this step?'),
                                        'pjax' => "#pjax-list-special-steps"
                                    ]
                                ]) ?>
                                <?= \yii\bootstrap5\Html::a(Yii::t('app', "Modificar"), '#', [
                                    'class' => "btn btn-sm btn-warning edit-special-step",
                                    'data-bs-toggle' => "modal",
                                    'data-bs-target' => "#modal-edit-special-step",
                                    'data-id' => $step->id,
                                    'data-number' => $step->number,
                                    'data-activity' => $step->activity,
                                    'data-time' => $step->time,
                                    'data-indicator' => $step->indicator,
                                    'data-img' => $image ? $image->getUrl() : '',
                                ]) ?>
                            </div>
                        </td>
                    </tr>
<!-- Modal para mostrar imagen grande -->
<div class="modal fade" id="specialStepImageModal" tabindex="-1" aria-labelledby="specialStepImageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="specialStepImageModalLabel">Imagen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
        <img id="special-step-modal-img" src="" alt="Imagen" style="max-width: 100%; max-height: 70vh; border-radius: 8px;" />
      </div>
    </div>
  </div>
</div>

<!-- Modal edición paso especial -->
<div class="modal fade" id="modal-edit-special-step" tabindex="-1" aria-labelledby="modal-edit-special-step-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-edit-special-step-label"><?= Yii::t('app', 'Editar paso especial') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-edit-special-step">
                    <input type="hidden" id="edit-special-step-id" name="id">
                    <div class="mb-3">
                        <label for="edit-special-step-activity" class="form-label"><?= Yii::t('app', 'Activity') ?></label>
<input type="text" class="form-control" id="edit-special-step-activity" name="activity" autocomplete="off" autocorrect="off" spellcheck="false">
                    </div>
                    <div class="mb-3">
                        <label for="edit-special-step-time" class="form-label"><?= Yii::t('app', 'Time') ?></label>
<input type="text" class="form-control" id="edit-special-step-time" name="time" autocomplete="off" autocorrect="off" spellcheck="false">
                    </div>
                    <div class="mb-3">
                        <label for="edit-special-step-indicator" class="form-label"><?= Yii::t('app', 'Indicator') ?></label>
<input type="text" class="form-control" id="edit-special-step-indicator" name="indicator" autocomplete="off" autocorrect="off" spellcheck="false">
                    </div>
                    <div class="mb-3">
                        <label for="edit-special-step-image" class="form-label"><?= Yii::t('app', 'Image') ?></label>
                        <div id="edit-special-step-image-preview-container" class="mb-2 position-relative" style="display:none;">
                            <img id="edit-special-step-image-preview" src="" alt="Imagen actual" style="max-width: 120px; max-height: 120px; border-radius: 6px; display:block; margin-bottom:8px;" />
                            <button type="button" id="edit-special-step-remove-image-btn" class="btn btn-sm btn-danger position-absolute" style="top:0; right:0; border-radius:50%; width:28px; height:28px; padding:0; display:flex; align-items:center; justify-content:center; z-index:2;" title="Eliminar imagen actual">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <input type="hidden" id="edit-special-step-remove-image" name="remove_image" value="0">
                        </div>
                        <input type="file" class="form-control" id="edit-special-step-image" name="_image" accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= Yii::t('app', 'Cancelar') ?></button>
                <button type="button" class="btn btn-success d-flex align-items-center gap-2" id="save-edit-special-step">
                    <span class="spinner-border spinner-border-sm me-2" id="save-edit-special-step-spinner" style="display:none;" role="status" aria-hidden="true"></span>
                    <span id="save-edit-special-step-text"><?= Yii::t('app', 'Guardar') ?></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Loading spinner para el botón de guardar
    var saveBtn = document.getElementById('save-edit-special-step');
    var saveSpinner = document.getElementById('save-edit-special-step-spinner');
    var saveText = document.getElementById('save-edit-special-step-text');
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            if (saveSpinner && saveText) {
                saveSpinner.style.display = 'inline-block';
                saveText.textContent = 'Cargando...';
                saveBtn.disabled = true;
            }
        });
    }
    // Modal imagen grande
    document.querySelectorAll('.special-step-img-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var imgSrc = this.getAttribute('data-img');
            var modalImg = document.getElementById('special-step-modal-img');
            modalImg.src = imgSrc;
            var modal = new bootstrap.Modal(document.getElementById('specialStepImageModal'));
            modal.show();
        });
    });
    var specialStepImageModal = document.getElementById('specialStepImageModal');
    if (specialStepImageModal) {
        specialStepImageModal.addEventListener('hidden.bs.modal', function () {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.querySelectorAll('.modal-backdrop').forEach(function(el) { el.remove(); });
        });
    }

    // Modal edición paso especial
    document.querySelectorAll('.edit-special-step').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var activity = this.getAttribute('data-activity');
            var time = this.getAttribute('data-time');
            var indicator = this.getAttribute('data-indicator');
            var imgUrl = this.getAttribute('data-img');
            var previewContainer = document.getElementById('edit-special-step-image-preview-container');
            var previewImg = document.getElementById('edit-special-step-image-preview');
            var removeInput = document.getElementById('edit-special-step-remove-image');
            var removeBtn = document.getElementById('edit-special-step-remove-image-btn');
            document.getElementById('edit-special-step-id').value = id;
            document.getElementById('edit-special-step-activity').value = activity;
            document.getElementById('edit-special-step-time').value = time;
            document.getElementById('edit-special-step-indicator').value = indicator;
            // Reset remove_image
            removeInput.value = '0';
            if (imgUrl) {
                previewImg.src = imgUrl;
                previewContainer.style.display = 'block';
                removeBtn.style.display = 'flex';
            } else {
                previewImg.src = '';
                previewContainer.style.display = 'none';
                removeBtn.style.display = 'none';
            }
        });
    });
    // Lógica para eliminar imagen con la X
    var removeBtn = document.getElementById('edit-special-step-remove-image-btn');
    if (removeBtn) {
        removeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            var previewContainer = document.getElementById('edit-special-step-image-preview-container');
            var previewImg = document.getElementById('edit-special-step-image-preview');
            var removeInput = document.getElementById('edit-special-step-remove-image');
            previewImg.src = '';
            previewContainer.style.display = 'none';
            removeInput.value = '1';
        });
    }
});
</script>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php \yii\widgets\Pjax::end(); ?>
