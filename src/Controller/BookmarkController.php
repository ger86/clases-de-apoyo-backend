<?php

namespace App\Controller;

use App\Service\Bookmark\GetBookmarksView;
use App\Service\Bookmark\UserBookmarkManager;
use App\Service\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * The saved files on the website. It uses the same service and table as the app, so a file
 * saved on the phone shows up here and the other way round. Every route lives under
 * /usuario, which security.yaml already keeps for logged-in users.
 */
final class BookmarkController extends AbstractController
{
    public function list(Security $security, GetBookmarksView $getBookmarksView): Response
    {
        return $this->render('views/bookmarks/list.html.twig', [
            'bookmarks' => ($getBookmarksView)($security->getSafeUser())->bookmarks,
        ]);
    }

    public function add(string $fileId, Request $request, Security $security, UserBookmarkManager $manager): Response
    {
        $this->checkToken($fileId, $request);
        $manager->add($security->getSafeUser(), (int) $fileId);

        return $this->redirectBack($fileId, $request);
    }

    public function remove(string $fileId, Request $request, Security $security, UserBookmarkManager $manager): Response
    {
        $this->checkToken($fileId, $request);
        $manager->remove($security->getSafeUser(), (int) $fileId);

        return $this->redirectBack($fileId, $request);
    }

    private function checkToken(string $fileId, Request $request): void
    {
        if (!$this->isCsrfTokenValid('bookmark_' . $fileId, (string) $request->request->get('_token'))) {
            throw new AccessDeniedHttpException('La sesión no es válida. Recarga la página e inténtalo de nuevo.');
        }
    }

    /** Only two places hold the button, so the target is picked from a fixed list, never from a URL. */
    private function redirectBack(string $fileId, Request $request): Response
    {
        if ($request->request->get('return') === 'list') {
            return $this->redirectToRoute('app_bookmarks');
        }

        return $this->redirectToRoute('file', ['fileId' => $fileId]);
    }
}
