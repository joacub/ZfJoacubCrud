<?php

namespace ZfJoacubCrud\DataGrid\Column\Decorator;

interface DecoratorInterface
{
    public function render($value, $row);       
}