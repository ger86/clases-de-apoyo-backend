# exam-video

Remotion tool that turns one `exercise.json` into an explainer video (16:9, 1080p, 30 fps) with ElevenLabs narration, KaTeX math and synced captions. The same file can also hold a `reel`: a vertical 1080x1920 short for Instagram and TikTok. No per-exercise code: every scene is data.

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
node scripts/batch.mjs <slug> [<slug> ...]   # video AND reel for several exercises: tts + build-props + render + youtube for each format (--dry-run counts credits, --no-reel or --only-reel for one format)
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
| `cta` | closing call to action: headline, formula, two lines. Ends a reel |

Colors are semantic names from `src/theme.ts`: `given`, `danger`, `indet`, `solved`, `warn`, `teal`, `ink`, `inkSoft`, `muted`.

## Captions

Write one caption per narration sentence. The narration is what the voice reads (numbers in words), the caption is what the viewer sees (digits). `tts.mjs --dry-run` fails if the counts differ. `build-props.mjs` places each caption at the exact second the sentence starts, using the ElevenLabs character alignment.

## Voice

`voice` in `exercise.json` picks the voice; `voice.settings` changes how it performs, and `voice.reelSettings` changes it again for the reel only, because a 40 second short wants more energy than a 5 minute explainer:

```json
"voice": {
  "provider": "elevenlabs", "model": "eleven_v3",
  "voiceId": "KDuMsTRG03d18osdZP8V", "fallbackVoiceId": "JBFqnCBsd6RMkjVDRZzb",
  "reelSettings": { "stability": 0.28, "style": 0.65, "speed": 1.05 }
}
```

Defaults are `stability 0.4, similarity_boost 0.78, style 0.45, speed 1.02`, and anything you leave out keeps its default. Lower `stability` lets the voice vary more, which reads as livelier; higher `style` exaggerates its own manner. Both push it away from an even, neutral read, so change them in small steps and listen. `tts.mjs` prints the settings it is about to use on every run.

The other half of the delivery is the ElevenLabs v3 tags at the start of a narration: `[excited]`, `[cheerful]`, `[energetic]`, `[calm]`, `[didactic]`. They are stripped before the character count and before the captions are matched.

## Reel format (vertical 9:16)

A reel is the same exercise told as one idea in 30 to 60 seconds. It lives under a `reel` key in `exercise.json`:

```json
"reel": {
  "title": "internal name",
  "caption": "text of the Instagram post, hashtags included",
  "cover": { "kicker": "...", "line1": "...", "line2": "...", "tex": "...", "badge": "..." },
  "scenes": [ /* 3 to 5 scenes, same structure as the long video's scenes */ ]
}
```

Rules that keep a reel watchable:

- 3 to 5 scenes, 300 to 600 characters of narration in total, 30 to 60 seconds. `tts.mjs --reel --dry-run` and `build-props.mjs --reel` print a warning when you are outside those ranges.
- One idea only. The long video explains the exercise; the reel sells a single insight.
- The hook has to land in the first 2 seconds, so scene 1 is a `hook` whose title states the idea, not the exam reference.
- The last scene is a `cta` body with the call to action. Hook and CTA scenes hide the phase heading and the footer, so they read full bleed.

Layout comes from `src/layout.ts`, which keys off the composition size: the horizontal numbers are exactly what the 16:9 video always used, the vertical ones add the safe zones the apps need.

- Content box: 50 px on the left, 170 px on the right, so 860 px wide. Instagram and TikTok draw their button column over the right edge, so nothing readable goes there. Every vertical width in `layout.ts` is derived from that box, including the overhang the graph bodies draw past their declared width, so no body can reach into the gutter.
- Captions are pinned at y = 1060, in the middle third. The bottom of the frame belongs to the app's post text.
- No footer on vertical, and the top bar stacks the brand over the exam label on the left.
- Formulas do not shrink to fit. Author an explicit `size` per row, then look at `stills.mjs --reel` output before spending credits. Watch for two different failures: `rows`, `summary` and `cta` are text, so KaTeX wraps a row that is too wide onto a second, left-aligned line; `table`, `dots`, `normal` and `plot` are a grid and SVG, so they scale or clip instead of wrapping and a crowded frame is the only sign.
- Density limits measured on real scenes: a `table` stays readable up to about 4 columns (its type shrinks with the cell width, and at 7 columns the headers are too small to read even though they fit); a `summary` takes 4 rows plus the closing line, tightened automatically, and no more.

Compositions: `ExamVideo` (1920x1080), `ExamReel` (1080x1920), `Thumbnail` (1280x720), `ReelCover` (1080x1920).
