<?php

namespace App\Controller\Api;

use App\Model\ProgressKind;
use App\Model\ProgressTarget;
use App\Service\Progress\GetProgressView;
use App\Service\Progress\UserProgressManager;
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
 * The chapters and exams a student marked as done. Everything here needs an account:
 * the marks of a guest stay on the phone until they log in, and the app then merges them.
 */
final class ApiMeProgressController extends AbstractFOSRestController
{
    private const KIND_PATTERN = 'chapter|exam';

    #[Get(path: '/me/progress')]
    #[IsGranted('ROLE_USER')]
    public function listAction(Security $security, GetProgressView $getProgressView): View
    {
        return $this->view(($getProgressView)($security->getSafeUser()));
    }

    #[Put(path: '/me/progress')]
    #[IsGranted('ROLE_USER')]
    public function mergeAction(
        Request $request,
        Security $security,
        UserProgressManager $manager,
        GetProgressView $getProgressView
    ): View {
        $user = $security->getSafeUser();

        try {
            $manager->merge($user, $request->getPayload()->all('done'));
        } catch (InvalidArgumentException $exception) {
            return $this->error($exception->getMessage(), 'invalid_payload', Response::HTTP_BAD_REQUEST);
        }

        return $this->view(($getProgressView)($user));
    }

    #[Put(path: '/me/progress/{kind}/{id}', requirements: ['kind' => self::KIND_PATTERN, 'id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function markAction(string $kind, string $id, Security $security, UserProgressManager $manager): View
    {
        try {
            $manager->markDone($security->getSafeUser(), new ProgressTarget(ProgressKind::from($kind), (int) $id));
        } catch (NotFoundHttpException $exception) {
            return $this->error($exception->getMessage(), 'not_found', Response::HTTP_NOT_FOUND);
        }

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    #[Delete(path: '/me/progress/{kind}/{id}', requirements: ['kind' => self::KIND_PATTERN, 'id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function unmarkAction(string $kind, string $id, Security $security, UserProgressManager $manager): View
    {
        $manager->unmark($security->getSafeUser(), new ProgressTarget(ProgressKind::from($kind), (int) $id));

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    private function error(string $message, string $code, int $status): View
    {
        return $this->view(['message' => $message, 'code' => $code], $status);
    }
}
