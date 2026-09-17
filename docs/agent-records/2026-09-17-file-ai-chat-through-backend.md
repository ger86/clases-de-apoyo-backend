# File AI chat moved behind the backend, with usage limits

Date: 2026-09-17

## Why

The app (9.1.0) called Gemini directly with `@ai-sdk/google`. That had two problems: the API key travelled inside the app bundle as an `EXPO_PUBLIC_` variable, so anyone could extract it and spend on our account, and the whole PDF was sent as base64 with every question, so each turn of a conversation paid for the full document again. The old key was revoked and the new free-tier key now lives only in the server's `.env.local`.

## What changed

Backend:

- New endpoint `POST /api/files/{fileId}/ai-chat` in [src/Controller/Api/ApiFileController.php](../../src/Controller/Api/ApiFileController.php). It requires a logged in account, reuses `FileAccessResolver` so locked files answer 403, and returns `{answer}` or `{message, code}`.
- [src/Service/FileAi/FileTutorMessageNormalizer.php](../../src/Service/FileAi/FileTutorMessageNormalizer.php) replaces the private `normalizeMessages()` of `FileController`, so the website and the API validate the history identically. It also keeps only the last 10 messages, always starting with a student turn, and caps each message at 2,000 characters.
- Two rate limiters in `framework.yaml`: `file_ai_chat_user` (40 questions per account per day) and `file_ai_chat_ip` (30 per IP per hour). The API endpoint applies both; the website chat, which has no login, applies the IP one.
- `GeminiFileTutorService` now sends `maxOutputTokens: 2048` and, for `gemini-3*` models, `thinkingConfig.thinkingLevel: low`. Thinking tokens are billed as output and Gemini 3 defaults to high.

App (9.2.0):

- `src/services/fileTutor.ts` posts to the new endpoint through `apiFetch`, so the bearer token and `X-App-Version` header travel as everywhere else. `@ai-sdk/google` and `ai` were removed, together with `src/consts/env.ts`, `app.config.js` and `.env.example`, which only existed for the key.
- The file id is now passed from the chapter and exam lists through `FileScreen` to the chat modal. The modal no longer reads the PDF from disk.
- A guest opening the chat sees a login prompt instead of the composer.

## Cost model after this change

Per question: the cached PDF reference (no re-upload), the system prompt, at most 10 history messages and at most 2,048 output tokens. The Files API keeps an upload for 48 hours; the service re-uploads when the cached reference is near expiry.

## Open points

- The free tier lets Google use prompts and files to improve its models. Student PDFs are our own teaching material, but this should be decided explicitly before a paid tier is needed.
- Limits (40/day, 30/hour) are a first guess; tune them from the `limiter` cache keys or add a small usage log if it becomes a support question.
- `composer check` could not be run on the developer machine (no PHP). PHPUnit, PHPStan, ECS, `lint:yaml` and `lint:container` were run inside a Docker container (`composer:2` image) for the touched files; the full suite still has to run in CI.
