<?php

namespace ZfJoacubCrud\DataGrid\Column;

use ZfJoacubCrud\DataGrid\Column\Decorator;

class Date extends Column
{
    public function init()
    {
    	parent::init();
    	
        $this->setFormElement(new \Zend\Form\Element\DateTime($this->getName()))
             ->addDecorator(new Decorator\DateFormat('d.m.Y'));
    }
}