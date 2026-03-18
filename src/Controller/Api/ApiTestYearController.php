<?php

namespace App\Controller\Api;

use App\Entity\TestYear;
use App\Repository\TestYearRepository;
use App\Service\GetTestYearView;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\{Get};
use FOS\RestBundle\Controller\AbstractFOSRestController;

class ApiTestYearController extends AbstractFOSRestController
{

    #[Get(path: '/test-years')]
    public function getTestYearsAction(
        Request $request,
        TestYearRepository $testYearRepository,
        GetTestYearView $getTestYearView
    ) {
        $ids = $request->query->get('ids');
        if ($ids === null) {
            throw $this->createNotFoundException('No se especificaron los ids');
        }
        return array_map(
            fn (TestYear $testYear) => ($getTestYearView)($testYear),
            $testYearRepository->findById(explode(',', $ids), ['year' => 'DESC'])
        );
    }

    #[Get(path: '/test-years/{id}')]
    public function getTestYearAction(
        string $id,
        TestYearRepository $testYearRepository,
        GetTestYearView $getTestYearView
    ) {
        $testYear = $testYearRepository->find($id);
        if ($testYear === null) {
            throw $this->createNotFoundException('No se encontró el TestYear');
        }
        return $this->view(($getTestYearView)($testYear));
    }
}
