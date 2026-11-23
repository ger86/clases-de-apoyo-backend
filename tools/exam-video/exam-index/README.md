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

# Exam index: PAU Madrid Matemáticas Aplicadas a las CC.SS. II

`pau-madrid-matematicas-cc-ss.json` lists every exercise of the 76 Madrid Matemáticas CC.SS. exams stored in production (subject slug `matematicas-cc-ss`, 1995 to 2026): 656 exercises. Same fields, same one-entry-per-alternative rule and same sort order as the Matemáticas II index above. `examUrl` uses `/s/selectividad/madrid/matematicas-cc-ss/<examSlug>`, with the production slug (some end in `-1`, for example `2026-junio-1`).

Built in November 2026 from the statement PDFs: text layer from `pdftotext -layout`, and every exercise with matrices, piecewise functions or inequality systems checked against the rendered page. Grading criteria, syllabus and normal tables appended to some PDFs (2019-modelo, 2012-junio, 2005-modelo) are not indexed.

`block` is `Álgebra`, `Análisis` or `Probabilidad/Estadística`, so the jq queries above work on both files. Inference exercises are told apart by their tags.

## Tags (CC.SS.)

- Álgebra: matrices-operaciones, matriz-inversa, ecuacion-matricial, determinantes, rango-matriz, potencias-matriz, sistemas-discusion-parametro, sistemas-planteamiento, programacion-lineal, programacion-lineal-planteamiento (word problem to set up, always together with programacion-lineal), region-factible (draw the region or find its vertices).
- Análisis: dominio, limites, continuidad, derivabilidad, parametros-continuidad-derivabilidad, parametros-funcion (find coefficients from other conditions: an extremum, a point, a tangent, an integral), asintotas, monotonia-extremos, curvatura-inflexion, representacion-grafica, optimizacion, recta-tangente, integral-indefinida, integral-definida, area-recinto, calculo-primitiva-con-condiciones, funcion-a-trozos, valor-absoluto, funcion-contexto (the function models costs, profits, population, etc.).
- Probabilidad: probabilidad-sucesos, probabilidad-total, probabilidad-condicionada-bayes, independencia-sucesos, binomial, normal, aproximacion-normal-binomial.
- Inferencia: distribucion-media-muestral, distribucion-proporcion-muestral, intervalo-confianza-media, intervalo-confianza-proporcion, tamano-muestra, error-maximo, contraste-hipotesis (old exams only).

```bash
jq '.[] | select(.tags | index("programacion-lineal-planteamiento")) | {examSlug, label, summary}' pau-madrid-matematicas-cc-ss.json
jq '.[] | select(.tags | index("intervalo-confianza-media") or index("tamano-muestra")) | "\(.examSlug) \(.label): \(.summary)"' pau-madrid-matematicas-cc-ss.json
```

## Gaps and known issues (CC.SS.)

- Not in production: 2022, 2023 and 2024, and the 2025 and 2026 modelos. The exam "1995 Otros" (slug `1995-otros`) exists but has no files, so it is not indexed.
- No solution PDF (solutionFileId null): 2019-modelo, 2010-septiembre-f-m, 2000-septiembre, 2000-junio, 1999-junio, 1998-septiembre, 1998-junio, 1997-septiembre, 1997-junio, 1996-septiembre, 1996-junio, 1995-septiembre.
- No points printed (points null): 1998-junio, 1997-junio, 1996-septiembre, 1996-junio. 1998-septiembre Opción B Ejercicio 3 prints 3 points, so Opción B adds up to 11; the printed value is kept.
- 2007-modelo: page 1 says the UCM model is the June 2006 exam; pages 2 and 3 hold a separate "Modelo 2007", which is the one indexed. 2009-junio prints both "JUNIO 2009" and "EXAMEN MODELO", but its content differs from 2009-modelo.
- The headers of 2003, 2004, 2005, 2006 and 2007 septiembre read "MATEMÁTICAS I", but the content is the CC.SS. II exam.
- Typos or garbled symbols in the source: 2025-junio Pregunta 4.2 (asks for a value already given), 2026-julio-extraordinaria-1 Pregunta 4.2 (annual vs monthly spending), 2020-junio B.2 ("x >= 3x"), 2019-junio Opción B Ejercicio 3 (x=3 in two pieces), 2018-julio-extraordinaria Opción B Ejercicio 4 (overlapping expressions, question unclear), 2016-septiembre Opción A Ejercicio 4 and 2015-septiembre Opción A Ejercicio 4 (complement bars missing), 2011-septiembre Opción B Ejercicio 1 ("eA" for "cA"), 2009-septiembre Opción A Ejercicio 4 (mean 1.36 probably 4.36), 2002-junio Opción A Ejercicio 1 (D defined but unused), 1999-junio Opción B Ejercicio 2 (2x^2 probably 2x^3), 1997-junio Opción A Ejercicio 1 (values in b) differ from a)).
