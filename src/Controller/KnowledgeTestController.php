<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\KnowledgeTestRepository;
use App\Repository\CommunityTestRepository;
use App\Repository\CommunityTestCourseSubjectRepository;
use App\Repository\ExamRepository;
use Symfony\Component\HttpFoundation\Response;

class KnowledgeTestController extends AbstractController
{
    public function test(string $testSlug, KnowledgeTestRepository $testRepository): Response
    {
        $test = $testRepository->findOneBy(['slug' => $testSlug]);
        if ($test === null) {
            throw $this->createNotFoundException('No existe esa prueba');
        }
        return $this->render(
            'views/knowledge_tests/knowledge_test/knowledge_test.html.twig',
            ['test' => $test]
        );
    }

    public function communityTest(
        string $testSlug,
        string $communitySlug,
        CommunityTestRepository $communityTestRepository
    ): Response {
        $communityTest = $communityTestRepository->findByCommunityAndTestSlugs($testSlug, $communitySlug);
        if ($communityTest === null) {
            throw $this->createNotFoundException('No existe esa prueba para esa comunidad autónoma');
        }
        return $this->render(
            'views/knowledge_tests/community_test/community_test.html.twig',
            ['communityTest' => $communityTest]
        );
    }

    public function communityTestCourseSubject(
        string $testSlug,
        string $communitySlug,
        string $courseSubjectSlug,
        CommunityTestCourseSubjectRepository $communityTestCourseSubjectRepository
    ): Response {
        $communityTestCourseSubject = $communityTestCourseSubjectRepository->findByCommunityAndTestAndCourseSubjectSlugs(
            $testSlug,
            $communitySlug,
            $courseSubjectSlug,
        );
        if ($communityTestCourseSubject === null) {
            throw $this->createNotFoundException('No existe esa asignatura para esa comunidad autónoma');
        }
        return $this->render(
            'views/knowledge_tests/community_test_course_subject/community_test_course_subject.html.twig',
            ['communityTestCourseSubject' => $communityTestCourseSubject]
        );
    }

    public function exam(
        string $testSlug,
        string $communitySlug,
        string $courseSubjectSlug,
        string $examSlug,
        ExamRepository $examRepository
    ): Response {
        $exam = $examRepository->findByCriteria(
            $testSlug,
            $communitySlug,
            $courseSubjectSlug,
            $examSlug
        );

        if ($exam === null) {
            throw $this->createNotFoundException('No existe ese examen de selectividad');
        }

        return $this->render(
            'views/knowledge_tests/exam/exam.html.twig',
            ['exam' => $exam]
        );
    }
}
