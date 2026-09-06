<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

if ($exception) {
    switch ($exception->statusCode) {
        case 400:
            $this->title = Yii::t('app', 'Solicitud incorrecta');
            break;
        case 403:
            $this->title = Yii::t('app', 'Acceso denegado');
            break;
        case 404:
            $this->title = Yii::t('app', 'Página no encontrada');
            break;
        case 500:
            $this->title = Yii::t('app', 'Error interno del servidor');
            break;
        default:
            $this->title = $name;
    }
} else {
    $this->title = $name;
}
?>
<div class="site-error">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger">
        <?php if ($exception): ?>
            <?php if ($exception->statusCode == 403): ?>
                <strong><?= Yii::t('app', 'No tienes permisos para acceder a esta sección.') ?></strong><br>
                <?= Yii::t('app', 'Accede a la sección de ADMINISTRACIÓN Y CONFIGURACIÓN → AJUSTES → PLAN Y FACTURACIÓN → CAMBIAR PLAN → Selecciona el plan que deseas') ?>
            <?php elseif ($exception->statusCode == 404): ?>
                <?= Yii::t('app', 'La página que buscas no existe o ha sido movida.') ?>
            <?php elseif ($exception->statusCode == 400): ?>
                <?= Yii::t('app', 'La solicitud enviada no es válida. Por favor revisa los datos e inténtalo de nuevo.') ?>
            <?php elseif ($exception->statusCode == 500): ?>
                <?= Yii::t('app', 'Ha ocurrido un error interno en el servidor. Por favor intenta más tarde o contacta al soporte.') ?>
            <?php else: ?>
                <?= nl2br(Html::encode($message)) ?>
            <?php endif; ?>
        <?php else: ?>
            <?= nl2br(Html::encode($message)) ?>
        <?php endif; ?>
    </div>

    <?php if (!$exception || !in_array($exception->statusCode, [400,403,404,500])): ?>
    <p>
        The above error occurred while the Web server was processing your request.
    </p>
    <p>
        Please contact us if you think this is a server error. Thank you.
    </p>
    <?php endif; ?>

</div>
