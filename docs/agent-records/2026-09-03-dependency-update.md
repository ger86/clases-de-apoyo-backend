# Dependency Update and Security Advisory Cleanup

Date: 2026-09-03
Project: Clases de Apoyo backend
Status: implemented, verified, deployed to production on 2026-09-03.
Scope: PHP dependencies only. `package.json` and the JavaScript build were not touched.

## What changed

`composer update --with-all-dependencies` moved 100 packages and removed one (`ralouphie/getallheaders`, dropped by guzzle psr7 3). The notable ones:

- doctrine/orm 3.6.2 to 3.6.7, doctrine/dbal 4.4.1 to 4.4.4, doctrine/doctrine-bundle 3.2.2 to 3.3.1, doctrine/persistence 4.1.1 to 4.2.0
- gedmo/doctrine-extensions 3.22.0 to 3.22.1
- symfony components from 8.0.0 / 8.0.6 to 8.0.8 / 8.0.15, except the ones described below
- twig 3.23 to 3.28, sonata-project/admin-bundle 4.42 to 4.43, symfony/stimulus-bundle 2.32 to 3.4 (a major, pulled by the sonata bump)
- aws/aws-sdk-php 3.371.1 to 3.394.7, which pulled guzzle 7.10 to 8.1, guzzle/psr7 2.8 to 3.1 and guzzle/promises 2.3 to 3.0 (three majors)
- phpunit 13.0.5 to 13.3.2, phpstan 2.1.40 to 2.2.12, easy-coding-standard 13.0.4 to 13.2.19
- stripe/stripe-php 19.4.0 to 19.4.1 only. The 21.x major was deliberately left alone: it crosses two majors in the payment path, no test exercises Stripe, and `src/Service/Stripe/StripeProcessInvoicePaymentFailed.php` had uncommitted work in progress at the time.

Security result: `composer audit` went from **54 advisories affecting 19 packages** to **2**, and both remaining ones are ignored on purpose (see below). Fixed along the way: a critical code injection in `mtdowling/jmespath.php`, and high severity issues in `aws/aws-sdk-php`, `guzzlehttp/guzzle`, `symfony/mime` and `symfony/monolog-bridge`.

## The formatter-bundle constraint bug

`sonata-project/formatter-bundle` is required as `6.x-dev`. That branch requires **the exact version** `8.0` (not `^8.0`) for every Symfony component it uses:

```
symfony/config, dependency-injection, event-dispatcher, form, framework-bundle,
http-foundation, http-kernel, options-resolver, property-access, translation, validator
```

In Composer a bare `8.0` means exactly v8.0.0, so those eleven packages are frozen at v8.0.0 and can never receive a patch. That is why `symfony/form`, `symfony/framework-bundle` and `symfony/validator` sat at 8.0.0 while their siblings were at 8.0.6. Upstream still has the bug; the last commit on the `6.x` branch is 2026-05-03 (`d51231a`), and updating to it changes nothing.

Composer 2.9 refuses to install a version covered by a security advisory, so this froze the whole update: http-foundation v8.0.0 and http-kernel v8.0.0 are both under advisories, no other version satisfies the exact `8.0` constraint, and resolution failed outright.

The chosen fix is four entries in `config.audit.ignore` in `composer.json`, each carrying its reason. Both deferred advisories were checked against this codebase before ignoring them:

- `PKSA-dw7n-x7f5-zf63` (high, CVE-2026-45075, HEAD request bypasses the `methods` filter): the app declares no `#[IsGranted]`, `#[IsSignatureValid]` or `#[IsCsrfTokenValid]` attribute anywhere in `src/`, so there is nothing to bypass.
- `PKSA-y6py-qpv1-h52p` (medium, CVE-2026-48736, `IpUtils::PRIVATE_SUBNETS` misses IPv6 transition forms): the app configures no trusted proxies and never calls `IpUtils` or `NoPrivateNetworkHttpClient`.

Two further ids (`PKSA-365x-2zjk-pt47`, `PKSA-b35n-565h-rs4q`) are in the list only because the resolver blocks on them while scanning the http-foundation range; they do not affect the installed version.

