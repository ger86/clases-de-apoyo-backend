<?php

namespace App\Service\FileAi;

/**
 * Cleans the chat history sent by the website and the app before it reaches Gemini.
 *
 * Every request re-sends the whole conversation, so the history is the part of the bill
 * that grows with each turn. Keeping only the last few messages bounds that cost while the
 * PDF itself stays cached on the Gemini side.
 */
final class FileTutorMessageNormalizer
{
    public const MAX_MESSAGES = 30;
    public const MAX_HISTORY_MESSAGES = 10;
    public const MAX_MESSAGE_LENGTH = 2000;

    /**
     * @return array<int, array{role: string, text: string}>
     */
    public function normalize(mixed $messages): array
    {
        if (!\is_array($messages) || $messages === []) {
            throw new \InvalidArgumentException('Debes enviar al menos una pregunta sobre el documento.');
        }

        if (\count($messages) > self::MAX_MESSAGES) {
            throw new \InvalidArgumentException('La conversación es demasiado larga. Cierra el chat y empieza uno nuevo.');
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
                'text' => mb_substr($text, 0, self::MAX_MESSAGE_LENGTH),
            ];
        }

        if ($normalizedMessages === []) {
            throw new \InvalidArgumentException('Debes enviar al menos una pregunta sobre el documento.');
        }

        $normalizedMessages = $this->keepRecentHistory($normalizedMessages);

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

    /**
     * Keeps the newest messages and makes sure the kept history starts with a student
     * message, because Gemini expects the conversation to open with the user turn.
     *
     * @param array<int, array{role: string, text: string}> $messages
     * @return array<int, array{role: string, text: string}>
     */
    private function keepRecentHistory(array $messages): array
    {
        if (\count($messages) > self::MAX_HISTORY_MESSAGES) {
            $messages = \array_slice($messages, -self::MAX_HISTORY_MESSAGES);
        }

        while ($messages !== [] && $messages[0]['role'] !== 'user') {
            array_shift($messages);
        }

        return array_values($messages);
    }
}
