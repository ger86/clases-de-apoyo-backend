# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Clases de Apoyo is a PHP 8.4 / Symfony 8.0 platform that serves educational content (subjects, courses, tests, files) both as a Twig + Tailwind website and as a RESTful API for a mobile app. Payments run through Stripe, files are stored in AWS S3, and the admin backend is built with Sonata Admin.

## Running commands

All commands must run inside the `php` Docker container. Start it first if needed:

```bash
cd .docker && docker-compose up -d php
docker-compose exec php <command>
```

The web app is served by the `nginx` container on `http://localhost:8080`; MySQL 8 is exposed on host port 3307 (`db_user` / `hola1234`, database `clasesdeapoyo`).

## Quality gate

Before finishing any task or opening a PR, run:

```bash
docker-compose exec php composer ci
```

This runs, in order: ECS auto-fix, PHPStan analysis, `doctrine:schema:validate`, `lint:container`, `lint:twig templates`, `lint:yaml config/translations`, and PHPUnit. All steps must pass.

Individual checks: `composer cs` (ECS check-only), `composer stan` (PHPStan), `bin/phpunit` (tests). Run a single test with `bin/phpunit --filter TestName path/to/TestFile.php`.

## Doctrine migrations

Schema changes must go through migrations — never edit the database or entities without a matching migration. After changing an entity:

```bash
bin/console doctrine:migrations:diff      # generate
# review the new file under migrations/
bin/console doctrine:migrations:migrate   # apply
```

Migrations live in [migrations/](migrations/) (top-level, not `src/Migrations/`). `doctrine:schema:validate` runs as part of `composer ci`.

## Architecture

### Dual delivery: API and web

- API controllers live in [src/Controller/Api/](src/Controller/Api/) and use FOSRestBundle. Content endpoints stay readable without an account, exactly like the website; account and purchase endpoints require a bearer token. See "Mobile app API" below.
- Web controllers live directly in [src/Controller/](src/Controller/) and rely on Symfony Security (form login + remember-me sessions). See [config/packages/security.yaml](config/packages/security.yaml) for access rules — `/admin/*` requires `ROLE_ADMIN`, `/usuario/*` requires `ROLE_USER`, everything else is public.
- Subscription/billing flows are split into single-action controllers under [src/Controller/Subscription/](src/Controller/Subscription/), each backed by a service in [src/Service/Stripe/](src/Service/Stripe/).

### Layering rules (enforced by convention, not tooling)

- **Controllers are thin** — they handle HTTP only. Business logic belongs in [src/Service/](src/Service/).
- **All database queries go in repositories** ([src/Repository/](src/Repository/)). No DQL or QueryBuilder in services or controllers.
- **API responses are typed view objects** in [src/Model/View/](src/Model/View/) (readonly classes, e.g. `ChapterView`). Controllers return these, and FOSRestBundle serializes them. There are matching `Get*View` services (e.g. [src/Service/GetChapterView.php](src/Service/GetChapterView.php)) that build the view object from an entity — follow this pattern when adding new API endpoints.
- **DTOs for input** live in [src/Model/Dto/](src/Model/Dto/).

### Admin

Sonata Admin classes in [src/Admin/](src/Admin/) are auto-registered; each wraps one entity and is reachable under `/admin`. When adding an entity that needs back-office management, add a matching `*Admin` class.

### Code style specifics (from ecs.php / copilot-instructions.md)

