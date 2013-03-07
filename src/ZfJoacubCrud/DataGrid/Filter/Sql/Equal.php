<?php

namespace ZfJoacubCrud\DataGrid\Filter\Sql;

use ZfJoacubCrud\DataGrid\Filter;
use ZfJoacubCrud\DataGrid\Filter\Parameter\ParameterId;
use Doctrine\ORM\QueryBuilder;

class Equal extends Filter\AbstractFilter
{
    /**
     * @param $select
     * @param $column
     * @param mixed $value
     * @return mixed|void
     */
    public function apply($dataSource, $column, $value)
    {
        $value = $this->applyValueType($value);

        if (isset($value) && !empty($value)) {
            if($dataSource instanceof QueryBuilder) {
                $qb = $dataSource;
                $parameter = ParameterId::getParameter(__CLASS__, $column->getName());
                $qb->andWhere($qb->expr()->eq($qb->getRootAlias() . '.' . $column->getName(), ':' . $parameter))
                ->setParameter($parameter, $value);
            } else {
                //$columnName = $this->_findTableColumnName($select, $column->getName());
                $dataSource->getSelect()->where(array($column->getName() => $value));
            }
        	
        }

        return $dataSource;
    }
}