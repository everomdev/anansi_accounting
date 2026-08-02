<?php
/** @var $this \yii\web\View */
/** @var $model \common\models\StandardRecipe */
/** @var $business \common\models\Business */
?>

<?php \yii\widgets\Pjax::begin([
    'id' => 'pjax-ingredients-selection',
    'timeout' => false
]) ?>
<?= $this->render('_ingredients_table', ['model' => $model]) ?>
<?php \yii\widgets\Pjax::end(); ?>

<?php
// Modal personalizado para confirmar eliminación de ingrediente (acción irreversible)
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-delete-ingredient',
    'title' => Yii::t('app', "Eliminar ingrediente"),
]);
?>
<p>¿Estás seguro de que deseas eliminar este ingrediente? <strong>Esta acción no se puede deshacer.</strong></p>
<div class="d-flex justify-content-end gap-3">
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Cancelar'), [
        'class' => 'btn btn-secondary',
        'data-bs-dismiss' => 'modal'
    ]) ?>
    <?= \yii\bootstrap5\Html::button(Yii::t('app', 'Eliminar'), [
        'class' => 'btn btn-danger',
        'id' => 'confirm-delete-ingredient-btn',
        'type' => 'button'
    ]) ?>
</div>
<?php
\yii\bootstrap5\Modal::end();
?>

<?php
$this->registerJs(<<<'JS'
let deleteIngredientUrl = '';


// --- Pendientes modo toggle global ---
document.addEventListener('pending-mode-toggle', function(e) {
    const isPendingMode = !!e.detail.enabled;
    if (isPendingMode) {
        $('.pending-checkbox').removeClass('d-none');
        $('.pending-mode-header').removeClass('d-none');
    } else {
        $('.pending-checkbox').addClass('d-none').prop('checked', false);
        $('.pending-mode-header').addClass('d-none');
    }
});

// --- Recolectar pendientes al guardar (puedes adaptar el selector del botón de guardar principal si es diferente) ---
$(document).on('submit', 'form', function(e) {
    // Solo si hay checkboxes visibles (modo pendiente activo)
    if ($('.pending-checkbox:visible').length > 0) {
        let pendientes = [];
        $('.pending-checkbox:visible:checked').each(function() {
            pendientes.push({
                ingredient_id: $(this).data('ingredient-id'),
                field: $(this).data('field'),
                is_recipe: $(this).data('is-recipe')
            });
        });
        $('#pendingFieldsRecipeIngredients').val(JSON.stringify(pendientes));
    }
});

$(document).on('click', '.delete-ingredient', function(e) {
    e.preventDefault();
    deleteIngredientUrl = $(this).data('url');
    const modal = new bootstrap.Modal(document.getElementById('modal-delete-ingredient'));
    modal.show();
});

$('#confirm-delete-ingredient-btn').on('click', function(e) {
    if (deleteIngredientUrl) {
        var $btn = $(this);
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Eliminando...');

        // Bloquear el botón "Add" mientras se elimina y se recarga la lista
        var failsafeTimer = null;

        function restoreAddBtn() {
            if (failsafeTimer) clearTimeout(failsafeTimer);
            $('#btn-open-add-ingredient').prop('disabled', false);
        }

        $('#btn-open-add-ingredient').prop('disabled', true);
        // Failsafe: nunca dejar bloqueado el botón "Add"
        failsafeTimer = setTimeout(restoreAddBtn, 30000);

        $.ajax({
            url: deleteIngredientUrl,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modal-delete-ingredient'));
                modal.hide();
                deleteIngredientUrl = '';
                if (typeof reloadIngredients === 'function') {
                    var reload = reloadIngredients();
                    if (reload && $.isFunction(reload.always)) {
                        reload.always(restoreAddBtn);
                    } else {
                        restoreAddBtn();
                    }
                } else {
                    restoreAddBtn();
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalHtml);
            },
            error: function() {
                restoreAddBtn();
            }
        });
    }
});
JS
);
?>
