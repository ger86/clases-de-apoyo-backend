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
