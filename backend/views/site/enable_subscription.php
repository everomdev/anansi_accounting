<?php
/** @var $this \yii\web\View */
/** @var $user User */
/** @var $plan \common\models\Plan */

use common\models\User;
use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = "Habilitar suscripción";

// Verificar si hay una promoción en el mensaje flash
$hasPromotion = Yii::$app->session->hasFlash('promotion');
$promotionCode = $hasPromotion ? Yii::$app->session->getFlash('promotion') : '';
$discountPercentage = 0;

if ($promotionCode === '15% de descuento por renovación de suscripción.') {
    $discountPercentage = 15; // 15% de descuento
}
?>

<div class="vh-100 d-flex justify-content-center align-items-center">
    <div style="min-width: 350px; max-width: 1024px">
        <div class="card m-5">
            <div class="card-body">
                <h4><?= Yii::t('app', "Habilitar suscripción para continuar") ?></h4>
                
                <?php if ($hasPromotion && $discountPercentage > 0): ?>
                <div class="alert alert-success mb-4">
                    <i class="fas fa-tags me-2"></i>
                    <?= Yii::t('app', '¡Tienes un {discount}% de descuento disponible! El descuento ya está aplicado a los precios mostrados.', [
                        'discount' => $discountPercentage
                    ]) ?>
                </div>
                <?php endif; ?>
                
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
                                <?php foreach (array_reverse($plan->getPrices()) as $index => $price): 
                                    // Calcular el precio con descuento si aplica
                                    $originalPrice = $price->unit_amount / 100;
                                    $discountedPrice = $hasPromotion ? $originalPrice * (1 - $discountPercentage / 100) : $originalPrice;
                                    ?>
                                    
                                    <?php if ($hasPromotion && $discountPercentage > 0): ?>
                                    <div class="text-center mb-2">
                                        <span class="text-decoration-line-through text-muted">
                                            $<?= $originalPrice ?> <?= strtoupper($price->currency) ?>
                                        </span>
                                        <span class="badge bg-success">-<?= $discountPercentage ?>%</span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?= Html::a(Yii::t('app', "Pay {label}", [
                                        'label' => sprintf("$%s %s / %s", 
                                            number_format($discountedPrice, 2), 
                                            strtoupper($price->currency), 
                                            Yii::t('app', $price->recurring->interval)
                                        )
                                    ]), ['//payment/create-checkout-session', 
                                        'price' => $price->id, 
                                        'priceAmount' => $discountedPrice,
                                        'coupon_id' => $hasPromotion ? $promotionCode : null
                                    ], [
                                        'class' => 'btn btn-warning w-100 mb-3',
                                        'style' => "font-weight: bold; max-width: 230px",
                                        'data-price' => $discountedPrice,
                                        'data-original-price' => $originalPrice,
                                        'data-price-id' => $price->id,
                                        'id' => 'price-' . $index
                                    ]) ?>
                                <?php endforeach; ?>
                                
                                <div class="col-12">
                                    <?php $form = ActiveForm::begin([
                                        'id' => 'coupon-form',
                                        'method' => 'post',
                                        'action' => ['check-coupon'],
                                        'options' => [
                                            'onsubmit' => 'return false;' // Prevenir envío automático del formulario
                                        ]
                                    ]); ?>
                                        <?= Html::textInput('couponCode', '', [
                                            'placeholder' => Yii::t('app', 'Ingresa código de cupón'), 
                                            'id' => 'coupon-code', 
                                            'class' => 'form-control mb-3',
                                            'onkeydown' => 'if(event.key === "Enter"){event.preventDefault(); document.getElementById("apply-coupon").click(); return false;}'
                                        ]) ?>
                                        <?= Html::button(Yii::t('app', "Aplicar Cupón"), [
                                            'class' => 'btn btn-primary w-100 mb-3', 
                                            'style' => "font-weight: bold; max-width: 230px", 
                                            'id' => 'apply-coupon'
                                        ]) ?>
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
$autoApplyDiscount = $hasPromotion && $discountPercentage > 0 ? 'true' : 'false';
$discountPercent = $discountPercentage;
$promoCode = json_encode($promotionCode);

$js = <<< JS
// Variable global para saber si ya se aplicó el descuento automático
var discountAlreadyApplied = $autoApplyDiscount;
var discountPercentage = $discountPercent;
var promoCode = $promoCode;

