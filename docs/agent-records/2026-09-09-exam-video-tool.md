# Exam video tool and PAU Madrid Pregunta 3 video

Date: 2026-09-09

## What changed

The one-off Remotion project under the ignored `var/generated-videos/.../remotion/` folder (see the 2026-05-27 record) was promoted to a reusable tool in [tools/exam-video](../../tools/exam-video). The per-exercise React code is gone; every scene is now data in an `exercise.json` with five generic body types (hook, rows, dots, normal, summary).

Improvements over the v4 prototype:

- Durations are measured with `ffprobe`, not typed by hand.
- Captions are placed with the ElevenLabs character alignment (`/with-timestamps`), so each sentence appears when it is spoken.
- `tts.mjs --dry-run` reports the credit cost and validates that captions and narration sentences match, before anything is spent.
- Stills are derived from the timeline, so they cannot drift from it.
- Voice fallback: the library voice needs a paid plan; the script falls back to George automatically.

Procedure: [docs/runbooks/create-exam-video.md](../runbooks/create-exam-video.md).

## First exercise: Pregunta 3 (aceitunas, binomial y normal)

Slug: `pau-madrid-matematicas-pregunta-3-aceitunas`. 13 scenes, 3,844 characters of narration, about 4:51 of video. The math in the official solution was verified independently (0.95^12 = 0.5404; sigma = 4.98; z = 2.72; result 0.0033) and is correct.

Open points:

- The exercise is Pregunta 3 of the Comunidad de Madrid model exam, Matemáticas II (Ciencias), 2025/2026. `exam.label`, the hook kicker, and the YouTube metadata carry that label.
- The voice is George (default ElevenLabs voice), not the chosen Spain-accent library voice, because the account is on the free plan.
- Nobody has listened to the full narration yet.

## Second exercise: Pregunta 2 (f(x) = (x²+1)/(|x|+1)), 2026-09-10

The official solution had an error in part b (final value written as Ln(2) − 1/2 instead of 2·Ln(2) − 1/2 = Ln(4) − 1/2) and a typo in the polynomial division line (denominators x+1 instead of x−1). The user corrected the PDF (`tools/ejercicio-2.pdf`) before the video was made.

Slug: `pau-madrid-matematicas-ii-2025-2026-pregunta-2-valor-absoluto`. 14 scenes, 4,468 characters, about 5:03. Two scene types were added for it: `table` (sign table) and `plot` (function graph with points and shaded area). Credits used so far: 3,844 + 4,468 = 8,312 of the 10,000 free monthly credits.

## Third exercise: Pregunta 4.1 (matrices con parámetro), 2026-09-10

Slug: `pau-madrid-matematicas-ii-2025-2026-pregunta-4-1-matrices`. 7 scenes, 1,178 characters, about 1:35. Kept deliberately short because only about 1,700 free credits were left. Verified: A·Aᵗ diagonal for a = ±1; (A − B)(A + B) = A² − B² only for a = 1 (A·B = B·A). Credits used this month: about 9,490 of 10,000.

## Fourth exercise: PAU Madrid Junio 2026, Pregunta 1 (rango y ecuación matricial), 2026-09-15

The user upgraded ElevenLabs and created an instant clone of their own voice (`KDuMsTRG03d18osdZP8V`). It is now the default `voiceId` in every `exercise.json` and in the studio demo; George stays as fallback. Earlier videos still carry the George audio unless regenerated with `--force`.

Slug: `pau-madrid-matematicas-ii-2026-junio-pregunta-1-rango-ecuacion-matricial`. 14 scenes, 2,975 characters, about 3:18. Verified: |A| = (λ − 1)(λ − 3); rg A = 3 unless λ = 1 or 3 (rg 2); for λ = 2, X = A − A⁻¹ = [[2,0,0],[1,0,0],[0,0,2]]. The PDF shows a wrong matrix for λ = 3 ([[1,1,2],[0,1,1],[1,0,1]] instead of [[1,1,3],[0,1,2],[1,0,1]]); the rank conclusion is unaffected and the video uses the correct matrix.

Tool changes: `youtube.mjs` now re-reads `exam` and `youtube` from `exercise.json`, so text edits need no props rebuild; row labels with non-ASCII characters (λ) are no longer uppercased; rows holding 3-row matrices count as tall for the density scaling.

## Reel format (vertical 9:16) and first reel, 2026-09-15

The tool now produces vertical shorts for Instagram and TikTok from the same `exercise.json`. A `reel` key holds 3 to 5 short scenes plus the post metadata (title, caption with hashtags, cover), and `tts`, `build-props`, `stills`, `render` and `youtube` all take `--reel`. Audio goes to `public/exercises/<slug>/audio/reel/`, props to `props.reel.json` (added to `.gitignore`), output to `output/<slug>/reel/`.

Tool changes:

