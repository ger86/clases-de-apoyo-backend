<?php

namespace App\Controller\Api;

use App\Service\Bookmark\GetBookmarksView;
use App\Service\Bookmark\UserBookmarkManager;
use App\Service\Security;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * The files a student saved. Everything here needs an account: the bookmarks of a guest
 * stay on the phone until they log in, and the app then merges them.
 */
final class ApiMeBookmarksController extends AbstractFOSRestController
{
    #[Get(path: '/me/bookmarks')]
    #[IsGranted('ROLE_USER')]
    public function listAction(Security $security, GetBookmarksView $getBookmarksView): View
    {
        return $this->view(($getBookmarksView)($security->getSafeUser()));
    }

    #[Put(path: '/me/bookmarks')]
    #[IsGranted('ROLE_USER')]
    public function mergeAction(
        Request $request,
        Security $security,
        UserBookmarkManager $manager,
        GetBookmarksView $getBookmarksView
    ): View {
        $user = $security->getSafeUser();

        try {
            $manager->merge($user, $request->getPayload()->all('files'));
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 'invalid_payload', Response::HTTP_BAD_REQUEST);
        }

        return $this->view(($getBookmarksView)($user));
    }

    #[Put(path: '/me/bookmarks/file/{id}', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function addAction(string $id, Security $security, UserBookmarkManager $manager): View
    {
        try {
            $manager->add($security->getSafeUser(), (int) $id);
        } catch (NotFoundHttpException $exception) {
            return $this->error($exception->getMessage(), 'not_found', Response::HTTP_NOT_FOUND);
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    #[Delete(path: '/me/bookmarks/file/{id}', requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function removeAction(string $id, Security $security, UserBookmarkManager $manager): View
    {
        $manager->remove($security->getSafeUser(), (int) $id);

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    private function error(string $message, string $code, int $status): View
    {
        return $this->view(['message' => $message, 'code' => $code], $status);
    }
}
