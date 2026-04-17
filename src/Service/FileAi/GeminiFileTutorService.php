<?php

namespace App\Service\FileAi;

use App\Entity\File;
use App\Entity\SonataMediaMedia;
use Aws\S3\S3Client;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class GeminiFileTutorService
{
    private const DEFAULT_ERROR_MESSAGE = 'No he podido sacar una respuesta útil del archivo. Prueba con otra pregunta sobre el documento.';
    private const FILE_CACHE_BUFFER_SECONDS = 300;
    private const GEMINI_API_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';
    private const GEMINI_UPLOAD_BASE_URL = 'https://generativelanguage.googleapis.com/upload/v1beta/files';
    private const MAX_FILE_PROCESSING_SECONDS = 20;

    public function __construct(
        private S3Client $s3Client,
        private Pool $mediaService,
        #[Autowire(env: 'S3_BUCKET_NAME')]
        private string $bucketName,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
        #[Autowire(env: 'FILE_AI_API_KEY')]
        private string $apiKey,
        #[Autowire(env: 'FILE_AI_MODEL')]
        private string $model
    ) {
    }

    /**
     * @param array<int, array{role: string, text: string}> $messages
     */
    public function generateAnswer(File $file, array $messages): string
    {
        if (trim($this->apiKey) === '') {
            throw new \RuntimeException('Falta configurar FILE_AI_API_KEY para usar el chat del archivo.');
        }

        if (!\function_exists('curl_init')) {
            throw new \RuntimeException('La extensión cURL es obligatoria para consultar Gemini desde el backend.');
        }

        $uploadedFile = $this->resolveUploadedFile($file);

        try {
            return $this->requestAnswer($file, $messages, $uploadedFile);
        } catch (\RuntimeException $exception) {
            if (!$this->shouldRefreshCachedFile($exception->getMessage())) {
                throw $exception;
            }

            $this->clearUploadedFileCache($file);
            $uploadedFile = $this->resolveUploadedFile($file);

            return $this->requestAnswer($file, $messages, $uploadedFile);
        }
    }

    /**
     * @param array<int, array{role: string, text: string}> $messages
     * @param array{name: string, uri: string, mimeType: string, expirationTime: string|null} $uploadedFile
     */
    private function requestAnswer(File $file, array $messages, array $uploadedFile): string
    {
        $response = $this->requestJson(
            'POST',
            $this->buildApiUrl(\sprintf('models/%s:generateContent', $this->model)),
            [
                'system_instruction' => [
                    'parts' => [
                        [
                            'text' => $this->buildSystemPrompt($messages),
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.2,
                ],
                'contents' => $this->buildContents($file, $messages, $uploadedFile),
            ]
        );

        $textParts = [];

        foreach (($response['candidates'][0]['content']['parts'] ?? []) as $part) {
            if (\is_array($part) && isset($part['text']) && \is_string($part['text'])) {
                $textParts[] = trim($part['text']);
            }
        }

        $text = trim(implode("\n\n", array_filter($textParts, static fn(string $textPart): bool => $textPart !== '')));

        return $text !== '' ? $text : self::DEFAULT_ERROR_MESSAGE;
    }

    /**
     * @param array<int, array{role: string, text: string}> $messages
     */
    private function buildSystemPrompt(array $messages): string
    {
        $firstUserMessage = '';

        foreach ($messages as $message) {
            if ($message['role'] === 'user') {
                $firstUserMessage = trim($message['text']);
                break;
            }
        }

        return <<<PROMPT
Eres un profesor particular para alumnado de 12 a 17 años.

Objetivo:
- Responder exclusivamente preguntas relacionadas con el archivo proporcionado.
- Explicar el contenido con claridad, pasos cortos y ejemplos sencillos cuando ayuden.

Idioma:
- Debes responder siempre en el mismo idioma que use el estudiante en su primer mensaje.
- El primer mensaje del estudiante en esta conversación es: "{$firstUserMessage}".
- Si no puedes identificar con claridad el idioma de ese primer mensaje, usa español de España.

Límites:
- No respondas preguntas ajenas al archivo, aunque sean académicas o de cultura general.
- Si la pregunta no se puede contestar usando el archivo, responde de forma breve que no aparece en el archivo y pide una duda relacionada con ese documento.
- No inventes datos ni completes huecos con conocimiento externo sin avisar.

Tono:
- Habla como un profesor paciente, claro y cercano.
- Ajusta el nivel para estudiantes de 12 a 17 años.
- Prioriza respuestas concretas y fáciles de seguir.

Formato:
- Escribe el texto normal de forma limpia, sin encabezados Markdown, sin negritas, sin tablas y sin bloques de código.
- Cuando haya expresiones matemáticas, usa LaTeX para que se puedan mostrar bien en la app.
- Usa $...$ para fórmulas cortas en línea y $$...$$ para fórmulas en una línea separada.
- No escapes el LaTeX como texto literal ni lo pongas entre comillas o backticks.
- Si una explicación no necesita fórmulas, no fuerces el uso de LaTeX.
PROMPT;
    }

    /**
     * @param array<int, array{role: string, text: string}> $messages
     * @param array{name: string, uri: string, mimeType: string, expirationTime: string|null} $uploadedFile
     * @return array<int, array{role: string, parts: array<int, array<string, mixed>>}>
     */
    private function buildContents(File $file, array $messages, array $uploadedFile): array
    {
        $contents = [
            [
                'role' => 'user',
                'parts' => [
                    [
                        'text' => 'Este es el archivo que debes analizar. Úsalo como contexto obligatorio y mantén tu respuesta centrada en este documento.',
                    ],
                    [
                        'text' => \sprintf('Nombre del archivo: %s.', $file->getName() ?? 'Documento PDF'),
                    ],
                    [
                        'file_data' => [
                            'mime_type' => $uploadedFile['mimeType'],
                            'file_uri' => $uploadedFile['uri'],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($messages as $message) {
            $contents[] = [
                'role' => $message['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [
                    [
                        'text' => $message['text'],
                    ],
                ],
            ];
        }

        return $contents;
    }

    /**
     * @return array{name: string, uri: string, mimeType: string, expirationTime: string|null}
     */
    private function resolveUploadedFile(File $file): array
    {
        $cachedFile = $this->readUploadedFileCache($file);
        if ($cachedFile !== null) {
            return $cachedFile;
        }

        $uploadedFile = $this->uploadFileToGemini($file);
        $normalizedFile = $this->normalizeUploadedFile($uploadedFile);

        $this->writeUploadedFileCache($file, $normalizedFile);

        return $normalizedFile;
    }

    /**
     * @return array{name: string, uri: string, mimeType: string, expirationTime: string|null}
     */
    private function readUploadedFileCache(File $file): ?array
    {
        $cachePath = $this->getUploadedFileCachePath($file);
        if (!is_file($cachePath)) {
            return null;
        }

        $payload = json_decode((string) file_get_contents($cachePath), true);
        if (!\is_array($payload)) {
            $this->clearUploadedFileCache($file);
            return null;
        }

        if (!isset($payload['name'], $payload['uri'], $payload['mimeType']) || !\is_string($payload['name']) || !\is_string($payload['uri']) || !\is_string($payload['mimeType'])) {
            $this->clearUploadedFileCache($file);
            return null;
        }

        $expirationTime = isset($payload['expirationTime']) && \is_string($payload['expirationTime'])
            ? $payload['expirationTime']
            : null;

        if ($this->isNearExpiration($expirationTime)) {
            $this->clearUploadedFileCache($file);
            return null;
        }

        return [
            'name' => $payload['name'],
            'uri' => $payload['uri'],
            'mimeType' => $payload['mimeType'],
            'expirationTime' => $expirationTime,
        ];
    }

    /**
     * @param array{name: string, uri: string, mimeType: string, expirationTime: string|null} $uploadedFile
     */
    private function writeUploadedFileCache(File $file, array $uploadedFile): void
    {
        $cacheDir = \dirname($this->getUploadedFileCachePath($file));
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0775, true);
        }

        file_put_contents(
            $this->getUploadedFileCachePath($file),
            json_encode($uploadedFile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }

    private function clearUploadedFileCache(File $file): void
    {
        $cachePath = $this->getUploadedFileCachePath($file);
        if (is_file($cachePath)) {
            unlink($cachePath);
        }
    }

    private function getUploadedFileCachePath(File $file): string
    {
        return \sprintf(
            '%s/var/file-ai/%s.json',
            $this->projectDir,
            sha1(\sprintf('file-%s-media-%s', (string) $file->getId(), (string) $file->getFile()?->getId()))
        );
    }

    /**
     * @return array{name: string, uri: string, mimeType: string, expirationTime: string|null}
     */
    private function uploadFileToGemini(File $file): array
    {
        $temporaryFilePath = $this->downloadPdfToTemporaryPath($file->getFile(), $file->getName());
        $displayName = $this->buildDisplayName($file);

        try {
            $startUploadResponse = $this->sendRequest(
                'POST',
                $this->buildUploadUrl(),
                [
                    'Content-Type' => 'application/json',
                    'X-Goog-Upload-Protocol' => 'resumable',
                    'X-Goog-Upload-Command' => 'start',
                    'X-Goog-Upload-Header-Content-Length' => (string) filesize($temporaryFilePath),
                    'X-Goog-Upload-Header-Content-Type' => 'application/pdf',
                ],
                json_encode([
                    'file' => [
                        'display_name' => $displayName,
                    ],
                ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );

            $uploadUrl = $startUploadResponse['headers']['x-goog-upload-url'][0] ?? null;
            if (!\is_string($uploadUrl) || $uploadUrl === '') {
                throw new \RuntimeException('No se ha podido iniciar la subida del PDF a Gemini.');
            }

            $fileInfoResponse = $this->sendRequest(
                'POST',
                $uploadUrl,
                [
                    'Content-Length' => (string) filesize($temporaryFilePath),
                    'Content-Type' => 'application/pdf',
                    'X-Goog-Upload-Offset' => '0',
                    'X-Goog-Upload-Command' => 'upload, finalize',
                ],
                $this->readTemporaryFileContents($temporaryFilePath)
            );

            $payload = $this->decodeJsonBody($fileInfoResponse['body']);
            $uploadedFile = $payload['file'] ?? null;
            if (!\is_array($uploadedFile)) {
                throw new \RuntimeException('Gemini no ha devuelto la referencia del archivo subido.');
            }

            return $this->waitUntilFileIsReady($uploadedFile);
        } finally {
            if (is_file($temporaryFilePath)) {
                unlink($temporaryFilePath);
            }
        }
    }

    /**
     * @param array<string, mixed> $uploadedFile
     * @return array<string, mixed>
     */
    private function waitUntilFileIsReady(array $uploadedFile): array
    {
        $state = isset($uploadedFile['state']) && \is_string($uploadedFile['state'])
            ? strtoupper($uploadedFile['state'])
            : 'ACTIVE';

        if ($state === 'FAILED') {
            throw new \RuntimeException('Gemini no ha podido procesar el PDF.');
        }

        if ($state !== 'PROCESSING') {
            return $uploadedFile;
        }

        $name = isset($uploadedFile['name']) && \is_string($uploadedFile['name']) ? $uploadedFile['name'] : null;
        if ($name === null || $name === '') {
            throw new \RuntimeException('Gemini no ha devuelto el identificador del PDF subido.');
        }

        $startedAt = time();

        do {
            sleep(1);
            $response = $this->requestJson('GET', $this->buildApiUrl($name));
            $state = isset($response['state']) && \is_string($response['state'])
                ? strtoupper($response['state'])
                : 'ACTIVE';

            if ($state === 'FAILED') {
                throw new \RuntimeException('Gemini no ha podido procesar el PDF.');
            }

            if ($state !== 'PROCESSING') {
                return $response;
            }
        } while ((time() - $startedAt) < self::MAX_FILE_PROCESSING_SECONDS);

        throw new \RuntimeException('Gemini está tardando demasiado en preparar el PDF. Inténtalo de nuevo.');
    }

    private function downloadPdfToTemporaryPath(?SonataMediaMedia $media, ?string $fileName): string
    {
        if ($media === null) {
            throw new \RuntimeException('Este archivo no tiene un PDF asociado.');
        }

        $temporaryDirectory = \sprintf('%s/var/tmp', $this->projectDir);
        if (!is_dir($temporaryDirectory)) {
            mkdir($temporaryDirectory, 0775, true);
        }

        $temporaryFilePath = tempnam($temporaryDirectory, 'file-ai-');
        if ($temporaryFilePath === false) {
            throw new \RuntimeException('No se ha podido preparar el PDF para la consulta con IA.');
        }

        try {
            $provider = $this->mediaService->getProvider($media->getProviderName());
            $key = $provider->generatePrivateUrl($media, 'reference');

            $this->s3Client->getObject([
                'Bucket' => $this->bucketName,
                'Key' => $key,
                'SaveAs' => $temporaryFilePath,
            ]);

            return $temporaryFilePath;
        } catch (\Throwable $exception) {
            if (is_file($temporaryFilePath)) {
                unlink($temporaryFilePath);
            }

            throw new \RuntimeException(\sprintf(
                'No se ha podido recuperar el PDF "%s" desde el servidor.',
                $fileName ?? 'sin nombre'
            ), previous: $exception);
        }
    }

    private function buildDisplayName(File $file): string
    {
        $name = trim((string) $file->getName());
        if ($name === '') {
            $name = \sprintf('archivo-%s', (string) $file->getId());
        }

        if (!str_ends_with(mb_strtolower($name), '.pdf')) {
            $name .= '.pdf';
        }

        return mb_substr($name, 0, 120);
    }

    private function readTemporaryFileContents(string $temporaryFilePath): string
    {
        $contents = file_get_contents($temporaryFilePath);
        if ($contents === false) {
            throw new \RuntimeException('No se ha podido leer el PDF temporal antes de subirlo a Gemini.');
        }

        return $contents;
    }

    private function buildApiUrl(string $path): string
    {
        return \sprintf('%s/%s?key=%s', self::GEMINI_API_BASE_URL, ltrim($path, '/'), rawurlencode($this->apiKey));
    }

    private function buildUploadUrl(): string
    {
        return \sprintf('%s?key=%s', self::GEMINI_UPLOAD_BASE_URL, rawurlencode($this->apiKey));
    }

    private function shouldRefreshCachedFile(string $errorMessage): bool
    {
        $normalizedMessage = mb_strtolower($errorMessage);

        return str_contains($normalizedMessage, 'file_uri')
            || str_contains($normalizedMessage, 'files/')
            || str_contains($normalizedMessage, 'not found')
            || str_contains($normalizedMessage, 'expired');
    }

    private function isNearExpiration(?string $expirationTime): bool
    {
        if ($expirationTime === null || $expirationTime === '') {
            return false;
        }

        try {
            $expiresAt = new \DateTimeImmutable($expirationTime);
        } catch (\Exception) {
            return true;
        }

        return $expiresAt->getTimestamp() <= (time() + self::FILE_CACHE_BUFFER_SECONDS);
    }

    /**
     * @param array<string, mixed> $uploadedFile
     * @return array{name: string, uri: string, mimeType: string, expirationTime: string|null}
     */
    private function normalizeUploadedFile(array $uploadedFile): array
    {
        $name = isset($uploadedFile['name']) && \is_string($uploadedFile['name']) ? $uploadedFile['name'] : null;
        $uri = isset($uploadedFile['uri']) && \is_string($uploadedFile['uri']) ? $uploadedFile['uri'] : null;
        $mimeType = isset($uploadedFile['mimeType']) && \is_string($uploadedFile['mimeType'])
            ? $uploadedFile['mimeType']
            : 'application/pdf';
        $expirationTime = isset($uploadedFile['expirationTime']) && \is_string($uploadedFile['expirationTime'])
            ? $uploadedFile['expirationTime']
            : null;

        if ($name === null || $uri === null) {
            throw new \RuntimeException('Gemini no ha devuelto una referencia válida para reutilizar el PDF.');
        }

        return [
            'name' => $name,
            'uri' => $uri,
            'mimeType' => $mimeType,
            'expirationTime' => $expirationTime,
        ];
    }

    /**
     * @param array<string, string> $headers
     * @return array{statusCode: int, headers: array<string, array<int, string>>, body: string}
     */
    private function sendRequest(string $method, string $url, array $headers = [], ?string $body = null): array
    {
        $handle = curl_init($url);
        if ($handle === false) {
            throw new \RuntimeException('No se ha podido inicializar la conexión con Gemini.');
        }

        $responseHeaders = [];
        $formattedHeaders = [];

        foreach ($headers as $name => $value) {
            $formattedHeaders[] = \sprintf('%s: %s', $name, $value);
        }

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($handle, CURLOPT_TIMEOUT, 120);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $formattedHeaders);
        curl_setopt($handle, CURLOPT_HEADERFUNCTION, static function ($curlHandle, $headerLine) use (&$responseHeaders) {
            $trimmedHeader = trim($headerLine);
            if ($trimmedHeader === '' || !str_contains($trimmedHeader, ':')) {
                return \strlen($headerLine);
            }

            [$headerName, $headerValue] = explode(':', $trimmedHeader, 2);
            $normalizedHeaderName = strtolower(trim($headerName));
            $responseHeaders[$normalizedHeaderName] ??= [];
            $responseHeaders[$normalizedHeaderName][] = trim($headerValue);

            return \strlen($headerLine);
        });

        if ($body !== null) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, $body);
        }

        $responseBody = curl_exec($handle);

        if ($responseBody === false) {
            $errorMessage = curl_error($handle);
            if ($errorMessage === '') {
                $errorMessage = 'Error desconocido de conexión';
            }
            curl_close($handle);

            throw new \RuntimeException(\sprintf('No se ha podido conectar con Gemini: %s.', $errorMessage));
        }

        $statusCode = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new \RuntimeException($this->buildApiErrorMessage($statusCode, $responseBody));
        }

        return [
            'statusCode' => $statusCode,
            'headers' => $responseHeaders,
            'body' => $responseBody,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function requestJson(string $method, string $url, array $payload = []): array
    {
        $response = $this->sendRequest(
            $method,
            $url,
            [
                'Content-Type' => 'application/json',
            ],
            $method === 'GET'
                ? null
                : json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        return $this->decodeJsonBody($response['body']);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonBody(string $body): array
    {
        if (trim($body) === '') {
            return [];
        }

        $payload = json_decode($body, true);
        if (!\is_array($payload)) {
            throw new \RuntimeException('Gemini ha devuelto una respuesta con un formato inesperado.');
        }

        return $payload;
    }

    private function buildApiErrorMessage(int $statusCode, string $body): string
    {
        $defaultMessage = \sprintf('Gemini ha respondido con un error (%d).', $statusCode);

        $payload = json_decode($body, true);
        if (!\is_array($payload)) {
            return $defaultMessage;
        }

        $message = $payload['error']['message'] ?? $payload['message'] ?? null;
        if (!\is_string($message) || trim($message) === '') {
            return $defaultMessage;
        }

        return trim($message);
    }
}
