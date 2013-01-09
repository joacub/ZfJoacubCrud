<?php

namespace ZfJoacubCrud\DataGrid\Column;

use ZfJoacubCrud\DataGrid\Column\Decorator;

class DateTime extends Column
{
    /**
     * Extensions
     */
    public function init()
    {
        parent::init();
        
        $this->setFormElement(new \Zend\Form\Element\DateTime($this->getName()))
             ->addDecorator(new Decorator\DateFormat());
    }
}