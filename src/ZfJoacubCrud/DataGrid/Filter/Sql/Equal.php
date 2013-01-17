<?php

namespace ZfJoacubCrud\DataGrid\Filter\Sql;

use ZfJoacubCrud\DataGrid\Filter;
use ZfJoacubCrud\DataGrid\DataSource\DoctrineDbTableGateway;
use ZfJoacubCrud\DataGrid\Filter\Parameter\ParameterId;

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
            if($dataSource instanceof DoctrineDbTableGateway) {
                $qb = $dataSource->getSelect();
                $parameter = ParameterId::getParameter(__CLASS__, $column->getName());
                $qb->andWhere($qb->expr()->eq($dataSource->getEntity() . '.' . $column->getName(), ':' . $parameter))
                ->setParameter($parameter, $value);
            } else {
                //$columnName = $this->_findTableColumnName($select, $column->getName());
                $dataSource->getSelect()->where(array($column->getName() => $value));
            }
        	
        }

        return $dataSource;
    }
}