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
use App\Entity\SonataMediaMedia;
use App\Entity\Subject;
use App\Entity\TestYear;
use App\Entity\User;
use App\Service\ApiGatingPolicy;
use App\Service\GetFileView;
use App\Service\PremiumService;
use App\Service\Product\PauBundleProductCatalog;
use App\Service\PublicUrlGenerator;
use App\Service\Security;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Pins the rules the API answers with. They are the website rules, taken from
 * PremiumService, so the app never has to know what makes a file free.
 */
final class GetFileViewTest extends TestCase
{
    private const URL = 'https://s3.example.com/signed.pdf';

    public function testFreeChapterFileIsNotLocked(): void
    {
        $view = ($this->createGetFileView(gatingEnabled: true, appVersion: '9.0.0'))(
            $this->createChapterFile('Apuntes')
        );

        self::assertFalse($view->locked);
        self::assertSame(self::URL, $view->file);
    }

    public function testPremiumChapterFileIsLockedAndHasNoUrl(): void
    {
        $view = ($this->createGetFileView(gatingEnabled: true, appVersion: '9.0.0'))(
            $this->createChapterFile('Soluciones')
        );

        self::assertTrue($view->locked);
        self::assertNull($view->file);
    }

    public function testPremiumUserGetsTheUrl(): void
    {
        $premiumUser = (new User())->setPremiumUntil(new DateTimeImmutable('+1 year'));

        $view = ($this->createGetFileView(gatingEnabled: true, appVersion: '9.0.0', user: $premiumUser))(
            $this->createChapterFile('Soluciones')
        );

        self::assertFalse($view->locked);
        self::assertSame(self::URL, $view->file);
    }

    public function testLockedFileKeepsItsUrlWhileGatingIsOff(): void
    {
        $view = ($this->createGetFileView(gatingEnabled: false, appVersion: '9.0.0'))(
            $this->createChapterFile('Soluciones')
        );

        self::assertTrue($view->locked);
        self::assertSame(self::URL, $view->file);
    }

    /**
     * App 8.3.0 sends no version header and cannot render a locked file, so it keeps
     * working exactly as it shipped. Those installs are pushed to update instead.
     */
    public function testLockedFileKeepsItsUrlForClientsThatDoNotSendTheirVersion(): void
    {
        $view = ($this->createGetFileView(gatingEnabled: true, appVersion: null))(
            $this->createChapterFile('Soluciones')
        );

        self::assertTrue($view->locked);
        self::assertSame(self::URL, $view->file);
    }

    /**
     * An exam older than two years is premium as a whole on the website, so every one of its
     * files is locked in the API too, even a name that would be free inside a chapter.
     */
    public function testEveryFileOfAnOldExamIsLocked(): void
    {
        $getFileView = $this->createGetFileView(gatingEnabled: true, appVersion: '9.0.0');
        $oldExam = $this->createExam((string) ((int) date('Y') - 5));

        self::assertTrue(($getFileView)($this->createExamFile('Enunciados', $oldExam))->locked);
        self::assertTrue(($getFileView)($this->createExamFile('Soluciones', $oldExam))->locked);
    }

    public function testRecentExamFilesAreFree(): void
    {
        $getFileView = $this->createGetFileView(gatingEnabled: true, appVersion: '9.0.0');
        $recentExam = $this->createExam((string) ((int) date('Y') + 1));

        self::assertFalse(($getFileView)($this->createExamFile('Soluciones', $recentExam))->locked);
    }

    private function createGetFileView(bool $gatingEnabled, ?string $appVersion, ?User $user = null): GetFileView
    {
        $authChecker = $this->createStub(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->willReturn(false);

        $security = $this->createStub(Security::class);
        $security->method('getUser')->willReturn($user);

        $publicUrlGenerator = $this->createStub(PublicUrlGenerator::class);
        $publicUrlGenerator->method('generate')->willReturn(self::URL);

        $request = new Request();
        if ($appVersion !== null) {
            $request->headers->set(ApiGatingPolicy::APP_VERSION_HEADER, $appVersion);
        }
        $requestStack = new RequestStack();
        $requestStack->push($request);

        return new GetFileView(
            $publicUrlGenerator,
            new PremiumService($authChecker, $security, new PauBundleProductCatalog()),
            new ApiGatingPolicy($gatingEnabled, $requestStack)
        );
    }

    private function createChapterFile(string $name): File
    {
        return (new File())
            ->setName($name)
            ->setFile(new SonataMediaMedia());
    }

    private function createExamFile(string $name, Exam $exam): File
    {
        return (new File())
            ->setName($name)
            ->setExam($exam)
            ->setFile(new SonataMediaMedia());
    }

    private function createExam(string $year): Exam
    {
        $communityTest = (new CommunityTest())
            ->setCommunity((new Community())->setName('Madrid')->setSlug('madrid'))
            ->setKnowledgeTest((new KnowledgeTest())->setName('Selectividad')->setSlug('selectividad'));

        $courseSubject = (new CourseSubject())
            ->setCourse((new Course())->setName('2º Bachillerato')->setSlug('2o-bachillerato'))
            ->setSubject((new Subject())->setName('Historia')->setSlug('historia'));

        $testYear = (new TestYear())
            ->setYear($year)
            ->setCommunityTestCourseSubject(
                (new CommunityTestCourseSubject())
                    ->setCommunityTest($communityTest)
                    ->setCourseSubject($courseSubject)
            );

        return (new Exam())->setTestYear($testYear);
    }
}
