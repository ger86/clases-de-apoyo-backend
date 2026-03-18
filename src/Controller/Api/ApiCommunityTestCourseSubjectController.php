<?php

namespace App\Controller\Api;

use App\Entity\CommunityTestCourseSubject;
use App\Repository\CommunityTestCourseSubjectRepository;
use App\Service\GetCommunityTestCourseSubjectView;
use FOS\RestBundle\Controller\Annotations\{Get};
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;

class ApiCommunityTestCourseSubjectController extends AbstractFOSRestController
{

    #[Get(path: '/community-test-course-subjects')]
    public function getCommunityTestCourseSubjectsAction(
        Request $request,
        CommunityTestCourseSubjectRepository $communityTestCourseSubjectRepository,
        GetCommunityTestCourseSubjectView $getCommunityTestCourseSubjectView
    ) {
        $ids = $request->query->get('ids');
        if ($ids === null) {
            throw $this->createNotFoundException('No se especificaron los ids');
        }
        $dev = $communityTestCourseSubjectRepository->findById(explode(',', $ids));
        return $this->view(
            array_map(fn (CommunityTestCourseSubject $s) => ($getCommunityTestCourseSubjectView)($s), $dev)
        );
    }

    #[Get(path: '/community-test-course-subjects/{id}')]
    public function getCommunityTestCourseSubjectAction(
        string $id,
        CommunityTestCourseSubjectRepository $communityTestCourseSubjectRepository,
        GetCommunityTestCourseSubjectView $getCommunityTestCourseSubjectView
    ) {
        $communityTestCourseSubject = $communityTestCourseSubjectRepository->find($id);
        if ($communityTestCourseSubject === null) {
            throw $this->createNotFoundException('No se especificaron los ids');
        }
        return $this->view(($getCommunityTestCourseSubjectView)($communityTestCourseSubject));
    }
}
