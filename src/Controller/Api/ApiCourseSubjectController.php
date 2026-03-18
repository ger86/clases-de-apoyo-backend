<?php

namespace App\Controller\Api;

use App\Entity\ChapterBlock;
use App\Entity\CourseSubject;
use App\Repository\ChapterBlockRepository;
use App\Repository\CourseSubjectRepository;
use App\Service\GetChapterBlockView;
use App\Service\GetCourseSubjectTeaserView;
use App\Service\GetCourseSubjectView;
use FOS\RestBundle\Controller\Annotations\{Get};
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

class ApiCourseSubjectController extends AbstractFOSRestController
{
    #[Get(path: '/course-subjects')]
    public function getCourseSubjectsAction(
        Request $request,
        CourseSubjectRepository $courseSubjectRepository,
        GetCourseSubjectTeaserView $getCourseSubjectTeaserView
    ) {
        $ids = $request->query->get('ids');
        if ($ids === null) {
            throw $this->createNotFoundException('No se especificaron los ids');
        }
        $courseSubjects = $courseSubjectRepository->findById(explode(',', $ids));
        return array_map(
            fn (CourseSubject $courseSubject) => ($getCourseSubjectTeaserView)($courseSubject),
            $courseSubjects
        );
    }

    /**
     * @deprecated  2.0
     */
    #[Get(path: '/course-subjects/{courseSubjectId}/chapter-blocks')]
    public function getCourseSubjectChapterBlocksAction(
        $courseSubjectId,
        ChapterBlockRepository $chapterBlockRepository,
        GetChapterBlockView $getChapterBlockView
    ): View {
        $chapterBlocks = $chapterBlockRepository->findByCourseSubject($courseSubjectId, ['weight' => 'ASC']);
        return $this->view(array_map(
            fn (ChapterBlock $chapterBlock) => ($getChapterBlockView)($chapterBlock),
            $chapterBlocks
        ));
    }

    #[Get(path: '/course-subjects/{courseSubjectId}')]
    public function getCourseSubjectAction(
        $courseSubjectId,
        CourseSubjectRepository $courseSubjectRepository,
        GetCourseSubjectView $getCourseSubjectView
    ): View {
        $courseSubject = $courseSubjectRepository->find($courseSubjectId);
        return $this->view(($getCourseSubjectView)($courseSubject));
    }
}
