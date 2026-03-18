<?php

namespace App\Controller\Api;

use App\Entity\{Course, CourseSubject};
use App\Repository\{CourseRepository, CourseSubjectRepository};
use App\Service\{GetCourseView, GetCourseSubjectView, GetCourseTeaserView};
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;

class ApiCourseController extends AbstractFOSRestController
{

    #[Get(path: '/courses')]
    public function getCoursesAction(CourseRepository $courseRepository, GetCourseTeaserView $getCourseTeaserView): View
    {
        $courses = $courseRepository->findBy([], ['weight' => 'ASC']);
        $courseViews = array_map(
            fn (Course $course) => ($getCourseTeaserView)($course),
            $courses
        );
        return $this->view($courseViews);
    }

    #[Get(path: '/courses/{courseId}')]
    public function getCourse(
        $courseId,
        CourseRepository $courseRepository,
        GetCourseView $getCourseView
    ): View {
        $course = $courseRepository->find($courseId);
        return $this->view(($getCourseView)($course));
    }

    /**
     * @deprecated 2.0
     */
    #[Get(path: '/courses/{courseId}/course-subjects')]
    public function getCourseSubjectsAction(
        $courseId,
        CourseSubjectRepository $courseSubjectRepository,
        GetCourseSubjectView $getCourseSubjectView
    ): View {
        $courseSubjects = $courseSubjectRepository->findByCourse($courseId, ['weight' => 'ASC']);
        return $this->view(array_map(
            fn (CourseSubject $courseSubject) => ($getCourseSubjectView)($courseSubject),
            $courseSubjects
        ));
    }
}
