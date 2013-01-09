<?php

namespace ZfJoacubCrud\DataGrid\Column;

use ZfJoacubCrud\DataGrid\Column\Decorator;

class Literal extends Column
{
    /**
     * 
     */
	public function init()
	{
		parent::init();
		
		$this->addDecorator(new Decorator\Literal())
             ->setFormElement(new \Zend\Form\Element\Text($this->getName()));
	}
}