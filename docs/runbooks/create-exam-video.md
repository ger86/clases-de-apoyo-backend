# Runbook: create an explainer video for an exam exercise

The tool lives in [tools/exam-video](../../tools/exam-video). One `exercise.json` in, one MP4 out. The first exercise built this way is `pau-madrid-matematicas-pregunta-3-aceitunas`; copy it as a template.

## 0. Prerequisites

- Node 20+ and `ffmpeg`/`ffprobe` on the PATH.
- `ELEVENLABS_API_KEY` in the backend `.env.local`. The scripts read it from there; never paste it anywhere else.
- `cd tools/exam-video && npm install` (first time; downloads headless Chrome on the first render).

## 1. Read the statement and the solution

Scanned PDFs have no text layer. Render them to PNG (`pdftoppm -r 110 -png file.pdf out`) and read them visually. Check every result with an independent calculation before writing a single line of narration. Official solutions can contain errors; the video must not repeat them.

## 2. Write `public/exercises/<slug>/exercise.json`

- `slug` must equal the folder name.
- `exam.label` is the top-right text, `exam.question` and `exam.topic` go in the footer.
- 12 to 16 scenes, one idea each. Order: hook, data, model, formula, calculation, result, bridge to the next part, ..., summary.
- `narration`: Spanish from Spain, digits written as words for decimals ("cero coma cinco cuatro cero cuatro"). Optional ElevenLabs v3 tone tags at the start, such as `[didactic]`.
- `captions`: one per narration sentence, with digits. Sentences are split at `. ! ?` followed by a capital letter.
- `body`: pick a type from the tool README. TeX goes through KaTeX; escape backslashes in JSON (`\\frac`). Use `0{,}5404` for Spanish decimals in TeX.

## 3. Validate and review before spending credits

```bash
node scripts/tts.mjs <slug> --dry-run      # total characters = credits; caption count check
node scripts/build-props.mjs <slug>        # estimated durations, no audio yet
node scripts/stills.mjs <slug>             # look at output/<slug>/stills/*.png
```

Fix crowded or wrapped frames now. Frames are free; audio is not.

## 4. Generate audio

```bash
node scripts/tts.mjs <slug>
```

The script skips scenes that already have an MP3. To redo one scene after a narration change: `--only=<sceneId> --force`. The default voice is the cloned voice `KDuMsTRG03d18osdZP8V` (paid plan). If a voice is rejected with 402, the script falls back to `fallbackVoiceId` (George).

## 5. Build props and render

```bash
node scripts/build-props.mjs <slug>        # real durations + caption timestamps
node scripts/render.mjs <slug>             # output/<slug>/<slug>.mp4
```

Validate the file:

```bash
ffprobe -v error -show_entries format=duration -show_entries stream=codec_name,width,height -of default=noprint_wrappers=1 output/<slug>/<slug>.mp4
ffmpeg -v error -i output/<slug>/<slug>.mp4 -f null -
```

Listen to the whole video once before publishing. The voice is the part no script can check.

## 6. Publish

```bash
node scripts/youtube.mjs <slug>            # output/<slug>/youtube.md, <slug>.es.srt, thumbnail.png
```

This needs a `youtube` section in `exercise.json` (title, description, tags, thumbnail lines). Chapters and subtitles are generated from the timeline. Upload to YouTube and link it from the exam page through the `YoutubeVideo` entity in the admin. Do not commit MP3, props or MP4 files; they are ignored on purpose.
