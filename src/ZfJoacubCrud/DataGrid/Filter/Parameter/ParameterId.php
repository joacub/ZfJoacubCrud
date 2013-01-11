<?php
namespace ZfJoacubCrud\DataGrid\Filter\Parameter;

final class ParameterId
{
    protected static $parameter = 0;
    
    public static function getParameter($class, $column)
    {
        $pieces = explode('\\', $class);
        $name = end($pieces) . '_' . $column;
        return $name . self::$parameter++;
    }
}

?>