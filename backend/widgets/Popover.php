<?php

namespace backend\widgets;

use rmrevin\yii\fontawesome\FAS;
use yii\bootstrap5\Html;

class Popover extends \yii\bootstrap5\Widget
{
    public $placement;
    public $title;
    public $icon;
    public $tag;
    public $content;
    public $trigger;
    public $triggerElement;
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
        if (empty($this->content)) {
            $this->content = '';
        }
        if (empty($this->trigger)) {
            $this->trigger = 'hover';
        }



    }

    public function run()
    {
        /*
         * data-bs-toggle="popover"
         * data-bs-offset="0,14"
         * data-bs-placement="right"
         * data-bs-html="true"
         * data-bs-content="
         *  <p>This is a very beautiful popover, show some love.</p>
         *  <div class='d-flex justify-content-between'>
         *      <button type='button' class='btn btn-sm btn-outline-secondary'>
         *          Skip
         *      </button>
         *      <button type='button' class='btn btn-sm btn-primary'>
         *          Read More
         *      </button>
         *  </div>"
         * title=""
         * data-bs-original-title="Popover Title"
         */

        return Html::tag($this->tag, $this->icon, [
            'data-bs-toggle' => 'popover',
            'data-bs-offset' => '0,14',
            'data-bs-placement' => $this->placement,
            'data-bs-html' => "true",
            'title' => "",
            "data-bs-original-title" => "{$this->icon} {$this->title}",
            "data-bs-content" => $this->content
        ]);

    }
}