Alternatives that were rejected: forking the bundle, or adding a patch plugin. Both put new machinery into `prepare_cda_coffe` for a pair of advisories that do not apply to this code. Remove the ignore entries when upstream fixes the constraint, or when the bundle is dropped.

## The doctrine/orm cap

`composer.json` now says `"doctrine/orm": "^3.6 <3.6.8"`. Reason:

`doctrine/orm` 3.6.8 added `GenerateSchemaEventArgs::setSchema()`, which throws unless `Schema::edit()` exists, and that needs `doctrine/dbal` ^4.5. DBAL 4.5 has no stable release yet (only `4.5.x-dev`). Meanwhile `symfony/doctrine-bridge` 8.0.15 calls `setSchema()` whenever the method exists, guarded only by `method_exists($event, 'setSchema')` and not by the DBAL capability. The combination breaks every schema command:

```
doctrine:schema:validate
  The setSchema() method requires the DBAL Schema::edit() API which is not
  available in the current DBAL version. This feature requires doctrine/dbal ^4.5 or higher.
```

That is a `composer ci` failure and it would also break `doctrine:migrations:diff`. Web requests are unaffected, because the listeners only run during schema tooling.

Capping ORM below 3.6.8 removes the throwing method, the bridge takes its fallback path, and `schema:validate` passes again. Remove the cap when DBAL 4.5 is released, or when Symfony fixes the guard. Capping `symfony/doctrine-bridge` instead would be the other way to do it, but it is a transitive package and would need a new root requirement.

## Verification

`composer ci` is green: ECS, PHPStan level 2, `doctrine:schema:validate`, `lint:container`, `lint:twig`, `lint:yaml`, 32 tests, 43 assertions.

That gate does not touch the database, Stripe or Sonata form rendering, so a 76 check smoke harness was run before and after the update and the two outputs were diffed. It boots the kernel in the `test` environment against a copy of the local database, logs in as a real admin user, and requests:

- 14 public pages, with the slugs pulled from real rows: home, blog index, pack index, login, contact, a course, a course subject, a chapter, an article, a video, a pack, an exam
- the admin dashboard, plus list, create and edit for all 21 registered Sonata admins, with edit skipped where the table is empty

Result: every status code identical before and after. The same four failures appear on both sides and are pre-existing, unrelated to this update:

- `/sitemap.xml` 404, because sitemaps are static files written by `app:sitemap:build` into the gitignored `public/sitemap/`, not a route. That command was run separately and completed.
- `sonata.media.admin.media` list and create, and `sonata.media.admin.gallery` list, all 500 with a missing Twig variable inside the media bundle's own templates. Note: the Sonata media admin screens may need review.

Extra targeted checks for the risky bumps:

- doctrine-bundle 3.2.2 to 3.3.1 is the package whose event tag removal caused the outage recorded in `2026-09-03-gedmo-listeners-and-slug-policy.md`. `tests/Doctrine/GedmoListenersRegistrationTest.php` passes and the harness prints `gedmo sluggable: yes` / `gedmo timestampable: yes`.
- guzzle 7 to 8: no code in `src/` uses Guzzle directly, it only arrives under the AWS SDK. S3 presigning, which is what the paid pack downloads rely on, was exercised offline with fake credentials and produced a correct host, `X-Amz-Signature` and `X-Amz-Expires=1800`.
- symfony/stimulus-bundle 2 to 3: the front end does not use Stimulus at all (no `assets/bootstrap.js`, no `assets/controllers.json`, no import in `assets/app.js`). It arrives only as a Sonata admin dependency, and every admin screen renders.

Production PHP is 8.4.21 and Composer is 2.9.5, so the new lock installs there.

## Reproducing the smoke harness

The script is not in the repository. To rebuild it: clone the dev database into `clasesdeapoyo_test` (the test environment appends `_test` to the database name), then boot `new Kernel('test', true)` with `DATABASE_URL` pointed at that copy, and drive it with `Symfony\Bundle\FrameworkBundle\KernelBrowser` plus `loginUser()` on a row from the user table that has `ROLE_ADMIN`. Iterate the Sonata admins with `$pool->getAdminServiceIds()` and `$admin->generateUrl('list'|'create'|'edit')`. Save the output, update, run it again, and diff.

## Pre-deploy checks

Run before handing the deploy to a human, all read-only against production plus local rehearsals of each wrapper step.

