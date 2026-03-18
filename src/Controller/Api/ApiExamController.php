<?php

namespace App\Controller\Api;

use App\Entity\Exam;
use App\Repository\ExamRepository;
use App\Service\GetExamView;
use FOS\RestBundle\Controller\Annotations\{Get};
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;

class ApiExamController extends AbstractFOSRestController
{

    #[Get(path: '/exams/{id}')]
    public function getExamAction(
        int $id,
        ExamRepository $examRepository,
        GetExamView $getExamView
    ) {
        $exam = $examRepository->find($id);
        if ($exam === null) {
            throw $this->createNotFoundException('No se encontró ese examen');
        }
        return $this->view(($getExamView)($exam));
    }

    #[Get(path: '/exams')]
    public function getExamsAction(
        Request $request,
        ExamRepository $examRepository,
        GetExamView $getExamView
    ) {
        $ids = $request->query->get('ids');
        if ($ids === null) {
            throw $this->createNotFoundException('No se especificaron los ids');
        }
        return $this->view(array_map(
            fn (Exam $exam) => ($getExamView)($exam),
            $examRepository->findById(explode(',', $ids), ['weight' => 'ASC'])
        ));
    }
}
