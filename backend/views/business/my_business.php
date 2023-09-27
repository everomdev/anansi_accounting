<?php
/** @var $this \yii\web\View */
/** @var $model \backend\models\UpdateAccountForm */

$this->title = Yii::t('app', "Settings");
$timezones = timezone_identifiers_list();
$timezones = array_combine($timezones, $timezones);

$locales = intlcal_get_available_locales();
$locales = array_combine($locales, $locales);
?>

<div class="nav-align-top mb-4">
    <div class="d-flex flex-wrap justify-content-between align-content-center">
        <ul class="nav nav-pills mb-3" role="tablist">
            <li class="nav-item tab-item" data-name="general">
                <button
                        type="button"
                        class="nav-link active"
                        role="tab"
                        data-bs-toggle="tab"
                        data-bs-target="#navs-pills-top-general"
                        aria-controls="navs-pills-top-general"
                        aria-selected="true"
                >
                    <?= Yii::t('app', 'General') ?>
                </button>
            </li>

            <li class="nav-item tab-item" data-name="billing">
                <button
                        type="button"
                        class="nav-link"
                        role="tab"
                        data-bs-toggle="tab"
                        data-bs-target="#navs-pills-top-billing"
                        aria-controls="navs-pills-top-billing"
                        aria-selected="false"
                >
                    <?= Yii::t('app', 'Plan & Billing') ?>
                </button>
            </li>


        </ul>

    </div>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="navs-pills-top-general"
             role="tabpanel">
            <?= $this->render('tabs/_general', [
                'model' => $model,
                'locales' => $locales,
                'timezones' => $timezones,
            ]) ?>
        </div>
        <div class="tab-pane fade" id="navs-pills-top-billing"
             role="tabpanel">
            <?= $this->render('tabs/_billing') ?>
        </div>

    </div>
</div>


