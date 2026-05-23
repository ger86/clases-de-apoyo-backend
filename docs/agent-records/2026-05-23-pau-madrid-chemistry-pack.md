# PAU Madrid Chemistry Pack Record

Date: 2026-05-23
Project: Clases de Apoyo backend
Status: generated locally, uploaded to S3, Stripe product/price created, local seed verified, production deployment pending
Primary product: `pau-quimica-madrid-1996-2025`

## Purpose

This document records the paid PAU bundle product for Química Madrid, created by repeating the generic bundle process documented in `docs/runbooks/create-pau-bundle.md`.

## Product

- Product code: `pau_quimica_madrid_1996_2025`
- Slug: `pau-quimica-madrid-1996-2025`
- Title: `Pack PAU Química Madrid 1996-2025`
- Price: `9.99 EUR`
- Target: PAU/Selectividad, Madrid, 2o Bachillerato, Química
- Positioning: focused one-time PDF download for students who only want Madrid Química PAU material.

## Source Data Audit

Production data was used because the local database can be stale.

Connection used:

```bash
ssh -o "IdentitiesOnly yes" -i /Users/gerardofernandez/Projects/ClasesDeApoyo/clasesdeapoyo.pem ubuntu@35.180.205.41
```

Production query exported `App\Entity\File` rows for:

- community slug: `madrid`
- subject slug: `quimica`
- ordered by year, exam weight, and file weight

Result:

- 139 source files.
- Years: 1996-2025.
- 74 enunciado/enunciados files.
- 65 solucion/soluciones files.

Important data detail:

- Química uses mixed casing and singular/plural labels: `enunciados`, `Enunciados`, `Enunciado`, `soluciones`, `Soluciones`, and `Solución`.

## Generated Files

Local working paths:

```text
var/generated-bundles/quimica-madrid-source.json
var/generated-bundles/PAU-Quimica-Madrid-1996-2025-manifest.json
var/generated-bundles/PAU-Quimica-Madrid-1996-2025-examenes-y-soluciones.pdf
var/generated-bundles/PAU-Quimica-Madrid-1996-2025-enunciados.pdf
var/generated-bundles/PAU-Quimica-Madrid-1996-2025-soluciones.pdf
var/product-downloads/pau-quimica-madrid-1996-2025/
```

Page counts:

- Complete PDF: 708 pages.
- Enunciados PDF: 168 pages.
- Soluciones PDF: 540 pages.

The PDFs were merged locally with `qpdf` from source PDFs downloaded from production S3. The EC2 instance was not used for heavy PDF generation.

## S3

Uploaded to both buckets so local and production verification can work:

- `s3://clasesdeapoyodev/product-downloads/pau-quimica-madrid-1996-2025/`
- `s3://clasesdeapoyosf/product-downloads/pau-quimica-madrid-1996-2025/`

Object keys:

```text
product-downloads/pau-quimica-madrid-1996-2025/PAU-Quimica-Madrid-1996-2025-examenes-y-soluciones.pdf
product-downloads/pau-quimica-madrid-1996-2025/PAU-Quimica-Madrid-1996-2025-enunciados.pdf
product-downloads/pau-quimica-madrid-1996-2025/PAU-Quimica-Madrid-1996-2025-soluciones.pdf
```

Uploaded sizes:

- Enunciados: 7,356,798 bytes.
- Complete: 17,788,392 bytes.
- Soluciones: 10,429,142 bytes.

## Stripe

Live Stripe Product:

- Product ID: `prod_UZIKuvCNWrX18q`
- Name: `Pack PAU Química Madrid 1996-2025`
- Metadata: `product_code=pau_quimica_madrid_1996_2025`

Live Stripe Price:

- Price ID: `price_1Ta9k9BuKHqaI230rcvRoyS1`
- Amount: `999 eur`
- Type: one-time
- Active: yes
- Metadata: `product_code=pau_quimica_madrid_1996_2025`

## Backend Changes

- Added a `PauBundleProductCatalog` definition for Química Madrid.
- Reused the generic bundle seed/verify commands and catalog-based promotion helpers.
- No schema or access-control code changes were needed.

## Local Seed And Verify

Local commands run successfully:

```bash
docker-compose exec -T php php bin/console app:product:seed-pau-bundle \
  --product-code=pau_quimica_madrid_1996_2025 \
  --stripe-product-id=prod_UZIKuvCNWrX18q \
  --stripe-price-id=price_1Ta9k9BuKHqaI230rcvRoyS1 \
  --no-interaction

docker-compose exec -T php php bin/console app:product:verify-pau-bundle \
  --product-code=pau_quimica_madrid_1996_2025 \
  --stripe-product-id=prod_UZIKuvCNWrX18q \
  --stripe-price-id=price_1Ta9k9BuKHqaI230rcvRoyS1 \
  --no-interaction
```

Verification output:

- Product ready: `pau-quimica-madrid-1996-2025`.
- Product verified: `pau-quimica-madrid-1996-2025`.
- Files: 3.
- Storage: `s3://clasesdeapoyodev`.

## Local HTTP Checks

Checked through `http://localhost:8080`:

- `/packs/pau-quimica-madrid-1996-2025` showed Química, 1996-2025, 9,99 EUR, 74/65 document counts, 708/168/540 page counts, and checkout CTA.
- `/s/selectividad/madrid/quimica` showed the PAU Química pack promo.
- `/s/selectividad/madrid/quimica/2021-modelo` showed locked file CTAs pointing to the Química pack.

## Production Deployment

Pending.

## Production Seed And Verify

Pending.

## Production Smoke Checks

Pending.

## Quality Gates

Pending.

## Follow-Ups

- Monitor whether Química converts similarly to Matemáticas and Física.
- Consider moving the catalog from PHP code into structured config if bundle creation continues.
