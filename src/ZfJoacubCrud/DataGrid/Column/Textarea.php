<?php

namespace ZfJoacubCrud\DataGrid\Column;

use ZfJoacubCrud\DataGrid\Column\Decorator;

class Textarea extends Column
{
    public function init()
    {
        parent::init();

        $this->setFormElement(new \Zend\Form\Element\Textarea($this->getName()));
    }
}