# PAU Madrid Math Pack Monetization Record

Date: 2026-05-22  
Project: Clases de Apoyo backend  
Status: implemented and deployed by the user, with follow-up CRO changes committed  
Primary product: `pau-matematicas-ii-madrid-1994-2025`

## Purpose

This document records the monetization decisions, implementation work, deployment operations, and follow-up conversion changes made during the PAU bundle conversation. It is written for future AI agents and maintainers so they can continue from the current state without reconstructing the full chat history.

Use this document as a navigation index. The implementation source of truth remains the code, commits, production environment, Stripe dashboard, and S3 bucket.

## Final Business Direction

We decided to monetize the existing PAU material with a low-cost one-off digital product instead of pushing the existing subscription as the primary offer.

Chosen first product:

- Pack: PAU / EvAU Matematicas II Madrid 1994-2025.
- Price: `14.99 EUR`.
- Delivery: downloadable PDF files after Stripe Checkout payment.
- Included files:
  - Complete PDF: exams and solutions.
  - Enunciados-only PDF.
  - Soluciones-only PDF.
- Positioning: one-time purchase, no subscription, saves time versus browsing year by year.
- Upsell: intentionally skipped for first launch.

Rejected or deferred alternatives:

- Opening production MySQL to the world for bundle generation. Rejected because it creates unnecessary security risk.
- Keeping paid files only on the EC2 `var/` folder. Rejected after review because deployments could lose or miss ignored local files.
- Making the current subscription the main offer for this use case. Rejected because it was too cheap and could distract students from the concrete pack purchase.
- Ads/social automation were discussed as promotion options, but implementation focus moved first to on-site conversion.

## Key Decisions

### Decision 1: First product is a concrete PAU pack, not a generic subscription

Reasoning:

- Students searching for a specific PAU subject/community have a clear immediate intent.
- A concrete pack is easier to understand than a broad subscription.
- The value proposition is direct: all Madrid Matematicas II historical exams and solutions in one place.
- Payment friction is lower when the buyer understands exactly what they receive.

Implementation consequence:

- Added one-off product checkout and download flow instead of extending the subscription flow.

### Decision 2: Store product downloads in S3

Reasoning:

- Production runs on a raw EC2 instance, not a fully Dockerized deployment.
- `var/` is ignored by Git and should not be a source of truth for paid downloadable assets.
- S3 is already part of the project and is a better fit for durable paid files.
- S3 lets the EC2 instance avoid heavy file-serving work.

Implementation consequence:

- Product file paths are S3 object keys under `product-downloads/pau-matematicas-ii-madrid-1994-2025/`.
- Downloads are generated as S3 presigned URLs.
- Seed and verification commands check that required files exist before enabling/verifying the product.

### Decision 3: Use Stripe Checkout for one-off pack payments

Reasoning:

- The project already has Stripe integration.
- Checkout reduces custom payment surface area.
- Webhooks can mark purchases paid server-side.
- The success page can also retrieve the session as a fallback if webhook timing is delayed.

Implementation consequence:

- Added `Product` and `ProductPurchase` entities.
- Added product routes and controller.
- Added product-specific Stripe checkout service and product webhook subscriber.

### Decision 4: Harden payment and download checks before production

Code review identified several risks:

- Paid downloads depended on ignored local files.
- Webhook completion did not validate amount, currency, and product code.
- Product availability depended on manual seed.
- Success page depended only on webhook timing.
- File paths needed download-root constraints.

Final handling:

- S3 replaced local `var/product-downloads` as the product download store.
- Webhook completion validates amount, currency, product code, and checkout session identity.
- Seed and verify commands exist for production deployment checks.
- Success page accepts Stripe checkout session ID and confirms server-side if needed.
- Product download storage normalizes S3 object keys and rejects empty, null-byte, or parent-directory paths.

### Decision 5: Use the latest Madrid Matematicas II enunciados as a free sample

Reasoning:

- Restricting everything would reduce trust.
- Keeping a visible free sample helps students verify quality before buying.
- Locking solutions creates a clearer purchase trigger without blocking all usefulness.
- The restriction is scoped to Madrid Matematicas II, where the pack exists, to avoid surprising users in unrelated subjects.

Implementation consequence:

- For Madrid Matematicas II PAU exams, anonymous users can still open latest-year `enunciados` when the exam is otherwise free.
- `soluciones` for the same context are routed to the pack.
- Non-target subjects keep their existing Premium/register flow.

## Implemented Commits

### `f766d82 Add PAU bundle product checkout`

Introduced the first product commerce flow:

- `src/Entity/Product.php`
- `src/Entity/ProductPurchase.php`
- `src/Controller/ProductController.php`
- `src/Service/Stripe/StripeCreateProductCheckoutSession.php`
- `src/Service/Product/CompleteProductPurchaseFromStripeSession.php`
- `src/EventSubscriber/StripeProductCheckoutSessionCompletedSubscriber.php`
- `src/Repository/ProductRepository.php`
- `src/Repository/ProductPurchaseRepository.php`
- `src/Command/SeedMadridMathPackProductCommand.php`
- `templates/views/products/show.html.twig`
- `templates/views/products/success.html.twig`
- `config/routes/product.yaml`
- `migrations/Version20260521135500.php`

