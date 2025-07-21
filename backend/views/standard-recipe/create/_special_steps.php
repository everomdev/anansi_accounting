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
                        <td class="text-center align-middle">
                            <?php $image = $step->getImage(); ?>
                            <?php if ($image && $image->getUrl('200x200') && strpos($image->getUrl(), 'no-image') === false): ?>
                                <a href="#" class="special-step-img-link" data-img="<?= $image->getUrl() ?>">
                                    <img src="<?= $image->getUrl('200x200') ?>" alt="Imagen" style="max-width: 80px; max-height: 80px; border-radius: 6px; cursor:pointer;" />
                                </a>
                            <?php endif; ?>
                        </td>
                        <td class="text-center align-middle">
                            <?= \yii\bootstrap5\Html::a(Yii::t('app', "Remove"), \yii\helpers\Url::to(['standard-recipe/remove-step', 'recipeId' => $model->id, 'id' => $step->id]), [
                                'class' => "btn btn-sm btn-danger delete",
                                'data' => [
                                    'confirm-message' => Yii::t('app', 'Are you sure you want to delete this step?'),
                                    'pjax' => "#pjax-list-special-steps"
                                ]
                            ]) ?>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php \yii\widgets\Pjax::end(); ?>
