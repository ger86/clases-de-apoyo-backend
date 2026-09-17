<?php

namespace App\Controller\Api;

use App\Service\FileAccessResolver;
use App\Service\FileAi\FileTutorMessageNormalizer;
use App\Service\FileAi\GeminiFileTutorService;
use App\Service\Security;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Chat about a PDF from the app. The app used to call Gemini itself with a key baked
 * into the bundle and the whole PDF re-sent on every turn. Going through here keeps the
 * key on the server, reuses the PDF already uploaded to Gemini and lets us cap usage per
 * account.
 */
final class ApiFileController extends AbstractFOSRestController
{
    #[Post(path: '/files/{fileId}/ai-chat', requirements: ['fileId' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function askAiAction(
        string $fileId,
        Request $request,
        Security $security,
        FileAccessResolver $fileAccessResolver,
        FileTutorMessageNormalizer $messageNormalizer,
        GeminiFileTutorService $geminiFileTutorService,
        #[Autowire(service: 'limiter.file_ai_chat_user')]
        RateLimiterFactoryInterface $userLimiter,
        #[Autowire(service: 'limiter.file_ai_chat_ip')]
        RateLimiterFactoryInterface $ipLimiter
    ): View {
        $user = $security->getSafeUser();

        if (!$ipLimiter->create((string) $request->getClientIp())->consume()->isAccepted()) {
            return $this->error(
                'Has hecho muchas preguntas seguidas. Espera un rato antes de volver a preguntar.',
                'rate_limited',
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }

        if (!$userLimiter->create((string) $user->getId())->consume()->isAccepted()) {
            return $this->error(
                'Has llegado al límite diario de preguntas a la IA. Podrás volver a preguntar mañana.',
                'daily_limit_reached',
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }

        try {
            $file = $fileAccessResolver->resolveOrThrow($fileId);
            $messages = $messageNormalizer->normalize($request->getPayload()->all('messages'));

            return $this->view([
                'answer' => $geminiFileTutorService->generateAnswer($file, $messages),
            ]);
        } catch (NotFoundHttpException $exception) {
            return $this->error($exception->getMessage(), 'not_found', Response::HTTP_NOT_FOUND);
        } catch (AccessDeniedHttpException $exception) {
            return $this->error($exception->getMessage(), 'locked', Response::HTTP_FORBIDDEN);
        } catch (\InvalidArgumentException | \UnexpectedValueException $exception) {
            return $this->error($exception->getMessage(), 'invalid_payload', Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $exception) {
            return $this->error($exception->getMessage(), 'ai_unavailable', Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (\Throwable) {
            return $this->error(
                'No se ha podido consultar la IA en este momento. Inténtalo de nuevo dentro de unos minutos.',
                'ai_error',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function error(string $message, string $code, int $status): View
    {
        return $this->view(['message' => $message, 'code' => $code], $status);
    }
}
