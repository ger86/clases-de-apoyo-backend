<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends AbstractController
{
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('views/blog/index/index.html.twig', ['articles' => $articles]);
    }

    public function article($slug, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->findOneBy(['slug' => $slug]);
        if ($article === null) {
            throw $this->createNotFoundException('The article does not exist');
        }
        return $this->render('views/blog/article/article.html.twig', ['article' => $article]);
    }
}
