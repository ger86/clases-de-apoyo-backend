<?php

namespace App\Controller;

use App\Service\FileAccessResolver;
use App\Service\FileAi\GeminiFileTutorService;
use Aws\S3\S3Client;
use Sonata\MediaBundle\Provider\Pool;
use Psr\Http\Message\StreamInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileController extends AbstractController
{
    public function __construct(
        #[Autowire(env: 'S3_BUCKET_NAME')]
        private string $bucketName,
    ) {
    }

    public function viewer(
        string $fileId,
        FileAccessResolver $fileAccessResolver
    ): Response {
        $file = $fileAccessResolver->resolveOrThrow($fileId);

        return $this->render('views/files/viewer.html.twig', [
            'file' => $file,
        ]);
    }

    public function pdf(
        string $fileId,
        FileAccessResolver $fileAccessResolver,
        Pool $mediaService,
        S3Client $s3Client
    ): Response {
        $file = $fileAccessResolver->resolveOrThrow($fileId);
        $media = $file->getFile();

        if ($media === null) {
            throw new NotFoundHttpException('Este archivo no tiene un PDF asociado.');
        }

        $provider = $mediaService->getProvider($media->getProviderName());
        $key = $provider->generatePrivateUrl($media, 'reference');
        $result = $s3Client->getObject([
            'Bucket' => $this->bucketName,
            'Key' => $key,
        ]);

        $body = $result['Body'];
        \assert($body instanceof StreamInterface);

        $response = new StreamedResponse(
            static function () use ($body): void {
                while (!$body->eof()) {
                    echo $body->read(8192);
                }
            }
        );

        $filename = $this->buildDownloadFilename($file->getName() ?? (string) $file->getId());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', \sprintf('inline; filename="%s"', addslashes($filename)));
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        if (isset($result['ContentLength'])) {
            $response->headers->set('Content-Length', (string) $result['ContentLength']);
        }

        return $response;
    }

    public function askAi(
        string $fileId,
        Request $request,
        FileAccessResolver $fileAccessResolver,
        GeminiFileTutorService $geminiFileTutorService
    ): JsonResponse {
        if (!$this->isCsrfTokenValid(
            \sprintf('file_ai_chat_%s', $fileId),
            (string) $request->headers->get('X-CSRF-TOKEN')
        )) {
            return $this->json([
                'message' => 'La sesión del chat no es válida. Recarga la página e inténtalo de nuevo.',
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $file = $fileAccessResolver->resolveOrThrow($fileId);
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $messages = $this->normalizeMessages($payload['messages'] ?? null);

            return $this->json([
                'answer' => $geminiFileTutorService->generateAnswer($file, $messages),
            ]);
        } catch (NotFoundHttpException $exception) {
            return $this->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (AccessDeniedHttpException $exception) {
            return $this->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        } catch (\JsonException | \InvalidArgumentException $exception) {
            return $this->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $exception) {
            return $this->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (\Throwable) {
            return $this->json([
                'message' => 'No se ha podido consultar la IA en este momento. Inténtalo de nuevo dentro de unos minutos.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return array<int, array{role: string, text: string}>
     */
    private function normalizeMessages(mixed $messages): array
    {
        if (!\is_array($messages) || $messages === []) {
            throw new \InvalidArgumentException('Debes enviar al menos una pregunta sobre el documento.');
        }

        if (\count($messages) > 30) {
            throw new \InvalidArgumentException('La conversación es demasiado larga para esta primera versión.');
        }

        $normalizedMessages = [];

        foreach ($messages as $message) {
            if (!\is_array($message)) {
                throw new \InvalidArgumentException('El formato del historial del chat no es válido.');
            }

            $role = $message['role'] ?? null;
            $text = trim((string) ($message['text'] ?? ''));

            if (!\in_array($role, ['assistant', 'user'], true)) {
                throw new \InvalidArgumentException('El rol del mensaje no es válido.');
            }

            if ($text === '') {
                continue;
            }

            $normalizedMessages[] = [
                'role' => $role,
                'text' => mb_substr($text, 0, 4000),
            ];
        }

        if ($normalizedMessages === []) {
            throw new \InvalidArgumentException('Debes enviar al menos una pregunta sobre el documento.');
        }

        $hasUserMessage = false;

        foreach ($normalizedMessages as $message) {
            if ($message['role'] === 'user') {
                $hasUserMessage = true;
                break;
            }
        }

        if (!$hasUserMessage) {
            throw new \InvalidArgumentException('La conversación debe incluir al menos un mensaje del estudiante.');
        }

        return $normalizedMessages;
    }

    private function buildDownloadFilename(string $name): string
    {
        $filename = trim($name);
        if ($filename === '') {
            $filename = 'archivo.pdf';
        }

        if (!str_ends_with(mb_strtolower($filename), '.pdf')) {
            $filename .= '.pdf';
        }

        return str_replace(['"', '\\', "\r", "\n"], '', $filename);
    }
}
