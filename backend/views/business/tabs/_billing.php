<?php
/** @var $this \yii\web\View */
/** @var $business \common\models\Business */
/** @var $user \common\models\User */
/** @var $subscription \Stripe\Subscription */
/** @var $invoice \Stripe\Invoice */
/** @var $plan \common\models\Plan */

$business = \backend\helpers\RedisKeys::getBusiness();
$user = \common\models\User::findOne(['id' => $business->user_id]);
$subscription = $user->getSubscription();
$plan = $user->plan;
$subscriptionStatus = '-';
if (!empty($subscription)) {
    if ($subscription->status == 'active') {
        $subscriptionStatus = Yii::t('app', "Active");
    } else {
        $subscriptionStatus = Yii::t('app', "Inactive");
    }
} else {
    $subscriptionStatus = $user->userPlan->stripe_subscription_status;
}
$invoices = $user->getInvoices();
$this->registerJsVar("currentPlanId", $plan->id);
?>

<div class="row">
    <div class="col-12">
        <h5><?= Yii::t('app', "Subscription: <strong>{status}</strong>", ['status' => $subscriptionStatus]) ?></h5>
        <table class="table table-borderless">
            <tbody>
            <tr>
                <td><?= Yii::t('app', "Current plan") ?></td>
                <td>
                    <?php if (!empty($subscription)): ?>
                        <?php
                        /** @var \Stripe\SubscriptionItem $item */
                        $item = $subscription->items->first();
                        ?>
                        <?= sprintf("%s - %s %s / %s", $plan->name, ($item->price->unit_amount / 100), strtoupper($item->price->currency), $item->price->recurring->interval) ?>
                    <?php else: ?>
                        <?= $plan->name ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><?= Yii::t('app', "Cambiar Plan") ?></td>
                <td><?= \yii\bootstrap5\Html::dropDownList('plan_id', $plan->id, \yii\helpers\ArrayHelper::map(\common\models\Plan::find()->all(), 'id', 'label'), [
                        'class' => 'form-control',
                        'id' => 'plan_id',
                        'data-url' => \yii\helpers\Url::to(['payment/change-plan-form']),

                    ]) ?></td>
            </tr>
            <tr>
                <td><?= Yii::t('app', "Current period") ?></td>
                <td><?= empty($subscription) ? '' : sprintf("%s - %s", date('d M Y', $subscription->current_period_start), date('d M Y', $subscription->current_period_end)) ?></td>
            </tr>
            </tbody>
        </table>
        <?php if (!empty($subscription) && $subscriptionStatus == 'active'): ?>
            <?= \yii\bootstrap5\Html::a(Yii::t('app', "Cancel subscription"), ['//payment/cancel-subscription'], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => Yii::t('app', "Are you sure you want to cancel the subscription?")
                ]
            ]) ?>
        <?php endif; ?>
        <?php if (empty($subscription) or $subscriptionStatus != 'active'): ?>
            <?= \yii\bootstrap5\Html::a(Yii::t('app', "Enable subscription"), ['//site/enable-subscription'], [
                'class' => 'btn btn-success',

            ]) ?>
        <?php endif; ?>

        <hr>
        <h4><?= Yii::t('app', "Invoices") ?></h4>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                <th><?= Yii::t('app', "Period") ?></th>
                <th><?= Yii::t('app', "Due Date") ?></th>
                <th><?= Yii::t('app', "Amount") ?></th>
                <th><?= Yii::t('app', "Status") ?></th>
                <th><?= Yii::t('app', "PDF") ?></th>
                </thead>
                <tbody>
                <?php foreach ($invoices as $invoice): ?>
                    <tr>
                        <td>
                            <?= sprintf(
                                "%s - %s",
                                date('d M Y', $invoice->lines->first()->period->start),
                                date('d M Y', $invoice->lines->first()->period->end),
                            ) ?>
                        </td>
                        <td><?= date('d M Y', $invoice->effective_at) ?></td>
                        <td><?= Yii::$app->formatter->asCurrency($invoice->total / 100, 'usd') ?></td>
                        <td><?= $invoice->status ?></td>
                        <td><?= \yii\bootstrap5\Html::a('<i class="bx bx-download"/>', $invoice->invoice_pdf, ['class' => 'btn btn-sm btn-success']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
\yii\bootstrap5\Modal::begin([
    'id' => 'modal-change-plan',
    'title' => Yii::t('app', "Change plan")
]);
echo "<div id='change-plan-form-container'></div>";
\yii\bootstrap5\Modal::end();
$js = <<< JS
$(document).on('change', '#plan_id', function(event){
    event.preventDefault();
    const _this = $(this);
    let url = _this.data('url');
    let value = _this.val();
    url += "?planId=" + value;
    if(value == currentPlanId) return;
    $.ajax({
        url,
        type: 'get'
    }).done(function(response){
        $("#change-plan-form-container").html(response);
        $("#modal-change-plan").modal('show');
    })
    return false;
})
JS;

$this->registerJs($js);
?>