- `src/layout.ts` is new: one table of numbers per frame shape, selected from `useVideoConfig()`. The horizontal column is a literal copy of the values the components hardcoded before, so the 16:9 videos are unaffected. This was verified, not assumed: the 14 stills of Pregunta 1 were rendered with the original code and with the refactor against the same `props.json` and came out byte-identical. Two regressions were caught that way and fixed (the top bar lost its `space-between`, and a `maxWidth` added to the summary boxes rewrapped the closing line).
- Vertical safe zones: content box 50 px left and 170 px right, because both apps draw a button column over the right edge; captions pinned at y = 1060, in the middle third, because the bottom carries the post text; no footer; the top bar stacks the brand over the exam label on the left.
- New `cta` body type for the closing call to action. `hook` and `cta` scenes now hide the phase heading and the footer in both formats.
- New `ExamReel` (1080x1920) and `ReelCover` (1080x1920) compositions. `Video.tsx` reads the audio folder from a new `audioPath` prop, defaulting to `audio`.
- `tts.mjs --reel --dry-run` and `build-props.mjs --reel` warn outside 3 to 5 scenes, 300 to 600 characters and 30 to 60 seconds.
- The vertical widths are derived from the safe box, not typed one by one. The first version hand-picked five numbers (820, 830, 840, ...) that did not agree with each other; they were replaced by one `V_BOX = 1080 - 50 - 170` and a subtraction per body for the overhang the graph bodies draw past their declared width.
- Formulas are not auto-fitted. An auto-scaling `FitTex` using `delayRender` was considered and dropped: a handle left uncontinued hangs the render, and `scrollWidth` can be measured before the KaTeX stylesheet settles. The still review is the gate instead, which is what the runbook already asks for.

First reel: slug `pau-madrid-matematicas-ii-2026-junio-pregunta-1-rango-ecuacion-matricial`, 5 scenes, 577 characters, 39.9 s, cloned voice. Single idea: with matrices you cannot clear X the way you do with numbers.

The mistake shown in the reel was chosen after checking the algebra. The obvious candidate, multiplying by A⁻¹ on the wrong side, is not usable here: for this matrix `(A² − I)A⁻¹` equals `A·X·A⁻¹` equals X, so the "wrong" move accidentally gives the right answer. The reel uses cancelling the A instead: `A² − AX = I` does not give `A − X = I`, because that would mean `X = A − I = [[0,0,2],[0,0,1],[1,0,0]]`, and substituting gives `A² − A·X = A`, not I. Verified with exact integer arithmetic, along with `A⁻¹`, `X = A − A⁻¹ = [[2,0,0],[1,0,0],[0,0,2]]` and `|A| = (λ − 1)(λ − 3)` for λ from −3 to 5.

The reel itself only exercises `hook`, `rows` and `cta`, so the other five body types were rendered vertically on purpose before claiming the format is reusable: a throwaway `props.reel.json` (gitignored, deleted afterwards) with the `dots`, `normal` and `summary` scenes of the aceitunas exercise and the `table`, `plot` and 4-row `summary` scenes of Pregunta 2, rendered with `stills.mjs --reel` and reviewed with the safe zones drawn over them. It costs no credits and it found three real defects, all vertical only:

- The 7-column sign table was 960 px wide against an 860 px box, so it ran under the button column, because `tableWidth` was being read as the whole table while the label column was added on top of it. The vertical value is now `V_BOX - tableLabelWidth`, and the table type scales with the cell width.
- A 4-row `summary` overflowed its stack in both directions at once, printing the first row over the heading and the closing line over the captions. Vertical rows stack label over value, which the numbers had been chosen without accounting for. Four rows or more now render tighter, and the vertical stack starts 40 px higher.
- `dots`, `normal` and `plot` were fine, but they clip or crowd rather than wrap, which the README claimed only for rows. The docs now separate the two failure modes, because the still review is the only thing catching either.

Open point: nobody has listened to the reel narration yet.

## Six more reels: the rest of PAU Madrid June 2026, 2026-09-16

One reel for each remaining exercise of the exam, same format, 5 scenes and about 40 seconds each, 3,497 credits in total. Each one teaches a single mistake, and every mistake was checked numerically before it was written, not after:

- Pregunta 2 (jardín): deriving the cost with two variables. The relation comes from similar triangles, y = 3/4 (56 - x); x = 28, y = 21, 47,040 euros, checked against the cost at x = 20 and x = 35.
- Pregunta 3 (simétrico): answering M, the projection, or M - P. M - P = (2, 2, -1), whose midpoint with P is (2, 1, -1/2), not M. The right answer P' = 2M - P = (6, 4, -2) puts P and P' both 3 units from the plane.
- Pregunta 4.1 (baterías): reusing the probability you just computed as the p of the binomial. With p = 0.1587 the answer is 5e-7, which is the probability of nine defective ones; the question asks for nine good ones, so p = 0.8413 and the answer is 0.5127.
- Pregunta 4.2 (condicionada): reading P(B|A) = 0.2 as P(A∩B) = 0.2. That gives P(A) = 0.4, and then P(B|A) would be 0.5, contradicting the statement.
- Pregunta 5.1 (área): integrating ln x from 0. Between 0 and 1 the logarithm is negative and unbounded, so there is no region there; the region starts at x = 1 because ln 1 = 0. The reel deliberately does not write the bracket at 0: that integral converges to 0, which is a true but improper result and would teach a second error to students who have not seen improper integrals.
- Pregunta 5.2 (a trozos): looking for a in the continuity condition. sin(a·0) = 0 for every a, so continuity only gives b = 2 and a = -2 comes from the derivatives.

