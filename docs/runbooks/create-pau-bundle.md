# Create A PAU Bundle Product

Use this runbook when creating a new paid downloadable PAU bundle, such as a subject/community/year-range PDF pack. The first implementation was `pau-matematicas-ii-madrid-1994-2025`; treat it as the reference product, not as the only supported shape.

The goal is to keep each new bundle repeatable, auditable, and safe for production. Do not open production MySQL to the internet, do not depend on ignored local `var/` files for paid downloads, and do not publish a pack until the product row, Stripe price, and S3 files have all been verified.

## Existing Reference

Historical record:

- `docs/agent-records/2026-05-22-pau-madrid-math-pack.md`

Reference implementation files:

- `src/Entity/Product.php`
- `src/Entity/ProductPurchase.php`
- `src/Controller/ProductController.php`
- `src/Command/SeedPauBundleProductCommand.php`
- `src/Command/VerifyPauBundleProductCommand.php`
- `src/Command/SeedMadridMathPackProductCommand.php` (legacy first-pack command)
- `src/Command/VerifyMadridMathPackProductCommand.php` (legacy first-pack command)
- `src/Service/Product/PauBundleProductCatalog.php`
- `src/Service/Product/ProductDownloadStorage.php`
- `src/Service/Product/CompleteProductPurchaseFromStripeSession.php`
- `src/Service/Stripe/StripeCreateProductCheckoutSession.php`
- `src/Service/Stripe/StripeRetrieveCheckoutSession.php`
- `templates/views/products/show.html.twig`
- `templates/views/products/success.html.twig`
- `templates/common/products/pau_bundle_pack_promo.html.twig`

## Product Design Checklist

Before writing code or creating Stripe objects, decide and record:

- Target: knowledge test, community, course, subject, and year range.
- Product code, slug, and title.
- Price and currency.
- Included PDFs: usually complete, enunciados-only, and soluciones-only.
- Free sample policy: which recent files remain free and which paid CTA is shown.
- On-site promotion locations: listing pages, exam pages, course pages, and file viewer pages.
- Whether the pack competes with or complements Premium.
- Success metric: purchases, checkout starts, CTA clicks, or revenue per targeted visitor.

Recommended defaults for low-cost PAU packs:

- Keep the offer focused on one clear intent, for example one subject plus one community.
- Use a one-off Stripe Price, not a subscription.
- Keep a free sample visible so students can verify quality.
- Route high-intent locked files directly to the pack page.
- Avoid showing a generic Premium CTA beside the pack CTA unless the choice is intentionally explained.

## Data Audit

Audit the source exams/files before generating PDFs.

Use production data when the local database is stale, but access it over SSH. Do not expose production MySQL publicly.

Example DQL shape for a PAU subject/community audit:

```bash
ssh -o "IdentitiesOnly yes" -i /Users/gerardofernandez/Projects/ClasesDeApoyo/clasesdeapoyo.pem ubuntu@35.180.205.41 \
  'cd /var/www/clasesdeapoyo && php bin/console doctrine:query:dql "SELECT f.id, f.name, e.name AS examName, ty.year, m.id AS mediaId, m.providerReference, m.name AS mediaName FROM App\\Entity\\File f JOIN f.exam e JOIN e.testYear ty JOIN ty.communityTestCourseSubject ctcs JOIN ctcs.communityTest ct JOIN ct.community c JOIN ctcs.courseSubject cs JOIN cs.subject s JOIN f.file m WHERE c.slug = '\''madrid'\'' AND s.slug = '\''matematicas'\'' ORDER BY ty.year ASC, e.weight ASC, f.weight ASC" --no-interaction'
```

For a new bundle, adapt:

- `c.slug`
- `s.slug`
- the knowledge test/course joins if needed
- sort order
- selected fields

Check:

