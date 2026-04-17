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

- API controllers live in [src/Controller/Api/](src/Controller/Api/) and use FOSRestBundle. They are **unauthenticated**.
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

## Frontend assets

Webpack Encore + Tailwind. Assets are in [assets/](assets/); build with `yarn build` (prod) or `yarn watch` (dev). The CKEditor admin WYSIWYG is installed by `composer`'s `ckeditor:install` auto-script.

## Stripe webhooks (local)

Use ngrok to expose the local server and point Stripe/PayPal webhook URLs at it:

```
ngrok http -host-header=rewrite dev.clasesdeapoyo.com:8888
```

Test cards: `4000 0072 4000 0007`, `4000 0566 5566 5556`.
