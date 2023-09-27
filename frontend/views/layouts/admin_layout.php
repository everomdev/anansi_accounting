<?php

/*
 * This file is part of the 2amigos/yii2-usuario project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use yii\helpers\Html;
use yii\bootstrap5\Nav;
/**
 * @var $content string
 */

?>
<div class="clearfix"></div>


<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <?= Nav::widget(
    [
        'options' => [
            'class' => 'nav-tabs',
            'style' => 'margin-bottom: 15px',
        ],
        'items' => [
            [
                'label' => Yii::t('usuario', 'Users'),
                'url' => ['/user/admin/index'],
            ],
            [
                'label' => Yii::t('usuario', 'Roles'),
                'url' => ['/user/role/index'],
            ],
            [
                'label' => Yii::t('usuario', 'New user'),
                'url' => ['/user/admin/create'],
            ],
            
        ],
    ]
) ?>
                <?= $content ?>
            </div>
        </div>
    </div>
</div>
