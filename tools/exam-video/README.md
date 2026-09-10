# exam-video

Remotion tool that turns one `exercise.json` into an explainer video (16:9, 1080p, 30 fps) with ElevenLabs narration, KaTeX math and synced captions. No per-exercise code: every scene is data.

See [docs/runbooks/create-exam-video.md](../../docs/runbooks/create-exam-video.md) for the full procedure.

## Commands

From `tools/exam-video/`:

```bash
npm install                                  # first time only
node scripts/tts.mjs <slug> --dry-run        # count credits, validate captions
node scripts/tts.mjs <slug>                  # generate audio + alignment (spends credits)
node scripts/build-props.mjs <slug>          # measure durations, sync captions -> props.json
node scripts/stills.mjs <slug>               # one review PNG per scene
node scripts/render.mjs <slug>               # final MP4 -> output/<slug>/<slug>.mp4
node scripts/youtube.mjs <slug>              # youtube.md (title, description, chapters, tags), .es.srt subtitles, thumbnail.png
npx remotion studio --props=public/exercises/<slug>/props.json   # interactive preview
```

`<slug>` is the folder name under `public/exercises/`. The `exercise.json` inside is tracked in git; audio, props and output are ignored.

## Scene body types

| type | use for |
|---|---|
| `hook` | opening scene: kicker, title with `*highlight*`, optional big formula |
| `rows` | progressive list of TeX or text rows, optional chips and result badge |
| `dots` | N items (olives, cards, trials) popping in, with highlights |
| `normal` | bell curve with shaded tail (mu, sigma, cut) |
| `table` | sign table: TeX column headers, rows of colored cells |
| `plot` | graph of a function (`fn` is a JavaScript expression in x), marked points, shaded area |
| `summary` | closing boxes, one per result |

Colors are semantic names from `src/theme.ts`: `given`, `danger`, `indet`, `solved`, `warn`, `teal`, `ink`, `inkSoft`, `muted`.

## Captions

Write one caption per narration sentence. The narration is what the voice reads (numbers in words), the caption is what the viewer sees (digits). `tts.mjs --dry-run` fails if the counts differ. `build-props.mjs` places each caption at the exact second the sentence starts, using the ElevenLabs character alignment.
