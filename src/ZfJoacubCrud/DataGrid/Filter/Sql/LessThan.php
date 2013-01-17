<?php

namespace ZfJoacubCrud\DataGrid\Filter\Sql;

use ZfJoacubCrud\DataGrid\Filter;
use ZfJoacubCrud\DataGrid\DataSource\DoctrineDbTableGateway;
use ZfJoacubCrud\DataGrid\Filter\Parameter\ParameterId;

class LessThan extends Filter\AbstractFilter
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
            if($dataSource instanceof DoctrineDbTableGateway) {
                $qb = $dataSource->getQueryBuilder();
                $parameter = ParameterId::getParameter(__CLASS__, $column->getName());
                $qb->andWhere($qb->expr()->lt($dataSource->getEntity() . '.' . $column->getName(), ':' . $parameter));
                $qb->setParameter($parameter, $value);
            } else {
                $dataSource->where(
                    new \Zend\Db\Sql\Predicate\Operator($column->getName(), \Zend\Db\Sql\Predicate\Operator::OP_LT, $value)
                );
            }
            
        }

        return $dataSource;
    }
}