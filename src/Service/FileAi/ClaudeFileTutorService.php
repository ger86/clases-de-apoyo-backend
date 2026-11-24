<?php

namespace App\Service\FileAi;

use Anthropic\Client;
use Anthropic\Core\Exceptions\AnthropicException;
use Anthropic\Core\Exceptions\NotFoundException;
use Anthropic\Core\FileParam;
use App\Entity\File;
use App\Entity\SonataMediaMedia;
use Aws\S3\S3Client;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Answers questions about one PDF with Claude.
 *
 * Cost levers: the PDF is uploaded once to the Files API and its id cached under
 * var/file-ai/, the system prompt is static and the PDF block carries a cache
 * breakpoint, so every later turn reads the PDF from the prompt cache at a tenth of the
 * input price. Thinking is off, effort is low and the answer is capped.
 */
final class ClaudeFileTutorService
{
    private const DEFAULT_ERROR_MESSAGE = 'No he podido sacar una respuesta útil del archivo. Prueba con otra pregunta sobre el documento.';
    private const MAX_OUTPUT_TOKENS = 2048;
    private const SYSTEM_PROMPT = <<<'PROMPT'
Eres un profesor particular para alumnado de 12 a 17 años.

Objetivo:
- Responder exclusivamente preguntas relacionadas con el archivo proporcionado.
- Explicar el contenido con claridad, pasos cortos y ejemplos sencillos cuando ayuden.

Idioma:
- Responde siempre en el mismo idioma que use el estudiante en su primer mensaje de la conversación.
- Si no puedes identificar con claridad ese idioma, usa español de España.

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

        $client = new Client(apiKey: trim($this->apiKey));

        try {
            $fileId = $this->resolveUploadedFileId($client, $file);

            try {
                return $this->requestAnswer($client, $file, $messages, $fileId);
            } catch (NotFoundException) {
                // The uploaded PDF was deleted on Anthropic's side: upload it again once.
                $this->clearUploadedFileCache($file);
                $fileId = $this->resolveUploadedFileId($client, $file);

                return $this->requestAnswer($client, $file, $messages, $fileId);
            }
        } catch (AnthropicException $exception) {
            throw new \RuntimeException('No se ha podido consultar la IA en este momento. Inténtalo de nuevo dentro de unos minutos.', previous: $exception);
        }
    }

    /**
     * @param array<int, array{role: string, text: string}> $messages
     */
    private function requestAnswer(Client $client, File $file, array $messages, string $fileId): string
    {
        $apiMessages = [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Este es el archivo que debes analizar. Úsalo como contexto obligatorio y mantén tu respuesta centrada en este documento.',
                    ],
                    [
                        'type' => 'text',
                        'text' => \sprintf('Nombre del archivo: %s.', $file->getName() ?? 'Documento PDF'),
                    ],
                    [
                        'type' => 'document',
                        'source' => [
                            'type' => 'file',
                            'fileID' => $fileId,
                        ],
                        'cacheControl' => [
                            'type' => 'ephemeral',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($messages as $message) {
            $apiMessages[] = [
                'role' => $message['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $message['text'],
            ];
        }

        $response = $client->messages->create(
            maxTokens: self::MAX_OUTPUT_TOKENS,
            messages: $apiMessages,
            model: $this->model,
            outputConfig: ['effort' => 'low'],
            system: self::SYSTEM_PROMPT,
            thinking: ['type' => 'disabled'],
        );

        if ($response->stopReason === 'refusal') {
            return self::DEFAULT_ERROR_MESSAGE;
        }

        $textParts = [];
        foreach ($response->content as $block) {
            if ($block->type === 'text') {
                $textParts[] = trim($block->text);
            }
        }

        $text = trim(implode("\n\n", array_filter($textParts, static fn(string $part): bool => $part !== '')));

        return $text !== '' ? $text : self::DEFAULT_ERROR_MESSAGE;
    }

    private function resolveUploadedFileId(Client $client, File $file): string
    {
        $cachePath = $this->getUploadedFileCachePath($file);
        if (is_file($cachePath)) {
            $payload = json_decode((string) file_get_contents($cachePath), true);
            if (\is_array($payload) && isset($payload['fileId']) && \is_string($payload['fileId']) && $payload['fileId'] !== '') {
                return $payload['fileId'];
            }

            $this->clearUploadedFileCache($file);
        }

        $uploaded = $client->files->upload(
            file: FileParam::fromString($this->downloadPdf($file->getFile(), $file->getName()), $this->buildDisplayName($file), 'application/pdf'),
        );

        $cacheDir = \dirname($cachePath);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0775, true);
        }

        file_put_contents($cachePath, json_encode(['fileId' => $uploaded->id], JSON_THROW_ON_ERROR), LOCK_EX);

        return $uploaded->id;
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
            '%s/var/file-ai/claude/%s.json',
            $this->projectDir,
            sha1(\sprintf('file-%s-media-%s', (string) $file->getId(), (string) $file->getFile()?->getId()))
        );
    }

    private function downloadPdf(?SonataMediaMedia $media, ?string $fileName): string
    {
        if ($media === null) {
            throw new \RuntimeException('Este archivo no tiene un PDF asociado.');
        }

        try {
            $provider = $this->mediaService->getProvider($media->getProviderName());
            $key = $provider->generatePrivateUrl($media, 'reference');

            $result = $this->s3Client->getObject([
                'Bucket' => $this->bucketName,
                'Key' => $key,
            ]);

            return (string) $result['Body'];
        } catch (\Throwable $exception) {
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
}
