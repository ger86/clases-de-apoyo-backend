# PAU Madrid Matemáticas CCSS Pack Record

Date: 2026-05-25
Project: Clases de Apoyo backend
Status: generated locally, uploaded to S3, Stripe product/price created, deployed to production, and verified
Primary product: `pau-matematicas-ccss-madrid-1995-2025`

## Purpose

This document records the paid PAU bundle product for Matemáticas aplicadas a las Ciencias Sociales Madrid, created by repeating the generic bundle process documented in `docs/runbooks/create-pau-bundle.md`.

## Product

- Product code: `pau_matematicas_ccss_madrid_1995_2025`
- Slug: `pau-matematicas-ccss-madrid-1995-2025`
- Title: `Pack PAU Matemáticas CCSS Madrid 1995-2025`
- Price: `9.99 EUR`
- Target: PAU/Selectividad, Madrid, 2o Bachillerato, Matemáticas CCSS
- Positioning: focused one-time PDF download for students who only want Madrid Matemáticas CCSS PAU material.

## Source Data Audit

Production data was used because the local database was stale.

Production query exported `App\Entity\File` rows for:

- community slug: `madrid`
- subject slug: `matematicas-cc-ss`
- course slug: `2o-bachillerato`
- ordered by year, exam weight, exam id, file weight, and file id

Result:

- 75 production exam rows.
- 74 exams with downloadable files.
- 136 source files.
- Years: 1995-2025.
- 74 enunciado/enunciados files.
- 62 solucion/soluciones files.

Important data detail:

- 12 exams have enunciado files but no solution file in production: 1995 septiembre; 1996 septiembre; 1996 junio; 1997 septiembre; 1997 junio; 1998 septiembre; 1998 junio; 1999 junio; 2000 septiembre; 2000 junio; 2010 septiembre F-M; 2019 modelo.

## Generated Files

Local working paths:

```text
var/generated-bundles/matematicas-ccss-madrid-source.json
var/generated-bundles/PAU-Matematicas-CCSS-Madrid-1995-2025-manifest.json
var/generated-bundles/PAU-Matematicas-CCSS-Madrid-1995-2025-examenes-y-soluciones.pdf
var/generated-bundles/PAU-Matematicas-CCSS-Madrid-1995-2025-enunciados.pdf
var/generated-bundles/PAU-Matematicas-CCSS-Madrid-1995-2025-soluciones.pdf
var/product-downloads/pau-matematicas-ccss-madrid-1995-2025/
```

Page counts:

- Complete PDF: 696 pages.
- Enunciados PDF: 165 pages.
- Soluciones PDF: 531 pages.

The PDFs were merged locally with `qpdf` from source PDFs downloaded from production S3. The EC2 instance was not used for heavy PDF generation.

## S3

Uploaded to both buckets so local and production verification can work:

- `s3://clasesdeapoyodev/product-downloads/pau-matematicas-ccss-madrid-1995-2025/`
- `s3://clasesdeapoyosf/product-downloads/pau-matematicas-ccss-madrid-1995-2025/`

Object keys:

```text
product-downloads/pau-matematicas-ccss-madrid-1995-2025/PAU-Matematicas-CCSS-Madrid-1995-2025-examenes-y-soluciones.pdf
product-downloads/pau-matematicas-ccss-madrid-1995-2025/PAU-Matematicas-CCSS-Madrid-1995-2025-enunciados.pdf
product-downloads/pau-matematicas-ccss-madrid-1995-2025/PAU-Matematicas-CCSS-Madrid-1995-2025-soluciones.pdf
```

Uploaded sizes:

- Enunciados: 7,980,624 bytes.
- Complete: 18,926,693 bytes.
- Soluciones: 10,943,411 bytes.

## Stripe

Live Stripe Product:

- Product ID: `prod_Ua4M8h3tjmTaOw`
- Name: `Pack PAU Matemáticas CCSS Madrid 1995-2025`
- Metadata: `product_code=pau_matematicas_ccss_madrid_1995_2025`

Live Stripe Price:

- Price ID: `price_1TauE5BuKHqaI230PHelYoYv`
- Amount: `999 eur`
- Type: one-time
- Active: yes
- Metadata: `product_code=pau_matematicas_ccss_madrid_1995_2025`

## Backend Changes

- Added a `PauBundleProductCatalog` definition for Matemáticas CCSS Madrid.
- Reused the generic bundle seed/verify commands and catalog-based promotion helpers.
- No schema or access-control code changes were needed.

## Local Seed And Verify

Local commands run successfully:

```bash
docker-compose exec -T php php bin/console app:product:seed-pau-bundle \
  --product-code=pau_matematicas_ccss_madrid_1995_2025 \
  --stripe-product-id=prod_Ua4M8h3tjmTaOw \
  --stripe-price-id=price_1TauE5BuKHqaI230PHelYoYv \
  --no-interaction

docker-compose exec -T php php bin/console app:product:verify-pau-bundle \
  --product-code=pau_matematicas_ccss_madrid_1995_2025 \
  --stripe-product-id=prod_Ua4M8h3tjmTaOw \
  --stripe-price-id=price_1TauE5BuKHqaI230PHelYoYv \
  --no-interaction
```

Verification output:

```text
Producto listo: pau-matematicas-ccss-madrid-1995-2025
Producto verificado: pau-matematicas-ccss-madrid-1995-2025
Archivos: 3
Almacenamiento: s3://clasesdeapoyodev
```

## Local Smoke Tests

Rendered locally:

- `/packs/pau-matematicas-ccss-madrid-1995-2025` showed the product title, `9,99`, one-time payment copy, and page counts.
- `/packs` showed the product card.
- `/s/selectividad/madrid/matematicas-cc-ss` showed the pack promo with `1995-2025` and `Ver pack por 9,99`.

## Production Seed Command

Production commands run successfully after deployment:

```bash
cd /var/www/clasesdeapoyo
php bin/console app:product:seed-pau-bundle \
  --product-code=pau_matematicas_ccss_madrid_1995_2025 \
  --stripe-product-id=prod_Ua4M8h3tjmTaOw \
  --stripe-price-id=price_1TauE5BuKHqaI230PHelYoYv \
  --env=prod \
  --no-interaction

php bin/console app:product:verify-pau-bundle \
  --product-code=pau_matematicas_ccss_madrid_1995_2025 \
  --stripe-product-id=prod_Ua4M8h3tjmTaOw \
  --stripe-price-id=price_1TauE5BuKHqaI230PHelYoYv \
  --env=prod \
  --no-interaction
```

Verification output:

```text
Producto listo: pau-matematicas-ccss-madrid-1995-2025
Producto verificado: pau-matematicas-ccss-madrid-1995-2025
Archivos: 3
Almacenamiento: s3://clasesdeapoyosf
```

The production sitemap was rebuilt with:

```bash
php bin/console app:sitemap:build --env=prod --no-interaction
```

## Production Smoke Tests

Verified on the canonical `www` URLs:

- `/packs/pau-matematicas-ccss-madrid-1995-2025` showed the product title, `9,99`, one-time payment copy, and page counts.
- `/packs` showed the product card.
- `/s/selectividad/madrid/matematicas-cc-ss` showed the pack promo with `1995-2025` and `Ver pack por 9,99`.
- `/sitemap/products.xml` included `/packs/pau-matematicas-ccss-madrid-1995-2025`.

Deployment used the standard wrapper:

```bash
cd /var/www
./prepare_cda_coffe
```

Post-deploy cleanup:

- Stashed only the generated tracked `config/reference.php` diff on EC2.
- Left unrelated untracked server files untouched.
