<?php
/** @var $this \yii\web\View */
/** @var $user User */
/** @var $plan \common\models\Plan */

use common\models\User;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = "Habilitar suscripción";
//die(var_dump($plan->getPrices()));
?>

<div class="vh-100 d-flex justify-content-center align-items-center">
    <div style="min-width: 350px; max-width: 1024px">
        <div class="card m-5">
            <div class="card-body">
                <h4><?= Yii::t('app', "Enable subscription to continue") ?></h4>
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6" style="max-height: 500px; overflow: auto">
                        <h5><?= "Plan {$plan->name}" ?></h5>
                        <?= $plan->description ?>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 d-flex justify-content-center">
                        <div class="d-flex flex-column justify-content-center align-items-center">
                            <div class="col text-center">
                                <div style="max-width: 400px">
                                    <img src="<?= Yii::getAlias("@web/images/img_enable_subscription.png") ?>" class="w-100" alt="" style="object-fit: scale-down">
                                </div>
                            </div>
                            <div class="col text-center">
                                <?php foreach (array_reverse($plan->getPrices()) as $index => $price): ?>
                                    <?= Html::a(Yii::t('app', "Pay {label}", [
                                        'label' => sprintf("$%s %s / %s", ($price->unit_amount / 100), strtoupper($price->currency), Yii::t('app', $price->recurring->interval))
                                    ]), ['//payment/create-checkout-session', 'price' => $price->id, 'priceAmount' => $price->unit_amount / 100], [
                                        'class' => 'btn btn-warning w-100 mb-3',
                                        'style' => "font-weight: bold; max-width: 230px",
                                        'data-price' => $price->unit_amount / 100,
                                        'data-price-id' => $price->id,
                                        'id' => 'price-' . $index
                                    ]) ?>
                                <?php endforeach; ?>
                                <div class="col-12">
                                    <?php $form = ActiveForm::begin([
                                        'id' => 'coupon-form',
                                        'method' => 'post',
                                        'action' => ['check-coupon'],
                                    ]); ?>
                                        <?= Html::textInput('couponCode', '', ['placeholder' => Yii::t('app', 'Enter coupon code'), 'id' => 'coupon-code', 'class' => 'form-control mb-3']) ?>
                                        <?= Html::button(Yii::t('app', "Aplicar Cupón"), ['class' => 'btn btn-primary w-100 mb-3', 'style' => "font-weight: bold; max-width: 230px", 'id' => 'apply-coupon']) ?>
                                    <?php ActiveForm::end(); ?>
                                    <div id="coupon-message" class="text-danger"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$planId = json_encode($plan['stripe_product_id']);
$js = <<< JS
$(document).on('click', '#apply-coupon', function() {
    var couponCode = $('#coupon-code').val();
    var prices = [];
    var updatedPrices = []; // Array para los precios actualizados
    $('.btn-warning').each(function() {
        prices.push($(this).data('price'));
    });

    $.ajax({
        url: 'check-coupon',
        type: 'POST',
        data: {code: couponCode, prices: prices},
        success: function(response) {
            if (response.success) {
                $('#coupon-message').text(response.message).removeClass('text-danger').addClass('text-success');

                // Actualizar el precio con el descuento aplicado
                $('.btn-warning').each(function(index) {
                    var newPrice = response.new_price[index]; // Nuevo precio
                    updatedPrices.push(newPrice); // Guardar el precio actualizado
                    var originalText = $(this).text();
                    // Extraer las partes del texto original
                    var originalParts = originalText.split('$'); // Dividir por el símbolo $
                    var prefix = originalParts[0].trim(); // "Pagar"
                    var priceAndRest = originalParts[1].split('/'); // Dividir por el símbolo /
                    var currency = priceAndRest[0].trim().split(' ')[1]; // "USD"
                    var interval = priceAndRest[1].trim(); // "año"
                    // Formatear el nuevo texto
                    var newText = prefix + ' $' + newPrice.toFixed(0) + ' ' + currency + ' / ' + interval;
                    // Actualizar el texto del botón
                    $(this).text(newText);
                    // Actualizar el atributo data-price con el nuevo valor
                    $(this).data('price', newPrice);
                    // Actualizar la URL del enlace con el precio actualizado
                    var priceId = $(this).data('price-id');
                    var url = $(this).attr('href');
                    var updatedUrl = new URL(url, window.location.origin);
                    updatedUrl.searchParams.set('price', priceId); // Actualiza el price con el priceId
                    updatedUrl.searchParams.set('priceAmount', newPrice);
                    updatedUrl.searchParams.set('coupon_id', response.coupon_id); // Agrega el coupon_id a la URL
                    updatedUrl.searchParams.set('nickname', interval); // Agrega el nickname a la URL
                    $(this).attr('href', updatedUrl.toString()); // Actualiza el atributo href con la URL modificada
                });
            } else {
                $('#coupon-message').text(response.error).removeClass('text-success').addClass('text-danger');
            }
        },
        error: function() {
            $('#coupon-message').text('Error validating coupon').removeClass('text-success').addClass('text-danger');
        }
    });
});

// Si no se aplica un código de descuento, enviar coupon_id como null
$('.btn-warning').each(function() {
    var priceId = $(this).data('price-id');
    var url = $(this).attr('href');
    var updatedUrl = new URL(url, window.location.origin);
    updatedUrl.searchParams.set('price', priceId); // Actualiza el price con el priceId
    updatedUrl.searchParams.set('coupon_id', null); // Establece coupon_id como null
    updatedUrl.searchParams.set('nickname', null); // Agrega el nickname a la URL
    $(this).attr('href', updatedUrl.toString()); // Actualiza el atributo href con la URL modificada
});
JS;
$this->registerJs($js);?>
