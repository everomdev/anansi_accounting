<?php

namespace api\modules\v1\controllers;

use common\models\Ingredient;

class IngredientController extends BaseActiveController
{
    public $modelClass = Ingredient::class;
}
