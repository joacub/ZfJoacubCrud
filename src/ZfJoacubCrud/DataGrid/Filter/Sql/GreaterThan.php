<?php

namespace ZfJoacubCrud\DataGrid\Filter\Sql;

use ZfJoacubCrud\DataGrid\Filter;
use ZfJoacubCrud\DataGrid\Filter\Parameter\ParameterId;
use Doctrine\ORM\QueryBuilder;

class GreaterThan extends Filter\AbstractFilter
{
    /**
     * Returns the result of applying $value
     *
     * @param  mixed $value
     * @return mixed
     */
    public function apply($dataSource, $column, $value)
    {
        $value = $this->applyValueType($value);
        
        if (strlen($value) > 0) {
            if($dataSource instanceof QueryBuilder) {
                $qb = $dataSource;
                $parameter = ParameterId::getParameter(__CLASS__, $column->getName());
                $qb->andWhere($qb->expr()->gt($qb->getRootAlias() . '.' . $column->getName(), ':' . $parameter));
                $qb->setParameter($parameter, $value);
            } else {
                $dataSource->getSelect()->where(
                    new \Zend\Db\Sql\Predicate\Operator($column->getName(), \Zend\Db\Sql\Predicate\Operator::OP_GT, $value)
                );
            }
            
        }

        return $dataSource;
    }
}