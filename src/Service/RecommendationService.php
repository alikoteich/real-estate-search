<?php

namespace App\Service;

use App\Entity\Property;
use Doctrine\ORM\EntityManagerInterface;

class RecommendationService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function getRecommendations(array $criteria, array $excludeIds = []): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Property::class, 'p')
            ->andWhere('p.id NOT IN (:excludeIds)')
            ->setParameter('excludeIds', $excludeIds ?: [0]);

        // Recommendations for "Better Deals" (lower price)
        $betterDeals = $this->getBetterDeals($qb, $criteria);

        // Recommendations for "Premium Options" (higher price)
        $premium = $this->getPremiumOptions($qb, $criteria);

        // Recommendations for "Nearby Locations"
        $nearby = $this->getNearbyLocations($qb, $criteria);

        return [
            'better_deals' => $betterDeals,
            'premium' => $premium,
            'nearby' => $nearby,
        ];
    }

    private function getBetterDeals($qb, array $criteria): array
    {
        if (!$criteria['price']) return [];

        $priceThreshold = $criteria['price']['value'] ?? $criteria['price']['max'];
        $clonedQb = clone $qb;
        return $clonedQb
            ->andWhere('p.type = :type')
            ->andWhere('p.location = :location')
            ->andWhere('p.price < :price')
            ->setParameter('type', $criteria['type'])
            ->setParameter('location', $criteria['location'])
            ->setParameter('price', $priceThreshold * 0.9)
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

    private function getPremiumOptions($qb, array $criteria): array
    {
        if (!$criteria['price']) return [];

        $priceThreshold = $criteria['price']['value'] ?? $criteria['price']['max'];
        $clonedQb = clone $qb;
        return $clonedQb
            ->andWhere('p.type = :type')
            ->andWhere('p.location = :location')
            ->andWhere('p.price > :price')
            ->setParameter('type', $criteria['type'])
            ->setParameter('location', $criteria['location'])
            ->setParameter('price', $priceThreshold * 1.1)
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

    private function getNearbyLocations($qb, array $criteria): array
    {
        if (!$criteria['location']) return [];

        $nearbyCities = $this->getNearbyCities($criteria['location']);
        $clonedQb = clone $qb;
        return $clonedQb
            ->andWhere('p.type = :type')
            ->andWhere('p.location IN (:locations)')
            ->setParameter('type', $criteria['type'])
            ->setParameter('locations', $nearbyCities)
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();
    }

    private function getNearbyCities(string $location): array
    {
        // Define nearby cities (e.g., Paris → Lyon, Marseille)
        $nearbyMap = [
            'Paris' => ['Lyon', 'Marseille'],
            'New York' => ['Boston', 'Philadelphia'],
        ];

        return $nearbyMap[$location] ?? [];
    }
}