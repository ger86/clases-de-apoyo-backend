<?php

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use App\Repository\ArticleRepository;
use App\Repository\CourseRepository;
use App\Repository\KnowledgeTestRepository;
use App\Repository\YoutubeVideoRepository;
use Twig\Environment;

class SitemapService
{


    public function __construct(
        private CourseRepository $courseRepository,
        private KnowledgeTestRepository $knowledgeTestRepository,
        private ArticleRepository $articleRepository,
        private YoutubeVideoRepository $youtubeVideoRepository,
        private Environment $twig,
        private Filesystem $filesystem,
        private string $sitemapDir,
    ) {
        if (!$this->filesystem->exists($sitemapDir)) {
            $this->filesystem->mkdir($sitemapDir, 0755);
        }
    }

    public function buildSitemap(): void
    {
        $courses = $this->courseRepository->findAll();
        $knowledgeTests = $this->knowledgeTestRepository->findAll();
        $mainSitemap = $this->twig->render('sitemap/main.xml.twig', [
            'courses' => $courses,
            'knowledgeTests' => $knowledgeTests
        ]);
        $this->filesystem->dumpFile($this->sitemapDir . '/sitemap.xml', $mainSitemap);
        $this->buildCoursesSitemap();
        $this->buildKnowledgeTestsSitemap();
        $this->buildBlogSitemap();
        $this->buildVideosSitemap();
    }

    public function buildCoursesSitemap(): void
    {
        $courses = $this->courseRepository->findAll();
        foreach ($courses as $course) {
            $courseSitemap = $this->twig->render('sitemap/course.xml.twig', [
                'course' => $course,
            ]);
            $filename = "{$this->sitemapDir}/sitemap_{$course->getSlug()}.xml";
            $this->filesystem->dumpFile($filename, $courseSitemap);
        }
    }

    public function buildKnowledgeTestsSitemap(): void
    {
        $knowledgeTests = $this->knowledgeTestRepository->findAll();
        foreach ($knowledgeTests as $knowledgeTest) {
            $knSitemap = $this->twig->render('sitemap/knowledge_test.xml.twig', [
                'knowledgeTest' => $knowledgeTest,
            ]);
            $filename = "{$this->sitemapDir}/sitemap_{$knowledgeTest->getSlug()}.xml";
            $this->filesystem->dumpFile($filename, $knSitemap);
        }
    }

    public function buildBlogSitemap(): void
    {
        $articles = $this->articleRepository->findAll();
        $blogSitemap = $this->twig->render('sitemap/blog.xml.twig', [
            'articles' => $articles,
        ]);
        $filename = "{$this->sitemapDir}/blog.xml";
        $this->filesystem->dumpFile($filename, $blogSitemap);
    }

    public function buildVideosSitemap(): void
    {
        $videos = $this->youtubeVideoRepository->findAll();
        $videoSitemap = $this->twig->render('sitemap/videos.xml.twig', [
            'videos' => $videos,
        ]);
        $filename = "{$this->sitemapDir}/videos.xml";
        $this->filesystem->dumpFile($filename, $videoSitemap);
    }
}
