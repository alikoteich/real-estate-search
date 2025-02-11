<?php

// src/Controller/SearchController.php
namespace App\Controller;

use App\Repository\PropertyRepository;
use App\Service\FilterManager;
use App\Service\PropertyQueryParser;
use App\Service\RecommendationService;
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
        PropertyRepository $propertyRepo,
        FilterManager $filterManager,
        RecommendationService $recommendationService
    ): Response {
        // 1. Parse Input
        $query = $request->query->get('query', '');
        $selectedFilter = $request->query->get('filter');
        $criteria = $parser->parse($query);

        // 2. Fetch Results
        $results = $propertyRepo->searchByCriteria($criteria);

        // 3. Apply Filters (if any)
        if ($selectedFilter) {
            $qb = $propertyRepo->getSearchQueryBuilder($criteria);
            $filterManager->applyFilter($qb, $selectedFilter);
            $results = $qb->getQuery()->getResult();
        }

        // 4. Get Recommendations
        $recommendations = $recommendationService->getRecommendations(
            $criteria,
            array_map(fn($p) => $p->getId(), $results)
        );

        // 5. Render Template
        return $this->render('search/index.html.twig', [
            'query' => $query,
            'results' => $results,
            'recommendations' => $recommendations,
            'selectedFilter' => $selectedFilter,
            'criteria' => $criteria,
        ]);
    }
}