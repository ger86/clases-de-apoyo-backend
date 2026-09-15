# PAU Madrid Video Creation Experiment

Date: 2026-05-27

This record documents the first local video-production experiment for ClasesDeApoyo exam solutions, so the work can be resumed without relying on chat history.

## Goal

Create a local review video for:

- Page: `https://www.clasesdeapoyo.com/s/selectividad/madrid/matematicas/2025-julio-extraordinaria-1`
- Subject: Matemáticas II, Madrid, PAU 2025 julio extraordinaria
- Exercise: Pregunta 1.1
- Format target: YouTube-style 16:9 explainer, roughly 3 minutes, Spanish from Spain, didactic but not boring.

The video is not deployed and is not committed as a generated asset. It lives under ignored `var/`.

## Local Workspace

Base directory:

```text
var/generated-videos/madrid-matematicas-2025-julio-extraordinaria-1-pregunta-1-1
```

Important files:

```text
source/enunciado.pdf
source/solucion.pdf
work/video_plan_v3.json
work/render_slides_v3.mjs
work/generate_elevenlabs_audio_v3.mjs
work/assemble_video_v3.mjs
work/png-v3/
work/audio-elevenlabs-v3/
work/voice-benchmarks-elevenlabs/
output/pau-madrid-2025-matematicas-pregunta-1-1-elevenlabs-v3.mp4
output/youtube_metadata_v3.json
output/review-frames-v3/
output/README.md
```

## Source Material

The official statement and solution PDFs were downloaded from production S3 using the backend `.env.local` S3 configuration. Do not paste or document secrets.

Local copies:

```text
source/enunciado.pdf
source/solucion.pdf
```

Mathematical result used in the narration:

- `|A| = k(k+1)^2`
- `k != 0, -1`: sistema compatible determinado.
- `k = -1`: sistema incompatible.
- `k = 0`: sistema compatible indeterminado.
- For `k = 0`: `(x, y, z) = (-lambda, lambda, -lambda)`, with `lambda` real.

## What Was Tried

### v1: Basic local video

Output:

```text
output/pau-madrid-2025-matematicas-pregunta-1-1.mp4
```

This was an initial proof of concept using local voice synthesis and simple slides. It was useful only to validate that a video could be assembled locally from extracted exam content.

### OpenAI TTS version

Output:

```text
output/pau-madrid-2025-matematicas-pregunta-1-1-openai-voice.mp4
```

Used OpenAI TTS with Spanish-from-Spain instructions. Result was not good enough because accent control was unreliable and the voice sounded Latin American/Mexican to the user.

Conclusion: do not rely on OpenAI TTS for strict Spain-accent control unless OpenAI adds dependable voice/accent targeting or a suitable custom voice path.

### v2: Local Spain-accent fallback

Output:

```text
output/pau-madrid-2025-matematicas-pregunta-1-1-v2-spain-voice.mp4
```

This version improved the matrix rendering and used a local macOS Spanish-from-Spain voice. It still failed the quality bar:

- voice sounded poor
- tone was too flat
- video felt dull
- slide/camera movement was distracting

Conclusion: correct accent is not enough. The video needs a better synthetic voice and a stricter visual system.

## Expert Direction

After the poor v2 result, an expert review recommended:

- stop using generic TTS plus moving slide templates as the final format
- use AI to prepare script, math steps, scene plan, and metadata
- use a real Spain-accent narrator or a provider with explicit/high-quality `es-ES` voices
- make visuals feel like a guided solution or teacher board, not a moving presentation deck
- use LaTeX-like math blocks, fixed framing, and no fake camera drift
- benchmark voices before producing more videos

This drove the v3 rebuild.

## v3 Design Decisions

The v3 visual approach is intentionally conservative:

- fixed 16:9 frames
- no zoom
- no pan
- no animated camera motion
- hard cuts between scenes
- large formulas
- proper matrix blocks
- brand colors from ClasesDeApoyo
- one idea per scene
- 12 short scenes

The v3 plan is stored in:

```text
work/video_plan_v3.json
```

The slide renderer is:

```text
work/render_slides_v3.mjs
```

It generates SVG slides, rasterizes them with macOS `qlmanage`, crops to 1920x1080 with `ffmpeg`, and writes PNG frames to:

```text
work/png-v3/
```

Reason for `qlmanage`: the local `ffmpeg` build did not have an SVG decoder, and `rsvg-convert` / ImageMagick were not installed.

## ElevenLabs Work

The user configured `ELEVENLABS_API_KEY` in `.env.local`.

Successful benchmark samples were generated under:

```text
work/voice-benchmarks-elevenlabs/
```

