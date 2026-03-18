<?php

namespace App\Controller;

use App\Repository\YoutubeVideoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class YoutubeVideoController extends AbstractController
{

    public function video($videoSlug, YoutubeVideoRepository $youtubeVideoRepository): Response
    {
        $video = $youtubeVideoRepository->findOneBy(['slug' => $videoSlug]);
        if ($video === null) {
            throw $this->createNotFoundException('No existe ese video');
        }
        return $this->render('videos/video/video.html.twig', ['video' => $video]);
    }
}
