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
2. Regenerate only the audio with the selected voice and reassemble the existing v3 frames.
3. Review the final voice by ear before producing more videos.
4. Decide whether to keep the current static-frame template or move to a reusable HTML/Remotion/Hyperframes template.
5. If scaling, move the scripts out of `var/generated-videos/.../work/` into a reusable repo tool and make `video_plan_v3.json` the per-exercise input.

## Important Caveats

- Generated videos and intermediate assets are ignored under `var/`; they are not part of the tracked repository.
- Do not commit `.env.local` or any API keys.
- The current scripts are one-off local pipeline scripts, not production-ready batch tooling.
- The selected ElevenLabs voice ID is stored in the local plan, but the generated MP4 does not yet use it because the API plan blocked it.
