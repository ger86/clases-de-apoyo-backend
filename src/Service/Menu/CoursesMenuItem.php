<?php

namespace App\Service\Menu;

use App\Model\MenuLink;
use App\Repository\CourseRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CoursesMenuItem implements MenuItemGeneratorInterface
{

    public function __construct(
        private CourseRepository $courseRepository,
        private UrlGeneratorInterface $router
    ) {
    }

    public static function getDefaultPriority(): int
    {
        return 0;
    }

    public function getMenuLinks(): array
    {
        $courses = $this->courseRepository->findAll();
        $menuLinks = [];
        foreach ($courses as $course) {
            $courseSubjectLinks = [];
            foreach ($course->getCourseSubjects() as $courseSubject) {
                $url = $this->router->generate(
                    'course_subject',
                    [
                        'courseSlug' => $course->getSlug(),
                        'subjectSlug' => $courseSubject->getSubjectSlug()
                    ]
                );
                $courseSubjectLinks[] = new MenuLink($courseSubject->getSubjectName(), $url);
            }
            $url = $this->router->generate(
                'course',
                [
                    'courseSlug' => $course->getSlug(),
                ]
            );
            $menuLinks[] = new MenuLink($course->getName(), $url, $courseSubjectLinks);
        }

        return $menuLinks;
    }
}
