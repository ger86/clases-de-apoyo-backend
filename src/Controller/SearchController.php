<?php

namespace App\Controller;

use App\Service\Search\SearchWebCatalogue;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SearchController extends AbstractController
{
    public function search(Request $request, SearchWebCatalogue $searchWebCatalogue): Response
    {
        $query = trim(mb_substr($request->query->getString('q'), 0, 100));

        return $this->render('views/search/search.html.twig', [
            'query' => $query,
            'results' => $searchWebCatalogue->search($query),
            'minQueryLength' => SearchWebCatalogue::MIN_QUERY_LENGTH,
        ]);
    }
}