- Expected years exist.
- Expected file types exist, normally `enunciados` and `soluciones`.
- Missing solutions are understood and documented.
- Exam names are consistent enough for PDF labels.
- Sonata Media `providerReference` values point to real S3 objects.

## PDF Generation Process

The current Madrid pack PDFs were generated locally from existing Sonata Media PDFs and then uploaded to S3. The generated output was kept under ignored local folders:

- `var/generated-bundles/`
- `var/product-downloads/`

Those local folders are working space only. They are not deployment artifacts.

For each new pack, generate at least:

- Complete PDF: enunciados and soluciones in the chosen order.
- Enunciados PDF.
- Soluciones PDF.
- Manifest JSON with source file IDs, media IDs, source S3 keys, local names, and ordering.

Recommended output naming:

```text
PAU-<Subject>-<Community>-<StartYear>-<EndYear>-examenes-y-soluciones.pdf
PAU-<Subject>-<Community>-<StartYear>-<EndYear>-enunciados.pdf
PAU-<Subject>-<Community>-<StartYear>-<EndYear>-soluciones.pdf
PAU-<Subject>-<Community>-<StartYear>-<EndYear>-manifest.json
```

Generation requirements:

- Preserve deterministic ordering by year, exam weight, and file weight.
- Keep a manifest so later agents can trace every output page back to source files.
- Prefer local generation or a separate worker over running heavy PDF merge jobs on the small EC2 instance.
- If generation must use production data, fetch metadata over SSH and source PDFs from S3; do not open the database publicly.
- Validate output files before upload: non-empty, readable as PDFs, and page counts look plausible.

Current gap:

- The PDF generation step is not yet a reusable Symfony command. If bundle creation becomes frequent, add a generic command such as `app:product:generate-pau-bundle` that accepts community, subject, year range, and output directory, then produces the three PDFs plus manifest.

## S3 Upload

Paid bundle files must live in S3 under `product-downloads/...`.

Recommended key pattern:

```text
product-downloads/<product-slug>/<filename>.pdf
```

For example:

```text
product-downloads/pau-matematicas-ii-madrid-1994-2025/PAU-Matematicas-II-Madrid-1994-2025-examenes-y-soluciones.pdf
```

Use credentials from the appropriate environment file or AWS profile. Do not commit credentials.

After upload, verify the objects exist before seeding the product. The seed and verify commands should fail if any expected S3 object is missing.

## Stripe Setup

Create a Stripe Product and one-time Price for the pack.

Rules:

- Use live Stripe only when intentionally preparing production.
- Use a restricted key for agent/API operations.
- Store the resulting Stripe Product ID and Price ID in deployment notes or the product row; do not commit secrets.
- If replacing a price, create a new Price and deactivate the old one for new purchases. Existing paid purchases do not need migration.

The current checkout flow expects the local `Product` row to hold:

- `stripeProductId`
- `stripePriceId`
- `priceCents`
- `currency`

Webhook completion validates amount, currency, product code, and checkout session identity. Keep those checks intact for all future packs.

## Product Seed And Verify

The generic seed/verify commands work for every configured pack in `PauBundleProductCatalog`:

```bash
php bin/console app:product:seed-pau-bundle \
  --product-code=<product_code> \
  --stripe-product-id=<stripe_product_id> \
  --stripe-price-id=<stripe_price_id> \
  --env=prod
```

```bash
php bin/console app:product:verify-pau-bundle \
  --product-code=<product_code> \
  --stripe-product-id=<stripe_product_id> \
  --stripe-price-id=<stripe_price_id> \
  --env=prod
```

For another pack:

- Add the product definition to `PauBundleProductCatalog`.
- Generate and upload the PDFs to the S3 paths declared in that definition.
- Create the Stripe Product and Price.
- Run the generic seed and verify commands with the new `product-code`.

Preferred future direction:

- Move product definitions from PHP code into structured config if the catalog grows beyond a few packs.

Minimum requirements for any new seed command:

- Require Stripe Product ID and Price ID.
- Set stable product code and slug.
- Set title, description, price, currency, enabled state, and file catalog.
- Call `ProductDownloadStorage::findMissingFiles()` before persisting/enabling.
- Fail loudly if expected S3 files are missing.

Minimum requirements for any verify command:

- Confirm product exists and is enabled.
- Confirm expected local price/currency.
- Confirm expected Stripe Product ID and Price ID when provided.
- Confirm every configured product file exists in S3.
- Print the storage description and file count.

## On-Site Promotion And Access Control

For each bundle, decide how targeted visitors discover it.

Reference for Madrid Matematicas II:

- `src/Service/Product/PauBundleProductCatalog.php` detects configured pack contexts.
- `src/Service/PremiumService.php` applies file-level access behavior.
- `src/TwigExtension/PremiumExtension.php` exposes Twig helpers.
- `templates/views/knowledge_tests/exam/exam.html.twig` routes locked target files to the pack.
- `templates/views/knowledge_tests/community_test_course_subject/community_test_course_subject.html.twig` promotes the pack on the listing page.
- `templates/views/files/viewer.html.twig` suppresses competing Premium CTA in the target context.

For a new bundle:

- Add or generalize a context service so the targeting rule is centralized.
- Keep access restrictions scoped to the exact bundle context.
- Do not accidentally lock unrelated subjects, communities, or courses.
- Keep a free sample policy explicit in code and docs.
- Ensure locked target files use product-specific CTA copy.
- Suppress generic Premium CTAs only when the pack is the clearer offer.

If multiple packs exist, avoid a growing set of hard-coded `*PackContext` services. Prefer a configured catalog that can answer whether an exam/file/course subject belongs to a product.

## Deployment

Standard production deployment must use the EC2 wrapper script:

```bash
cd /var/www
./prepare_cda_coffe
```

Do not replace this with manual `git pull`, `composer install`, migrations, cache clears, service restarts, asset installs, or `npm run build` commands inside `/var/www/clasesdeapoyo`. The wrapper is the production deployment convention and exists so direct-copy production changes do not block or confuse future agent deployments.

After the wrapper succeeds, run the product-specific verification command:

```bash
cd /var/www/clasesdeapoyo
php bin/console app:product:verify-pau-bundle --product-code=<product_code> --env=prod
```

If the product has not been seeded in production yet:

1. Upload PDFs to S3.
2. Create Stripe Product and Price.
3. Run `/var/www/prepare_cda_coffe`.
4. Run the seed command with production Stripe IDs.
5. Run the verify command.
6. Smoke-test the product page and checkout start.

## Production Smoke Tests

For every new bundle, verify:

- Product page returns HTTP 200 on the canonical `www` URL.
- Product page shows the correct price, file list, and one-time-payment copy.
- Checkout button opens Stripe Checkout for the correct product/price.
- Webhook endpoint receives and accepts `checkout.session.completed`.
- Success page handles both paid and pending states.
- Paid download links redirect to presigned S3 URLs.
- Each presigned URL returns a PDF.
- Target exam/listing pages show the pack CTA.
- Non-target subjects are unchanged.
- Registration/Premium pages still present the intended Premium offer.

## Quality Gate

For code changes, run:

```bash
docker-compose exec -T php composer ci
```

For documentation-only changes, at minimum run:

```bash
git diff --check
```

When changing payment, S3, seed/verify commands, or access control, run the full `composer ci` and do local render checks against `http://localhost:8080` when the Docker web server is available.

## Traceability Requirements

For each new bundle, add or update a record under `docs/agent-records/` with:

- Product target and rationale.
- Product slug/code.
- Stripe Product and Price IDs, if safe to record.
- S3 object keys.
- Source data audit summary.
- PDF output filenames and page counts.
- Seed/verify commands used.
- Deployment date and smoke-test results.
- Any access-control or promotion changes.
- Open risks and follow-ups.