- Always declare return types and property types; no `@var` annotations for types.
- Use constructor property promotion, `final` classes by default, strict comparisons, early returns.
- Omit the caught variable when unused: `catch (SomeException) { ... }`.
- Short array syntax; `MethodArgumentSpaceFixer` enforces fully-multiline args when wrapped.
- `NativeFunctionInvocationFixer` prefixes compiler-optimized built-ins with `\` in namespaced code — let ECS apply this rather than writing it manually.
- PHPStan runs at level 2 with the Symfony + Doctrine + PHPUnit + strict-rules extensions.

## Mobile app API

The app in `../clases-de-apoyo-app` shares accounts and subscriptions with the website.

- **Auth**: opaque bearer tokens in the `api_token` table, stored as a sha256 hash, 60-day sliding expiry. [src/Security/ApiTokenAuthenticator.php](src/Security/ApiTokenAuthenticator.php) runs on the `api` firewall, which is declared **above** `main` in [config/packages/security.yaml](config/packages/security.yaml) because `main` matches `^/`. A request with no `Authorization` header stays anonymous, so free content is still readable without an account.
- **Endpoints**: `/api/auth/{register,login,logout,password-reset}`, `/api/me` (GET and DELETE, the latter required by App Store guideline 5.1.1(v)), `/api/me/progress` (the chapters and exams marked as done: GET, PUT to merge a guest's marks, PUT and DELETE on `/{kind}/{id}`), `/api/app-config`, `/api/search-index`, `/api/apple/transactions`, `/api/apple/notifications`.
- **Gating**: `GetFileView` and `GetExamTeaserView` ask `PremiumService` the same questions the Twig templates ask, then return a `locked` flag and drop the signed S3 URL. Never reimplement the free/premium rules anywhere else.
- **`APP_API_GATING_ENABLED`**: leave it off until the new app version is live. Even when it is on, a client that sends no `X-App-Version` header still receives the URL, because app 8.3.0 cannot render a locked file. Those installs are pushed to update through `MOBILE_MIN_SUPPORTED_VERSION` and `MOBILE_STORE_URL`, both served by `/api/app-config`.
- **Apple**: [src/Service/Apple/](src/Service/Apple/) verifies transactions and App Store Server Notifications V2 with `readdle/app-store-server-api` against the root certificate in `config/apple/`. A purchase is tied to an account through `appAccountToken`, a UUID the app passes to StoreKit. Renewals only arrive through the notifications endpoint, so it is as important as the Stripe webhook.
- **Search index**: `/api/search-index` is the flat catalogue the app puts in the phone search (Spotlight on iOS). It answers with a version, and a request carrying `?since=<version>` gets `changed: false` and nothing to download while the catalogue has not moved. It holds no download link and no `locked` flag, because that index is written once for everybody: the app opens the exam or the chapter screen and the gating above decides there. `GetSearchIndexView` caches the whole answer for an hour.
- **File AI chat**: `POST /api/files/{id}/ai-chat` (login required) and the website's `/f/{id}/ai-chat` both go through `FileTutorMessageNormalizer` and `GeminiFileTutorService`. The Gemini key lives only in `FILE_AI_API_KEY` on the server (`.env.local`, never `.env`); the app has no key since 9.2.0. Costs are bounded by the `file_ai_chat_user` (per account, per day) and `file_ai_chat_ip` limiters in `framework.yaml`, the 10-message history cap, `maxOutputTokens` and the low thinking level for Gemini 3 models. The PDF is uploaded once to the Gemini Files API and its reference cached under `var/file-ai/`.
- **Provider guard**: `User::grantPremiumUntil()` records whether Stripe or Apple paid, and one provider never shortens access the other granted.
- **Paid app buyers**: the app was a paid download (8,99 €) until version 9.0.0 made it free. `/api/apple/legacy-access` gives those buyers one free year, recorded with the `legacy_app` provider so no later payment can shorten it. Eligibility is the StoreKit original purchase date against `APPLE_LEGACY_ACCESS_CUTOFF`, which must be set to the day 9.0.0 goes live.

## Frontend assets

Webpack Encore + Tailwind. Assets are in [assets/](assets/); build with `yarn build` (prod) or `yarn watch` (dev). The CKEditor admin WYSIWYG is installed by `composer`'s `ckeditor:install` auto-script.

## Stripe webhooks (local)

Use ngrok to expose the local server and point Stripe/PayPal webhook URLs at it:

```
ngrok http -host-header=rewrite dev.clasesdeapoyo.com:8888
```

Test cards: `4000 0072 4000 0007`, `4000 0566 5566 5556`.

## Paid PAU bundle products

Paid downloadable PAU packs are one-off Stripe Checkout products backed by S3 downloads. Before creating another bundle, read [docs/runbooks/create-pau-bundle.md](docs/runbooks/create-pau-bundle.md).

The first product implementation and business decisions are recorded in [docs/agent-records/2026-05-22-pau-madrid-math-pack.md](docs/agent-records/2026-05-22-pau-madrid-math-pack.md).

Important rules:

- Do not expose production MySQL publicly for bundle generation; use SSH and S3.
- Do not depend on ignored local `var/` files for paid downloads; production files belong in S3 under `product-downloads/...`.
- Keep Stripe amount/currency/product-code validation intact.
- Run the product seed and verify commands before advertising a bundle.
- Keep access-control restrictions scoped to the exact bundle context.

## Exam explainer videos

Explainer videos for exam exercises are produced with the Remotion tool in [tools/exam-video/](tools/exam-video/). Follow [docs/runbooks/create-exam-video.md](docs/runbooks/create-exam-video.md). Audio, props and rendered files are ignored; only `exercise.json` files are tracked.

## Production deployments

Production deployments must run through the EC2 deployment wrapper:

```bash
cd /var/www
./prepare_cda_coffe
```

Do not manually run deployment steps such as `git pull`, `composer install`, service restarts, cache clears, asset installs, or `npm run build` inside `/var/www/clasesdeapoyo`. The wrapper is the repository convention for production deployment and should be used by agents before any post-deploy seed/verify command.
