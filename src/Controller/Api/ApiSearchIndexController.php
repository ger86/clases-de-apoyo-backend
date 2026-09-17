<?php

namespace App\Controller\Api;

use App\Service\GetSearchIndexView;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

final class ApiSearchIndexController extends AbstractFOSRestController
{

    /**
     * The catalogue the app puts in the phone search. It sends back the version of the index it
     * already built, and an app whose index is current gets an answer of a few bytes instead of
     * the whole catalogue.
     */
    #[Get(path: '/search-index')]
    public function getSearchIndexAction(Request $request, GetSearchIndexView $getSearchIndexView): View
    {
        $since = $request->query->get('since');

        return $this->view(($getSearchIndexView)(\is_string($since) ? $since : null));
    }
}