The still review was automated this time, on top of looking at the frames: for each of the 30 stills, the brightest pixel inside the right safe zone (x from 910 to 1080, below the top bar) was measured with ffmpeg signalstats. All 30 read 37, the background, against 226 inside the content box, which proves nothing was drawn under the button column. The same check on the 18 px band above the captions caught nothing either.

`batch.mjs` now produces both formats for every slug instead of only the video: the four steps for the 16:9 explainer, then the same four with `--reel` when that `exercise.json` has a `reel` section. It is the difference between remembering to make the reel and getting one by default. An exercise with no `reel` section is not silently skipped: it still gets its video and the summary marks it "sin reel", because a missing reel is almost always an oversight rather than a decision. `--no-reel` and `--only-reel` cover the two one-format cases.

Found while writing the Pregunta 5.2 reel: scene `06-derivada-der` of the long video says "uno menos dos x más x al cuadrado, que es uno menos x al cuadrado", which spoken aloud is 1 - x². The factor is (1 - x)², which is what the TeX and the caption of that scene show, so only the narration is wrong. The reel writes and narrates it unambiguously. Fixing the long video means regenerating that one clip and rendering again; it has not been done.

## Whole exam batch: PAU Madrid Junio 2026, Preguntas 2 to 5.2, 2026-09-15

The six remaining questions of `tools/junio-2026.pdf` were produced in one pass. Three forked agents wrote two `exercise.json` each in parallel (editing only their own exercise folders) and reviewed their stills; the main session verified all results independently before that, spot-checked stills, then ran the new `scripts/batch.mjs` (tts, build-props, render, youtube per slug) detached with `nohup`, since a full exam exceeds the 10-minute limit of a background shell command.

| Slug | Scenes | Chars | Length |
|---|---|---|---|
| pau-madrid-matematicas-ii-2026-junio-pregunta-2-optimizacion-jardin | 11 | 2,931 | 3:19 |
| pau-madrid-matematicas-ii-2026-junio-pregunta-3-simetrico-plano-recta | 13 | 3,440 | 4:06 |
| pau-madrid-matematicas-ii-2026-junio-pregunta-4-1-normal-binomial-baterias | 12 | 2,864 | 3:18 |
| pau-madrid-matematicas-ii-2026-junio-pregunta-4-2-probabilidad-condicionada | 11 | 2,584 | 2:53 |
| pau-madrid-matematicas-ii-2026-junio-pregunta-5-1-area-logaritmo | 8 | 1,872 | 2:20 |
| pau-madrid-matematicas-ii-2026-junio-pregunta-5-2-continuidad-derivabilidad-tangente | 12 | 3,054 | 3:33 |

Total about 16,700 credits, 18 minutes of batch time, all six with the cloned voice and full caption alignment. No errors found in the PDF solutions. Pregunta 3 covers a, b1 and b2 and says the student answers only one of b1/b2. Pregunta 2 adds the minimum cost (47,040 EUR), which the PDF does not state.

Thumbnail fix: long formulas made the formula box grow leftwards over the title. The box is now capped at 560 px and the formula shrinks by length.

## Voice delivery is configurable, and a reel outside the PAU exams, 2026-09-17

The voice settings were hardcoded in `tts.mjs`. They are now defaults that `voice.settings` overrides per exercise, and `voice.reelSettings` overrides again for the reel only, which is the case that matters: a 40 second short wants more energy than a 5 minute explainer. `tts.mjs` prints the settings it is about to use, so what a clip was recorded with is visible in the log rather than guessed. The voice id is untouched by all of this: the request is that the same cloned voice performs differently, not that it becomes another voice.

Lively profile, asked for and used by the first exercise outside the PAU exams: `stability 0.28` (down from 0.4, so the voice varies more), `style 0.65` (up from 0.45, which exaggerates its own manner) and `speed 1.05`. Plus the v3 tags `[excited]` and `[cheerful]` instead of `[energetic]`. Earlier reels keep their audio; changing the settings regenerates nothing without `--force`.

First exercise from a worksheet rather than an exam: `matrices-ejercicio-1-matriz-traspuesta`, from `matrices_ejercicio_1.pdf` (transposes of a 4x2, a 3x3 and a 3x5 matrix). The PDF's solutions were checked element by element and are correct. The reel teaches that transposing is not rotating: rotating A a quarter turn gives [[7,0,3,-1],[4,7,-1,2]], the same numbers in the wrong order, against the transpose [[-1,3,0,7],[2,-1,7,4]]. 5 scenes, 516 characters, 33 seconds. It has no `scenes` key and no `youtube` key, which the reel path does not need, so an exercise can exist as a reel only.
