<?php

// src/Repository/PropertyRepository.php
namespace App\Repository;

use App\Entity\Property;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PropertyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Property::class);
    }

    public function getSearchQueryBuilder(array $criteria)
    {
        $qb = $this->createQueryBuilder('p');

        // Apply criteria
        if (!empty($criteria['type'])) {
            $qb->andWhere('p.type = :type')->setParameter('type', $criteria['type']);
        }

        if (!empty($criteria['bedrooms'])) {
            $qb->andWhere('p.bedrooms = :bedrooms')->setParameter('bedrooms', $criteria['bedrooms']);
        }

        if (!empty($criteria['price'])) {
            if ($criteria['price']['operator'] === 'BETWEEN') {
                $qb->andWhere('p.price BETWEEN :min AND :max')
                    ->setParameter('min', $criteria['price']['min'])
                    ->setParameter('max', $criteria['price']['max']);
            } else {
                $qb->andWhere("p.price {$criteria['price']['operator']} :price")
                    ->setParameter('price', $criteria['price']['value']);
            }
        }

        if (!empty($criteria['location'])) {
            $qb->andWhere('p.location = :location')->setParameter('location', $criteria['location']);
        }

        return $qb;
    }

    public function searchByCriteria(array $criteria): array
    {
        return $this->getSearchQueryBuilder($criteria)
            ->getQuery()
            ->getResult();
    }
}
