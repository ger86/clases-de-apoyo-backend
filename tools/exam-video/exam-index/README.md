# Exam index: PAU Madrid Matemáticas II

`pau-madrid-matematicas-ii.json` lists every exercise of the 91 Madrid Matemáticas II exams stored in production (Selectividad, PAU, EvAU, 1994 to 2026): 722 exercises. Each exercise has one entry; when the exam offers alternatives (Opción A/B, Pregunta 4.1/4.2), each alternative is its own entry. Apartados are covered by the tags and the summary.

Fields: `year`, `call` (modelo, junio, septiembre, julio-extraordinaria), `variant` (only 2010: "F.G." or "F.M."), `examName`, `examSlug`, `examUrl`, `order`, `label` (as the exam names it), `points` (null if not printed), `block` (Análisis, Álgebra, Geometría, Probabilidad/Estadística), `tags`, `summary` (Spanish, formulas in ASCII), `statementFileId`, `statementS3Key`, `solutionFileId`, `solutionS3Key`. Sorted by year desc, then call (modelo, junio, septiembre/julio), then order.

Built in November 2026 from the statement PDFs (text layer from `pdftotext -layout`, with pages rendered and read visually where formulas were garbled). Every exercise tagged with a continuity or derivability tag was checked against the rendered page.

## Tags

- Análisis: continuidad, derivabilidad, parametros-continuidad-derivabilidad, limites, lhopital, asintotas, monotonia-extremos, curvatura-inflexion, representacion-grafica, optimizacion, recta-tangente, teorema-bolzano, teorema-rolle-valor-medio, integral-indefinida, integral-definida, area-recinto, calculo-primitiva-con-condiciones, funcion-a-trozos, valor-absoluto, derivada-definicion-calculo.
- Álgebra: sistemas-discusion-parametro, sistemas-planteamiento (system with no parameter, often a word problem), rango-matriz, matriz-inversa, ecuacion-matricial, potencias-matriz, determinantes, matrices-operaciones.
- Geometría: rectas-planos-posicion, ecuaciones-rectas-planos (find a line or plane), distancias, angulos, simetricos-proyecciones, producto-vectorial-mixto-areas-volumenes, vectores-dependencia-lineal, lugar-geometrico (loci, conics), esfera (spheres and quadrics, old exams).
- Probabilidad: probabilidad-sucesos, probabilidad-condicionada-bayes, binomial, normal, aproximacion-normal-binomial.

Tags added to the original vocabulary: sistemas-planteamiento, matrices-operaciones, ecuaciones-rectas-planos, vectores-dependencia-lineal, probabilidad-sucesos, lugar-geometrico, derivada-definicion-calculo, esfera.

`parametros-continuidad-derivabilidad` means "find a, b... so that f is continuous and/or differentiable". `continuidad` and `derivabilidad` mean "study it" with no parameters. Query both when you want any continuity/derivability exercise. `funcion-a-trozos` and `valor-absoluto` are added on top.

## Query with jq

```bash
jq '.[] | select(.tags | index("parametros-continuidad-derivabilidad")) | {examSlug, label, summary}' pau-madrid-matematicas-ii.json
jq '.[] | select(.tags | index("derivabilidad") or index("parametros-continuidad-derivabilidad")) | "\(.examSlug) \(.label): \(.summary)"' pau-madrid-matematicas-ii.json
jq '.[] | select(.block == "Probabilidad/Estadística" and .year >= 2020) | {examSlug, label}' pau-madrid-matematicas-ii.json
```

## Download a PDF (read-only)

From the backend root: `set -a; eval "$(grep -E '^S3_' .env.local)"; set +a; AWS_ACCESS_KEY_ID=$S3_ACCESS_KEY AWS_SECRET_ACCESS_KEY=$S3_SECRET_KEY aws s3 cp s3://clasesdeapoyosf/<statementS3Key or solutionS3Key> ./exam.pdf --region eu-west-3`. Never write to the bucket.

## Gaps and known issues

- No solution PDF (solutionFileId null): 2019-modelo (its statement PDF has the solutions from page 3), 1999-junio, 1998-junio, 1998-septiembre, 1997-septiembre, 1996-junio, 1995-septiembre.
- 2023-modelo (file 1732) prints the header "MODELO Curso 2021/2022", but its content differs from 2022-modelo-2 (file 1706). It is indexed as listed in production.
- Incomplete statements in the source PDF: 2006-modelo Opción A Ejercicio 4 b, 2006-septiembre Opción A Ejercicio 2 a (condition missing, read as continuity), 1994-septiembre Opción B Ejercicio 1 (no question asked).
- A few printed headers disagree with the sum of their apartados. `points` is 2.5 (apartados) for Opción A Ejercicio 4 of 2019-junio, 2019-julio-extraordinaria, 2018-junio-ordinaria and 2018-julio-extraordinaria; it keeps the printed value for 2012-septiembre Opción B Ejercicio 4 (2) and 1999-septiembre Opción B Ejercicio 2 (3).