// Si hay descuento automático, actualizar la URL para incluir el código de promoción
if (discountAlreadyApplied) {
    $('.btn-warning').each(function() {
        var url = $(this).attr('href');
        var updatedUrl = new URL(url, window.location.origin);
        updatedUrl.searchParams.set('coupon_id', promoCode); 
        $(this).attr('href', updatedUrl.toString());
    });
}

$(document).on('click', '#apply-coupon', function() {
    var couponCode = $('#coupon-code').val();
    var prices = [];
    var originalPrices = [];
    var updatedPrices = []; // Array para los precios actualizados
    
    $('.btn-warning').each(function() {
        prices.push($(this).data('price'));
        originalPrices.push($(this).data('original-price') || $(this).data('price'));
    });

    $.ajax({
        url: 'check-coupon',
        type: 'POST',
        data: {code: couponCode, prices: originalPrices, plan_id: $plan->id},
        success: function(response) {
            if (response.success) {
                $('#coupon-message').text(response.message).removeClass('text-danger').addClass('text-success');
                
                // Obtener el porcentaje de descuento de la respuesta (con valor por defecto)
                var discountPercentage = response.discount || 0;

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
                    var newText = prefix + ' $' + newPrice.toFixed(2) + ' ' + currency + ' / ' + interval;
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
                    
                    // Mostrar el precio original tachado
                    var originalPrice = $(this).data('original-price') || prices[index];
                    if (!$(this).prev('.text-decoration-line-through').length) {
                        // Verificar el tipo de descuento
                        if (response.type_discount === 'amount') {
                            // Si es un monto fijo
                            $(this).before('<div class="text-center mb-2"><span class="text-decoration-line-through text-muted">$' + 
                                           originalPrice.toFixed(2) + ' ' + currency + '</span> <span class="badge bg-success">-$' + 
                                           response.discount.toFixed(2) + '</span></div>');
                        } else {
                            // Si es un porcentaje
                            $(this).before('<div class="text-center mb-2"><span class="text-decoration-line-through text-muted">$' + 
                                           originalPrice.toFixed(2) + ' ' + currency + '</span> <span class="badge bg-success">-' + 
                                           response.discount + '%</span></div>');
                        }
                    }
                });            } else {
                // Manejo de errores más específico basado en el tipo de error
                var errorMessage = response.error;
                var errorClass = 'text-danger';
                
                // Si hay un tipo de error específico, podemos personalizar el estilo
                if (response.error_type) {
                    switch(response.error_type) {
                        case 'wrong_plan':
                            errorClass = 'text-warning';
                            // Agregar icono de advertencia para errores de plan
                            errorMessage = '<i class="fas fa-exclamation-triangle me-1"></i>' + errorMessage;
                            break;
                        case 'expired':
                            errorClass = 'text-danger';
                            errorMessage = '<i class="fas fa-clock me-1"></i>' + errorMessage;
                            break;
                        case 'limit_reached':
                            errorClass = 'text-info';
                            errorMessage = '<i class="fas fa-info-circle me-1"></i>' + errorMessage;
                            break;
                        default:
                            errorMessage = '<i class="fas fa-times-circle me-1"></i>' + errorMessage;
                    }
                }
                
                $('#coupon-message').html(errorMessage).removeClass('text-success text-danger text-warning text-info').addClass(errorClass);
            }
        },
        error: function() {
            $('#coupon-message').text('Error validando el cupón').removeClass('text-success').addClass('text-danger');
        }
    });
});

// Si no se aplica un código de descuento manual y no hay descuento automático, enviar coupon_id como null
if (!discountAlreadyApplied) {
    $('.btn-warning').each(function() {
        var priceId = $(this).data('price-id');
        var url = $(this).attr('href');
        var updatedUrl = new URL(url, window.location.origin);
        updatedUrl.searchParams.set('price', priceId); // Actualiza el price con el priceId
        updatedUrl.searchParams.set('coupon_id', null); // Establece coupon_id como null
        updatedUrl.searchParams.set('nickname', null); // Agrega el nickname a la URL
        $(this).attr('href', updatedUrl.toString()); // Actualiza el atributo href con la URL modificada
    });
}
JS;
$this->registerJs($js);?>