Default voices tested included:

- George
- Adam
- Antoni
- Arnold

Some default voices failed due to plan limits.

Voice Design was attempted but blocked by ElevenLabs:

```text
Creating a voice through the API is only available on a paid plan.
```

Listing voices was also blocked by missing API permission:

```text
missing_permissions: voices_read
```

The user found a promising ElevenLabs voice-library candidate:

```text
6xftrpatV0jGmFHxDjUv
```

The API rejected it on the current plan:

```text
payment_required: Free users cannot use library voices via the API.
```

The v3 plan now stores that selected voice ID as the default target, but the current rendered v3 MP4 still uses previously generated default ElevenLabs voice audio because the selected library voice cannot yet be used through the API.

## Current v3 Output

Latest candidate:

```text
output/pau-madrid-2025-matematicas-pregunta-1-1-elevenlabs-v3.mp4
```

Validation performed:

- H.264 video
- 1920x1080
- AAC audio
- duration around 3:12
- `ffmpeg -v error -i ... -f null -` completed without errors
- review frames extracted under `output/review-frames-v3/`

This candidate is mainly a visual/style test bed until the chosen ElevenLabs library voice is available through the API.

## v4 Design Rebuild (Remotion + motion)

The v3 static frames were correct but **dull**: with hard cuts and no in-scene
motion, nothing directs the student's attention and the slides read like a
corporate deck (two oversized white boxes, Arial math, lots of dead space). One
slide even shipped a bug — the summary frame printed the literal placeholder
word `Resumen` because summary slides had no `formula` field and the renderer
fell back to it.

v4 keeps the **narration unchanged** (voice is out of design scope; the 12
existing ElevenLabs MP3s are reused as-is) and rebuilds only the **visuals** as a
motion-driven explainer using Remotion (React). This was a deliberate scope
choice: progressive reveal — each algebra line appearing as it is spoken, active
terms lighting up — is the single biggest attention driver for math video, and it
needs a real animation framework. The static SVG→PNG pipeline cannot do it.

### v4 design system

- **Dark "studio" theme** (near-black teal, layered radial gradients + faint
  grid) so formulas and color-coded results pop, like premium math channels.
- **Real math typesetting** via KaTeX rendered in Chromium — proper brackets,
  superscripts, `·`, `λ`, `≠` — replacing Arial glyphs and the ambiguous `*`.
- **A persistent `k` number-line** as the spine of the whole video: ticks at
  `-1` and `0`, three regions that light up per case and tie the 12 scenes into
  one journey. Semantic color is consistent everywhere:
  - teal = given/neutral, **pink = incompatible** (`k = -1`),
    **cyan = indeterminado** (`k = 0`), **green = determinado / final answer**.
- **Progressive reveal**: headings, formula rows, chips and result badges spring
  in line-by-line (no fake camera drift — purposeful build-on only). Active
  values (`k`, `(k+1)`, the danger points) are color-emphasised.
- **Burned-in captions** (sentence-level, distributed across each narration
  segment), a real **hook** (system matrix → `|A|` spotlight) and a **branded
  outro** (number-line "full map" + `clasesdeapoyo.com` CTA).
- 16:9, 1920×1080, 30 fps, ~3:17, sync-timed to the narration MP3 durations.

### v4 file structure

Lives under the ignored `var/` tree (not committed; `node_modules` ≈ 683 MB):

```text
remotion/
  package.json  tsconfig.json  remotion.config.ts
  public/audio/                 # the 12 ElevenLabs MP3s, copied from work/audio-elevenlabs-v3
  scripts/stills.mjs            # bundle once, render one still per scene (review)
  src/
    index.ts  Root.tsx  PauVideo.tsx   # entry, composition, sequence+audio stitching
    theme.ts                           # colors / fonts / semantic palette
    timeline.ts                        # 12 scenes: durations, captions, k-number-line state
    math.tsx                           # <Tex> (KaTeX) + reveal/pop spring helpers
    ui.tsx                             # Background, TopBar, Heading, Captions, ResultBadge, NumberLineK, Footer
    scenes.tsx                         # 12 scene bodies + router + shared chrome
  output/
    pau-madrid-2025-matematicas-pregunta-1-1-remotion.mp4   # rendered video
    stills/                            # one review still per scene
```

`timeline.ts` is the per-exercise input: swap durations, captions, headings and
the math TeX strings to retarget another question. The 12 scene component types
in `scenes.tsx` (hook, system, method, determinant, general, two cases ×
matrix/result, solve, solution, summary) are the reusable templates.

### v4 commands

From the `remotion/` directory:

```bash
npm install                       # first time only (also fetches headless Chrome on first render)
node scripts/stills.mjs           # render review stills (fast, no audio)
npx remotion studio               # interactive preview / scrubbing
npm run render                    # full MP4 -> output/pau-...-remotion.mp4
```

If the narration is ever regenerated (e.g. the ElevenLabs library voice becomes
available), recopy the MP3s into `public/audio/` and update the matching
`durSec` values in `timeline.ts`, then re-render.

### v4 validation

- H.264, 1920×1080, AAC stereo, duration 3:17.
- `ffmpeg -v error -f null -` decodes clean; audio mean ≈ −22.7 dB (not silent).
- 12 review stills + frames extracted from the encoded video confirm the
  progressive reveal and layout on every scene type.

## Commands To Resume

From `clases-de-apoyo-backend`:

```bash
node var/generated-videos/madrid-matematicas-2025-julio-extraordinaria-1-pregunta-1-1/work/render_slides_v3.mjs
```

If the ElevenLabs plan supports the selected library voice:

```bash
node var/generated-videos/madrid-matematicas-2025-julio-extraordinaria-1-pregunta-1-1/work/generate_elevenlabs_audio_v3.mjs 6xftrpatV0jGmFHxDjUv --force
```

Assemble the MP4:

```bash
node var/generated-videos/madrid-matematicas-2025-julio-extraordinaria-1-pregunta-1-1/work/assemble_video_v3.mjs
```

Validate the MP4:

```bash
ffprobe -v error -show_entries format=duration -show_entries stream=codec_name,width,height -of default=noprint_wrappers=1 var/generated-videos/madrid-matematicas-2025-julio-extraordinaria-1-pregunta-1-1/output/pau-madrid-2025-matematicas-pregunta-1-1-elevenlabs-v3.mp4
ffmpeg -v error -i var/generated-videos/madrid-matematicas-2025-julio-extraordinaria-1-pregunta-1-1/output/pau-madrid-2025-matematicas-pregunta-1-1-elevenlabs-v3.mp4 -f null -
```

Extract review frames:

```bash
mkdir -p var/generated-videos/madrid-matematicas-2025-julio-extraordinaria-1-pregunta-1-1/output/review-frames-v3
ffmpeg -y -v error -ss 00:00:35 -i var/generated-videos/madrid-matematicas-2025-julio-extraordinaria-1-pregunta-1-1/output/pau-madrid-2025-matematicas-pregunta-1-1-elevenlabs-v3.mp4 -frames:v 1 var/generated-videos/madrid-matematicas-2025-julio-extraordinaria-1-pregunta-1-1/output/review-frames-v3/frame-35s.png
ffmpeg -y -v error -ss 00:01:45 -i var/generated-videos/madrid-matematicas-2025-julio-extraordinaria-1-pregunta-1-1/output/pau-madrid-2025-matematicas-pregunta-1-1-elevenlabs-v3.mp4 -frames:v 1 var/generated-videos/madrid-matematicas-2025-julio-extraordinaria-1-pregunta-1-1/output/review-frames-v3/frame-105s.png
ffmpeg -y -v error -ss 00:02:45 -i var/generated-videos/madrid-matematicas-2025-julio-extraordinaria-1-pregunta-1-1/output/pau-madrid-2025-matematicas-pregunta-1-1-elevenlabs-v3.mp4 -frames:v 1 var/generated-videos/madrid-matematicas-2025-julio-extraordinaria-1-pregunta-1-1/output/review-frames-v3/frame-165s.png
```

## Next Decisions

Before scaling to more exercises:

1. Upgrade or configure ElevenLabs so library voice `6xftrpatV0jGmFHxDjUv` can be used through the API, or choose another API-available Spain-accent voice.
2. When the voice is settled, recopy the new MP3s into `remotion/public/audio/`, update the `durSec` values in `timeline.ts`, and re-render the v4 Remotion video.
3. Review the final voice by ear before producing more videos.
4. ~~Decide whether to keep the static-frame template or move to Remotion/Hyperframes.~~ **Done — moved to a Remotion motion design (v4); see "v4 Design Rebuild" above.**
5. If scaling, promote the v4 Remotion project out of `var/generated-videos/.../remotion/` into a reusable repo tool, with `timeline.ts` (durations + captions + math TeX) as the per-exercise input.

## Important Caveats

- Generated videos and intermediate assets are ignored under `var/`; they are not part of the tracked repository.
- Do not commit `.env.local` or any API keys.
- The current scripts are one-off local pipeline scripts, not production-ready batch tooling.
- The selected ElevenLabs voice ID is stored in the local plan, but the generated MP4 does not yet use it because the API plan blocked it.
