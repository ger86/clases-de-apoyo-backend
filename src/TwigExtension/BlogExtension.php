<?php

namespace App\TwigExtension;

use App\Repository\ArticleRepository;
use Twig\{Environment, TwigFunction};
use Twig\Extension\AbstractExtension;

class BlogExtension extends AbstractExtension
{

    public function __construct(private ArticleRepository $articleRepository)
    {
    }

    public function getName()
    {
        return 'blog';
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('renderLastArticle', [$this, 'renderLastArticle'], [
                'is_safe' => ['html'],
                'needs_environment' => true
            ])
        ];
    }

    public function renderLastArticle(Environment $twig): ?string
    {
        $lastArticles = $this->articleRepository->findBy(
            [],
            ['createdAt' => 'DESC'],
            2
        );
        if (\count($lastArticles) === 0) {
            return null;
        }
        return $twig->render('common/blog/articles_teasers.html.twig', [
            'lastArticles' => $lastArticles
        ]);
    }
}
