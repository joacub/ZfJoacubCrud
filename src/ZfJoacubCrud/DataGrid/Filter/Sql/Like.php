<?php

namespace ZfJoacubCrud\DataGrid\Filter\Sql;

use ZfJoacubCrud\DataGrid\Filter;
use ZfJoacubCrud\DataGrid\DataSource\DoctrineDbTableGateway;
use ZfJoacubCrud\DataGrid\Filter\Parameter\ParameterId;
use Doctrine\ORM\QueryBuilder;

class Like extends Filter\AbstractFilter
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

        if (isset($value) && !empty($value)) {
            
            //$columnName = $this->findTableColumnName($select, $column->getName());
            $columnName = $column->getName();
            
            if($dataSource instanceof QueryBuilder) {
            	$dataSource instanceof \Doctrine\ORM\QueryBuilder;
                $parameter = ParameterId::getParameter(__CLASS__, $columnName);
                $qb = $dataSource;
                $qb->andWhere(
                    $qb->expr()
                        ->orx(
                        $qb->expr()
                            ->like($qb->getRootAlias() . '.' . $columnName, ':' . $parameter)))->setParameter($parameter, '%' . $value . '%');
            } else {
                // @todo Add param for like template
                $spec = function (\Zend\Db\Sql\Where $where) use($columnName, 
                $value)
                {
                    $where->like($columnName, '%' . $value . '%');
                };
                
                $dataSource->getSelect()->where($spec);
            }
            
        }

        return $dataSource;
    }
}