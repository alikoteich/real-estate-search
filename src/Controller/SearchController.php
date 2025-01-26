<?php

namespace App\Controller;

use App\Entity\Property;
use App\Service\PropertyQueryParser;
use App\Service\RecommendationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function index(
        Request $request,
        PropertyQueryParser $parser,
        EntityManagerInterface $entityManager,
        RecommendationService $recommendationService
    ): Response {
        $query = $request->query->get('query', '');
        $selectedFilter = $request->query->get('filter');
        $criteria = $parser->parse($query);

        // Build main query
        $qb = $entityManager->createQueryBuilder()
            ->select('p')
            ->from(Property::class, 'p');

        // Apply search criteria
        if ($criteria['type']) {
            $qb->andWhere('p.type = :type')
                ->setParameter('type', $criteria['type']);
        }

        if ($criteria['bedrooms']) {
            $qb->andWhere('p.bedrooms = :bedrooms')
                ->setParameter('bedrooms', $criteria['bedrooms']);
        }

        if ($criteria['price']) {
            if ($criteria['price']['operator'] === 'BETWEEN') {
                $qb->andWhere('p.price BETWEEN :min AND :max')
                    ->setParameter('min', $criteria['price']['min'])
                    ->setParameter('max', $criteria['price']['max']);
            } else {
                $qb->andWhere("p.price {$criteria['price']['operator']} :price")
                    ->setParameter('price', $criteria['price']['value']);
            }
        }

        if ($criteria['location']) {
            $qb->andWhere('p.location = :location')
                ->setParameter('location', $criteria['location']);
        }

        // Handle filter selection
        $filteredResults = [];
        if ($selectedFilter) {
            $qbFilter = clone $qb;
            
            // Get min/max value
            switch ($selectedFilter) {
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

        // Get final results
        $results = $qb->getQuery()->getResult();

        // Get recommendations
        $recommendations = $recommendationService->getRecommendations(
            $criteria,
            array_map(fn(Property $p) => $p->getId(), $results)
        );

        return $this->render('search/index.html.twig', [
            'query' => $query,
            'results' => $results,
            'criteria' => $criteria,
            'recommendations' => $recommendations,
            'selectedFilter' => $selectedFilter,
        ]);
    }
}