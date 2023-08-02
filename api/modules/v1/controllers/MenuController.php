<?php

namespace api\modules\v1\controllers;

use common\models\Menu;

class MenuController extends BaseActiveController
{
    public $modelClass = Menu::class;
}
