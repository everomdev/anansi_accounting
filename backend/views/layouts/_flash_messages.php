<?php

use kartik\growl\Growl;

$flashes = Yii::$app->getSession()->getAllFlashes();
?>


<?php foreach ($flashes as $type => $message): ?>
    <?=
    Growl::widget([
        'type' => Growl::TYPE_SUCCESS,
        'title' => 'Well done!',
        'icon' => 'fas fa-check-circle',
        'body' => 'You successfully read this important alert message.',
        'showSeparator' => true,
        'delay' => 0,
        'pluginOptions' => [
            'showProgressbar' => true,
            'placement' => [
                'from' => 'top',
                'align' => 'right',
            ]
        ]
    ]);
    ?>
<?php endforeach; ?>