`prepare_cda_coffe` runs: `git pull origin master`, `composer install`, `doctrine:migrations:migrate`, nginx restart, php8.4-fpm restart, `cache:clear`, `assets:install`, `npm run build`, `dump-autoload --optimize --no-dev --classmap-authoritative`, `dump-env prod`. Each step was checked:

- **git pull.** Production is on `master` at `8cef6da` with only `config/reference.php` modified, plus untracked junk (`._` AppleDouble files, `.env.save`). The incoming commits touch `composer.json`, `composer.lock`, `symfony.lock` and `docs/`, so nothing overlaps and the pull cannot conflict.
- **composer install.** `composer validate` reports no lock mismatch, and `composer install --dry-run` says "Verifying lock file contents can be installed on current platform. Nothing to install, update or remove". Platform requirements were extracted from both locks and compared: the only new one is `ext-filter`, which production already loads. Every other required extension is present, and the highest PHP floor in the lock is `>=8.4.1` against production's 8.4.21. Note that the wrapper installs with dev dependencies, so phpunit, phpstan and ECS are installed too; their platform requirements were included in this comparison.
- **migrations:migrate.** Production is already at the latest version, 13 executed of 13 available, and these commits add no migration, so the step is a no-op.
- **cache:clear and assets:install.** Both were run locally in the `prod` environment with debug off. The production container compiles, `lint:container --env=prod` passes, and assets install.
- **npm run build.** `package.json` is untouched by this update, and `yarn build` still compiles locally ("webpack compiled successfully").
- **composer auto-scripts.** `composer install` also runs the `auto-scripts` block, whose `ckeditor:install` step downloads CKEditor over the network, so it can fail even when the lock installs cleanly. Running `composer run-script auto-scripts` in the container returns `[OK]` for `cache:clear`, `ckeditor:install` and `assets:install public`.

Runtime rehearsal in the `prod` environment, debug off, against a copy of the database: 15 public pages return 200 (home, blog index and article, packs index and pack detail, login, register, reset password, contact, course, course subject, chapter, video, exam, community test), `/admin/dashboard` returns 302 to the login form, and `/api/courses` returns 200 with a JSON body. The Gedmo sluggable and timestampable listeners are both registered in the compiled prod container.

Payment and mail paths, checked offline because no test covers them:

- `Stripe\Webhook::constructEvent()` verifies a self-signed payload on 19.4.1, and `Stripe\StripeClient` constructs.
- The `MAILER_DSN` still resolves to `GmailSmtpTransport`.
- S3 presigning produces a correct host, `X-Amz-Signature` and `X-Amz-Expires=1800` under guzzle 8.

Not verified, and not verifiable without live traffic: actually sending an email, a real Stripe charge or webhook delivery, and a real S3 download. These are patch-level or transitive changes, but they are the residual risk.

## Deploy result

Deployed on 2026-09-03 at about 12:41 UTC through `prepare_cda_coffe`. Fast-forward `8cef6da..747a115`, 4 installs, 100 updates, 1 removal, migrations already at the latest version, prod cache cleared, assets installed, webpack "Compiled successfully", `.env.local.php` dumped.

One unexpected warning appeared four times during the run: `Some commands could not be registered: Class "Symfony\UX\StimulusBundle\Twig\UxControllersTwigExtension" not found`. It comes from the old compiled prod container, which was built against stimulus-bundle 2.x, where that class still existed. It only affects the console command list, never a web request, and it stops after the wrapper's `cache:clear`. Confirmed after the deploy: `bin/console list --env=prod` is clean.

Post-deploy verification against the live site: home, `/blog`, a blog article, `/packs`, both pack detail pages, `/login`, `/register`, `/reset-password`, `/contacto`, `/c/2o-bachillerato`, `/c/2o-bachillerato/quimica`, `/s/selectividad`, `/s/selectividad/madrid`, `/s/selectividad/madrid/matematicas` and an exam page all return 200. `/admin/dashboard` returns 302 to the login form and `/api/courses` returns 200. `var/log/prod.log` has no entry at all after the deploy.

Rollback: on the instance, `git reset --hard 8cef6da` followed by `composer install` restores the dependency set that was live before this deploy.