Important routes:

- `GET /packs/{slug}` shows the product.
- `POST /packs/{slug}/checkout` starts Stripe Checkout.
- `GET /packs/success/{token}` shows paid/pending download state.
- `GET /packs/download/{token}/{fileKey}` redirects to the authorized download.

### `4344a60 Harden PAU bundle payment downloads`

Resolved review findings before production:

- Added `app:product:verify-madrid-math-pack`.
- Added `ProductDownloadStorage`.
- Added Stripe session retrieval fallback.
- Hardened webhook completion by comparing Stripe amount, currency, product code, and checkout session.
- Made seed/verify fail if required files are missing.
- Improved checkout metadata.

### `7f7508c Add Codex code reviewer instructions`

Committed `.codex/code-reviewer.md`.

Purpose:

- Local project-specific review checklist for future Codex/code-reviewer runs.
- Covers Symfony, Doctrine, Stripe, S3, Sonata, FOSRest, Twig, security, and payment/download risk areas.

### `e14e319 Serve PAU bundle downloads from S3`

Moved product downloads away from local filesystem storage:

- `ProductDownloadStorage` now uses AWS S3.
- `config/services.yaml` wires `Aws\S3\S3Client` and product download TTL.
- Product seed file paths are S3 object keys.
- Verification reports storage as `s3://<bucket>`.

### `3b1cd05 Promote PAU bundle on relevant pages`

Added first on-site promotion:

- Pack promo partial: `templates/common/products/madrid_math_pack_promo.html.twig`.
- Promoted pack on Madrid Matematicas PAU listing page.
- Promoted pack on Madrid Matematicas PAU exam pages.
- Promoted pack in relevant Matematicas course/file contexts.

### `d3f6ff2 Improve PAU bundle conversion funnel`

Implemented CRO follow-up:

- Added `src/Service/Product/MadridMathPackContext.php` to centralize pack-context detection.
- Added file-level exam access control for the pack context.
- Added Twig helpers:
  - `canSeeExamFile(file)`
  - `isMadridMathPackExam(exam)`
  - `isMadridMathPackFile(file)`
- Locked Madrid Matematicas II latest-year solutions while preserving latest-year enunciados as free sample.
- Routed locked Madrid Matematicas II files to the pack page instead of generic registration/Premium.
- Suppressed generic Premium CTAs in the exact pack context.
- Clarified product page copy: free sample exists, one-time payment, no subscription.

## Stripe State And Operational Notes

The user created a production restricted Stripe token and placed it in a local file for agent use during setup. Do not commit or document secrets.

The user also enabled webhook endpoints.

The product and price were created in Stripe before deployment. Future agents should verify current production IDs from environment/Stripe/dashboard or from the seeded product row, not from this document.

Required product seed command format:

```bash
php bin/console app:product:seed-madrid-math-pack \
  --stripe-product-id=<prod_stripe_product_id> \
  --stripe-price-id=<prod_stripe_price_id> \
  --env=prod
```

Required production verification command format:

```bash
php bin/console app:product:verify-madrid-math-pack \
  --stripe-product-id=<prod_stripe_product_id> \
  --stripe-price-id=<prod_stripe_price_id> \
  --env=prod
```

Important: if these commands fail because S3 files are missing, do not enable or advertise the product until S3 is fixed.

## S3 Download Assets

Current expected S3 object keys:

```text
product-downloads/pau-matematicas-ii-madrid-1994-2025/PAU-Matematicas-II-Madrid-1994-2025-examenes-y-soluciones.pdf
product-downloads/pau-matematicas-ii-madrid-1994-2025/PAU-Matematicas-II-Madrid-1994-2025-enunciados.pdf
product-downloads/pau-matematicas-ii-madrid-1994-2025/PAU-Matematicas-II-Madrid-1994-2025-soluciones.pdf
```

Current metadata in seed command:

- Complete PDF: `943 paginas`.
- Enunciados PDF: `194 paginas`.
- Soluciones PDF: `749 paginas`.

The PDFs were generated from existing exam/file material and uploaded to S3 using S3 credentials from local `.env.local`. Do not rely on local `var/product-downloads` for production.

## Production Deployment Notes

Production server connection used during the work:

```bash
ssh -o "IdentitiesOnly yes" -i /Users/gerardofernandez/Projects/ClasesDeApoyo/clasesdeapoyo.pem ubuntu@35.180.205.41
```

Production app path used in prior commands:

```text
/var/www/clasesdeapoyo
```

Typical deployment after the current commits:

```bash
cd /var/www/clasesdeapoyo
git pull
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
php bin/console cache:clear --env=prod
php bin/console app:product:verify-madrid-math-pack --env=prod
```

If the product has not been seeded in that environment, run the seed command before verify.

No new migration was needed for commit `d3f6ff2`; the product tables came from `f766d82`.

## Production Testing Notes

The product canonical URL is:

```text
https://www.clasesdeapoyo.com/packs/pau-matematicas-ii-madrid-1994-2025
```

The non-www URL redirects to www:

```text
https://clasesdeapoyo.com/packs/pau-matematicas-ii-madrid-1994-2025 -> https://www.clasesdeapoyo.com/packs/pau-matematicas-ii-madrid-1994-2025
```

Recommended production smoke tests:

1. Open the product page and confirm price, copy, and file list.
2. Start Stripe Checkout with a low-risk test path if available in the environment.
3. Confirm success page can show pending state if webhook is delayed.
4. Confirm paid success state exposes download links.
5. Confirm each download redirects to a presigned S3 URL and returns a PDF.
6. Confirm anonymous Madrid Matematicas II locked exams route to the pack.
7. Confirm unrelated subjects still route to the previous Premium/register path.

## CRO Summary

The conversion plan was evaluated with the `$cro` skill.

Chosen changes:

- Put the pack directly in the Madrid Matematicas II PAU exam/listing context.
- Remove competing generic Premium CTA in that context.
- Keep a free sample to preserve trust.
- Lock solutions for the target context to create a purchase trigger.
- Use product-specific CTA copy instead of generic registration copy.
- Clarify on the product page that this is a one-time purchase, not a subscription.

Important implementation constraint:

- The access restriction must stay scoped to Madrid + Selectividad/PAU + 2o Bachillerato + Matematicas. Do not accidentally apply the `soluciones` lock to all subjects unless that is a deliberate new monetization decision.

## Quality Gates Run

After the CRO implementation:

```bash
docker-compose exec -T php php -l src/Service/PremiumService.php
docker-compose exec -T php php -l src/Service/Product/MadridMathPackContext.php
docker-compose exec -T php php -l src/TwigExtension/PremiumExtension.php
docker-compose exec -T php php bin/console lint:twig templates --no-interaction
docker-compose exec -T php composer stan
docker-compose exec -T php composer ci
```

Result:

- PHP syntax checks passed.
- Twig lint passed.
- PHPStan passed.
- `composer ci` passed.
- PHPUnit ran but reported `No tests executed!`, which was the current project state.

Local HTTP render checks were also made against `http://localhost:8080`:

- Madrid Matematicas 2021 exam page showed pack CTAs and `14,99 EUR`.
- Madrid Fisica 2021 exam page kept the generic Premium path.
- Product page showed `Antes de comprar`, `Pago unico`, `Comprar y descargar`, and `Sin suscripcion`.

## Open Follow-Ups

Prioritized next steps:

1. Add automated tests for the product purchase lifecycle:
   - Checkout creation.
   - Webhook completion.
   - Success fallback using Stripe session ID.
   - Download authorization.
   - S3 missing-file behavior.
2. Add tests for `PremiumService::canSeeExamFile()` so the Madrid Matematicas II restriction cannot accidentally expand to other subjects.
3. Add analytics events or funnel reporting for:
   - Pack promo impressions.
   - Locked file CTA clicks.
   - Checkout started.
   - Purchase completed.
   - Download clicked.
4. Verify production webhook delivery in Stripe after real deployment.
5. Consider a second pack only after measuring this first one.
6. If paid acquisition is tested, start with low-budget search ads for high-intent queries rather than broad social ads.

## Agent Guidance For Future Work

Recommended skills:

- Use `cro` for further conversion changes.
- Use `ads` and `ad-creative` if building paid acquisition campaigns.
- Use `social` if creating scheduled posts for Twitter/X or Instagram.
- Use `seo-audit` before changing indexed PAU pages at scale.
- Use the local `.codex/code-reviewer.md` before production-impacting payment/download changes.

Repository conventions:

- Run commands inside Docker with `docker-compose exec php ...`.
- Run `docker-compose exec php composer ci` before finishing.
- Do not commit secrets or local tokens.
- Do not rely on local ignored `var/` files for paid product delivery.
- Keep product/download/payment logic in services and controllers thin.
- Treat Stripe and S3 changes as production-risky even when code changes are small.

## Current Source Files To Inspect First

For product checkout and downloads:

- `src/Controller/ProductController.php`
- `src/Entity/Product.php`
- `src/Entity/ProductPurchase.php`
- `src/Service/Product/CompleteProductPurchaseFromStripeSession.php`
- `src/Service/Product/ProductDownloadStorage.php`
- `src/Service/Stripe/StripeCreateProductCheckoutSession.php`
- `src/Service/Stripe/StripeRetrieveCheckoutSession.php`
- `src/EventSubscriber/StripeProductCheckoutSessionCompletedSubscriber.php`
- `src/Command/SeedMadridMathPackProductCommand.php`
- `src/Command/VerifyMadridMathPackProductCommand.php`

For CRO and on-site promotion:

- `src/Service/Product/MadridMathPackContext.php`
- `src/Service/PremiumService.php`
- `src/Service/FileAccessResolver.php`
- `src/TwigExtension/PremiumExtension.php`
- `templates/common/products/madrid_math_pack_promo.html.twig`
- `templates/views/knowledge_tests/exam/exam.html.twig`
- `templates/views/knowledge_tests/community_test_course_subject/community_test_course_subject.html.twig`
- `templates/views/files/viewer.html.twig`
- `templates/views/products/show.html.twig`

