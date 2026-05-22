# PAU Madrid Physics Pack Record

Date: 2026-05-22
Project: Clases de Apoyo backend
Status: generated locally, uploaded to S3, Stripe product/price created, deployed to production, and verified
Primary product: `pau-fisica-madrid-1996-2025`

## Purpose

This document records the second paid PAU bundle product, created by repeating and generalizing the Matemáticas II Madrid process. Use `docs/runbooks/create-pau-bundle.md` as the repeatable process and this file as the Física-specific trace record.

## Product

- Product code: `pau_fisica_madrid_1996_2025`
- Slug: `pau-fisica-madrid-1996-2025`
- Title: `Pack PAU Física Madrid 1996-2025`
- Price: `9.99 EUR`
- Target: PAU/Selectividad, Madrid, 2o Bachillerato, Física
- Positioning: focused one-time PDF download for students who only want Madrid Física PAU material.

## Source Data Audit

Production data was used because the local database was stale.

Connection used:

```bash
ssh -o "IdentitiesOnly yes" -i /Users/gerardofernandez/Projects/ClasesDeApoyo/clasesdeapoyo.pem ubuntu@35.180.205.41
```

Production query exported `App\Entity\File` rows for:

- community slug: `madrid`
- subject slug: `fisica`
- ordered by year, exam weight, and file weight

Result:

- 148 source files.
- Years: 1996-2025.
- 78 enunciado/enunciados files.
- 70 solucion/soluciones files.

Important data detail:

- Some Física file names use singular labels such as `Enunciado` and `Solución`. The free-sample access logic was broadened from exact `enunciados` to names starting with `enunciado`.

## Generated Files

Local working paths:

```text
var/generated-bundles/PAU-Fisica-Madrid-1996-2025-manifest.json
var/generated-bundles/PAU-Fisica-Madrid-1996-2025-examenes-y-soluciones.pdf
var/generated-bundles/PAU-Fisica-Madrid-1996-2025-enunciados.pdf
var/generated-bundles/PAU-Fisica-Madrid-1996-2025-soluciones.pdf
var/product-downloads/pau-fisica-madrid-1996-2025/
```

Page counts:

- Complete PDF: 786 pages.
- Enunciados PDF: 170 pages.
- Soluciones PDF: 616 pages.

The PDFs were merged locally with `qpdf` from source PDFs downloaded from production S3. The EC2 instance was not used for heavy PDF generation.

## S3

Uploaded to both buckets so local and production verification can work:

- `s3://clasesdeapoyodev/product-downloads/pau-fisica-madrid-1996-2025/`
- `s3://clasesdeapoyosf/product-downloads/pau-fisica-madrid-1996-2025/`

Object keys:

```text
product-downloads/pau-fisica-madrid-1996-2025/PAU-Fisica-Madrid-1996-2025-examenes-y-soluciones.pdf
product-downloads/pau-fisica-madrid-1996-2025/PAU-Fisica-Madrid-1996-2025-enunciados.pdf
product-downloads/pau-fisica-madrid-1996-2025/PAU-Fisica-Madrid-1996-2025-soluciones.pdf
```

Uploaded sizes:

- Enunciados: 8,844,573 bytes.
- Complete: 21,808,913 bytes.
- Soluciones: 12,958,194 bytes.

## Stripe

Live Stripe Product:

- Product ID: `prod_UYyVHdqXdv3nAk`
- Name: `Pack PAU Física Madrid 1996-2025`
- Metadata: `product_code=pau_fisica_madrid_1996_2025`

Live Stripe Price:

- Price ID: `price_1TZqYaBuKHqaI230iRwSlLnq`
- Amount: `999 eur`
- Type: one-time
- Active: yes
- Metadata: `product_code=pau_fisica_madrid_1996_2025`

## Backend Changes

The second bundle triggered a small generalization:

- Added `PauBundleProductDefinition`.
- Added `PauBundleProductCatalog`.
- Added generic commands:
  - `app:product:seed-pau-bundle`
  - `app:product:verify-pau-bundle`
- Added generic Twig/product context helpers.
- Added generic promo partial:
  - `templates/common/products/pau_bundle_pack_promo.html.twig`
- Updated product page copy to use catalog metadata instead of hardcoded Matemáticas II counts.
- Updated exam/listing/course/file-viewer promotion to use the catalog pack for the current context.
- Kept legacy Matemáticas commands for backward compatibility.

Committed and pushed:

```text
c9fd61b Add Madrid Physics PAU bundle
```

## Local Seed And Verify

Local commands run successfully:

```bash
docker-compose exec -T php php bin/console app:product:seed-pau-bundle \
  --product-code=pau_fisica_madrid_1996_2025 \
  --stripe-product-id=prod_UYyVHdqXdv3nAk \
  --stripe-price-id=price_1TZqYaBuKHqaI230iRwSlLnq \
  --no-interaction

docker-compose exec -T php php bin/console app:product:verify-pau-bundle \
  --product-code=pau_fisica_madrid_1996_2025 \
  --stripe-product-id=prod_UYyVHdqXdv3nAk \
  --stripe-price-id=price_1TZqYaBuKHqaI230iRwSlLnq \
  --no-interaction
```

