<?php

namespace App\Tests\Service;

use App\Entity\Community;
use App\Entity\CommunityTest;
use App\Entity\CommunityTestCourseSubject;
use App\Entity\Course;
use App\Entity\CourseSubject;
use App\Entity\Exam;
use App\Entity\File;
use App\Entity\KnowledgeTest;
use App\Entity\Subject;
use App\Entity\TestYear;
use App\Service\PremiumService;
use App\Service\Product\PauBundleProductCatalog;
use App\Service\Security;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class PremiumServiceTest extends TestCase
{
    public function testPackEnunciadoIsPublicEvenWhenExamIsOld(): void
    {
        $service = $this->createPremiumService();
        $exam = $this->createMadridMathPackExam('2022');

        $file = (new File())
            ->setName('Enunciados')
            ->setExam($exam);

        self::assertTrue($service->canSeeExamFile($file));
    }

    public function testPackSolutionRemainsLockedWhenExamIsOld(): void
    {
        $service = $this->createPremiumService();
        $exam = $this->createMadridMathPackExam('2022');

        $file = (new File())
            ->setName('Soluciones')
            ->setExam($exam);

        self::assertFalse($service->canSeeExamFile($file));
    }

    public function testPackSolutionRemainsLockedWhenExamIsRecent(): void
    {
        $service = $this->createPremiumService();
        $exam = $this->createMadridMathPackExam('2025');

        $file = (new File())
            ->setName('Soluciones')
            ->setExam($exam);

        self::assertFalse($service->canSeeExamFile($file));
    }

    private function createPremiumService(): PremiumService
    {
        $authChecker = $this->createStub(AuthorizationCheckerInterface::class);
        $authChecker
            ->method('isGranted')
            ->willReturn(false);

        $security = $this->createStub(Security::class);
        $security
            ->method('getUser')
            ->willReturn(null);

        return new PremiumService($authChecker, $security, new PauBundleProductCatalog());
    }

    private function createMadridMathPackExam(string $year): Exam
    {
        $community = (new Community())
            ->setName('Madrid')
            ->setSlug('madrid');
        $knowledgeTest = (new KnowledgeTest())
            ->setName('Selectividad')
            ->setSlug('selectividad');
        $communityTest = (new CommunityTest())
            ->setCommunity($community)
            ->setKnowledgeTest($knowledgeTest);

        $course = (new Course())
            ->setName('2º Bachillerato')
            ->setSlug('2o-bachillerato');
        $subject = (new Subject())
            ->setName('Matemáticas II')
            ->setSlug('matematicas');
        $courseSubject = (new CourseSubject())
            ->setCourse($course)
            ->setSubject($subject);

        $communityTestCourseSubject = (new CommunityTestCourseSubject())
            ->setCommunityTest($communityTest)
            ->setCourseSubject($courseSubject);
        $testYear = (new TestYear())
            ->setYear($year)
            ->setCommunityTestCourseSubject($communityTestCourseSubject);

        return (new Exam())->setTestYear($testYear);
    }
}
