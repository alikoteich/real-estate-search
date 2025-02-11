<?php

// src/Service/FilterManager.php
namespace App\Service;

use Doctrine\ORM\QueryBuilder;

class FilterManager
{
    public function applyFilter(QueryBuilder $qb, string $filter): void
    {
        $qbFilter = clone $qb;

        switch ($filter) {
            case 'min_price':
                $value = $qbFilter->select('MIN(p.price)')->getQuery()->getSingleScalarResult();
                $qb->andWhere('p.price = :filterValue')->setParameter('filterValue', $value);
                break;
            case 'max_price':
                $value = $qbFilter->select('MAX(p.price)')->getQuery()->getSingleScalarResult();
                $qb->andWhere('p.price = :filterValue')->setParameter('filterValue', $value);
                break;
            case 'min_bedrooms':
                $value = $qbFilter->select('MIN(p.bedrooms)')->getQuery()->getSingleScalarResult();
                $qb->andWhere('p.bedrooms = :filterValue')->setParameter('filterValue', $value);
                break;
            case 'max_bedrooms':
                $value = $qbFilter->select('MAX(p.bedrooms)')->getQuery()->getSingleScalarResult();
                $qb->andWhere('p.bedrooms = :filterValue')->setParameter('filterValue', $value);
                break;
        }
    }
}