Verification output:

- Product ready: `pau-fisica-madrid-1996-2025`.
- Files: 3.
- Storage: `s3://clasesdeapoyodev`.

## Local Render Checks

Checked through `http://localhost:8080`:

- `/packs/pau-fisica-madrid-1996-2025` showed Física, 1996-2025, 9,99 EUR, 786/170/616 page counts, and checkout CTA.
- `/s/selectividad/madrid/fisica` showed the PAU Física pack promo and suppressed the generic Premium box.
- `/s/selectividad/madrid/fisica/2021-modelo` showed locked file CTAs pointing to the Física pack.
- `/s/selectividad/madrid/quimica/2021-modelo` kept the generic Premium/register flow.

## Production Deployment

Production was deployed from Git after pushing commit `c9fd61b`.

Initial production pull was blocked because the EC2 checkout still had tracked local modifications from a previous direct-copy pricing deployment. To avoid overwriting or losing those files, the tracked production changes were stashed before pulling:

```bash
git stash push -m before-fisica-bundle-deploy
git pull --ff-only
composer install --no-dev --optimize-autoloader --no-interaction
php bin/console cache:clear --env=prod --no-interaction
```

After `composer install` / cache clear, production generated a tracked change in `config/reference.php`. That generated tracked change was also stashed to prevent future `git pull` operations from being blocked:

```bash
git stash push -m after-fisica-bundle-composer-reference -- config/reference.php
```

Production still had untracked backup/resource files, including `.env.local.pricing-backup-*`, `.env.save`, macOS `._*` files, and `public/.well-known/`. They were left untouched because they were unrelated to the bundle deployment and untracked files do not block normal pulls unless paths collide with committed files.

Final production Git state:

- HEAD: `c9fd61b`
- Tracked working tree: clean after stashing `config/reference.php`.
- Stashes kept as safety backups:
  - `before-fisica-bundle-deploy`
  - `after-fisica-bundle-composer-reference`

## Production Seed And Verify

The production product row was seeded and verified with:

```bash
php bin/console app:product:seed-pau-bundle \
  --product-code=pau_fisica_madrid_1996_2025 \
  --stripe-product-id=prod_UYyVHdqXdv3nAk \
  --stripe-price-id=price_1TZqYaBuKHqaI230iRwSlLnq \
  --env=prod \
  --no-interaction

php bin/console app:product:verify-pau-bundle \
  --product-code=pau_fisica_madrid_1996_2025 \
  --stripe-product-id=prod_UYyVHdqXdv3nAk \
  --stripe-price-id=price_1TZqYaBuKHqaI230iRwSlLnq \
  --env=prod \
  --no-interaction
```

Production verification output:

- Product ready: `pau-fisica-madrid-1996-2025`.
- Física product verified.
- Files: 3.
- Storage: `s3://clasesdeapoyosf`.

Matemáticas was also re-verified in production after the catalog generalization:

```bash
php bin/console app:product:verify-pau-bundle \
  --product-code=pau_matematicas_ii_madrid_1994_2025 \
  --stripe-product-id=prod_UYi73Jy72QltbK \
  --stripe-price-id=price_1TZpXeBuKHqaI2304ibQ6s3a \
  --env=prod \
  --no-interaction
```

## Production Smoke Checks

Checked live URLs under `https://www.clasesdeapoyo.com`:

- `/packs/pau-fisica-madrid-1996-2025` showed Física, 1996-2025, 9,99 EUR, 78/70 document counts, 786/170/616 page counts, and the checkout CTA.
- `/s/selectividad/madrid/fisica` showed the PAU Física pack promo and free-sample copy.
- `/s/selectividad/madrid/fisica/2021-modelo` showed locked file CTAs pointing to the Física pack.
- `/s/selectividad/madrid/quimica/2021-modelo` kept the generic Premium/register flow.

No real Stripe purchase was made during this deployment.

## Quality Gates

Local checks run before commit/deploy:

```bash
docker-compose exec -T php php bin/console lint:twig templates --no-interaction
docker-compose exec -T php composer stan
docker-compose exec -T php composer ci
```

Result:

- Twig lint passed.
- PHPStan passed.
- `composer ci` passed.
- PHPUnit ran but reported `No tests executed!`, matching the current project state.

## Follow-Ups

- Add automated tests for catalog-based pack context detection.
- Add tests for singular/plural free-sample names.
- Consider moving the catalog from PHP code into structured config if a third pack is added.
- Monitor whether Física converts similarly to Matemáticas before creating more packs.
- Later maintenance can remove or archive the old production stash entries and untracked backup/resource files once they are confirmed unnecessary.
