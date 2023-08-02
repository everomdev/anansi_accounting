<?php

namespace backend\widgets;

use rmrevin\yii\fontawesome\FAS;
use yii\bootstrap5\Html;

class Tooltips extends \yii\bootstrap5\Widget
{
    public $placement;
    public $title;
    public $icon;
    public $tag;

    public function init()
    {
        parent::init();
        if (empty($this->icon)) {
            $this->icon = Html::tag('icon', '', ['class' => 'bx bx-info-circle bx-xs']);
        }
        if (empty($this->placement)) {
            $this->placement = "top";
        }
        if (empty($this->title)) {
            $this->title = "";
        }
        if (empty($this->tag)) {
            $this->tag = 'div';
        }

    }

    public function run()
    {
        /*
         * data-bs-toggle="tooltip"
         * data-bs-offset="0,4"
         * data-bs-placement="top"
         * data-bs-html="true"
         * title=""
         * data-bs-original-title="<i class='bx bx-bell bx-xs' ></i> <span>Tooltip on top</span>"
         */

        return Html::tag($this->tag, $this->icon, [
            'data-bs-toggle' => 'tooltip',
            'data-bs-offset' => '0,1',
            'data-bs-placement' => $this->placement,
            'data-bs-html' => "true",
            'title' => "",
            "data-bs-original-title" => "{$this->icon} {$this->title}"
        ]);

    }
}
