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

To make the same voice sound livelier, do not change the voice: add `voice.settings` (both formats) or `voice.reelSettings` (reel only) to `exercise.json`, lowering `stability` and raising `style`. `matrices-ejercicio-1-matriz-traspuesta` uses `stability 0.28, style 0.65, speed 1.05` as the lively profile. `tts.mjs` prints the settings on every run, and the tags `[excited]` and `[cheerful]` at the start of a narration do the rest. Changing the settings does not regenerate anything by itself: pass `--force` to redo clips that already exist.

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

## 7. Vertical reel for Instagram and TikTok (optional)

A reel is the same exercise told as a single idea in 30 to 60 seconds, at 1080x1920. It is not a summary of the long video: it is the hook that sends people to YouTube.

### 7.1 Write the `reel` key in `exercise.json`

```json
"reel": {
  "title": "internal name of the reel",
  "caption": "text of the post, hashtags included",
  "cover": { "kicker": "...", "line1": "...", "line2": "...", "tex": "...", "badge": "..." },
  "scenes": [ /* 3 to 5 scenes, same structure as the long video's scenes */ ]
}
```

Rules:

- 3 to 5 scenes, 300 to 600 characters of narration in total, 30 to 60 seconds.
- One idea only. Pick the step where people get it wrong, not the whole statement.
- Scene 1 is a `hook` whose title states the idea within the first 2 seconds.
- The last scene uses the `cta` body, with the call to action. `hook` and `cta` scenes hide the phase heading and the footer, so they read full bleed.
- The mistake you teach has to be a real mistake. Check it numerically before recording: in Pregunta 1 of June 2026, multiplying by A⁻¹ on the right happens to give the same answer, so it is not a valid counterexample; cancelling the A does fail.
- Narration and on-screen text are Spanish from Spain, like the long video.

### 7.2 Validate, review, produce

```bash
node scripts/tts.mjs <slug> --reel --dry-run   # credits, captions, scene-count and length warnings
node scripts/build-props.mjs <slug> --reel     # estimated durations, warns outside 30-60 s
node scripts/stills.mjs <slug> --reel          # output/<slug>/reel/stills/*.png
```

Look at the vertical PNGs before spending credits. Formulas do not rescale themselves: a row that is too wide gets wrapped by KaTeX onto a second, left-aligned line. It is obvious in the still; fix it by lowering `size` or splitting the row in two. The grid and SVG bodies (`table`, `dots`, `normal`, `plot`) do not wrap, so check them for crowding instead: at most about 4 columns in a `table`, at most 4 rows plus the closing line in a `summary`.

Safe zones the vertical layout respects (`tools/exam-video/src/layout.ts`): 170 px clear on the right, where Instagram and TikTok draw their button column, and captions pinned to the middle third, because the bottom is covered by the post text. The vertical widths are all derived from that 860 px box, so nothing reaches the gutter; if you add a body type, derive its width the same way instead of typing a number.

```bash
node scripts/tts.mjs <slug> --reel             # audio -> public/exercises/<slug>/audio/reel/
node scripts/build-props.mjs <slug> --reel     # real durations
node scripts/render.mjs <slug> --reel          # output/<slug>/reel/<slug>-reel.mp4
node scripts/youtube.mjs <slug> --reel         # reel.md (post text), .es.srt, cover.png
```

Validate the MP4 as with the long video, checking that it comes out 1080x1920:

```bash
ffprobe -v error -show_entries format=duration -show_entries stream=codec_name,width,height -of default=noprint_wrappers=1 output/<slug>/reel/<slug>-reel.mp4
ffmpeg -v error -i output/<slug>/reel/<slug>-reel.mp4 -f null -
```

Upload the MP4 as a reel, use `cover.png` as the cover and copy the text from `reel.md`. The `.srt` is generated in case you need it; the video already carries its own burnt-in captions.

## Whole exam: several questions from one PDF

You do not need to cut the PDF into exercises. The agent reads the whole scan page by page (`pdftoppm -r 120 -png exam.pdf out`), builds an index of questions, verifies every result, and writes one `exercise.json` per question (4.1 and 4.2 are separate videos). Writing the exercises can run in parallel (one agent per two questions), but nothing may edit `src/` or `scripts/` during that phase; only `exercise.json` files. Every exercise must pass `tts.mjs --dry-run` and a full still review before any audio is generated.

Then run everything in one go:

```bash
node scripts/batch.mjs --dry-run <slug1> <slug2> ...    # credits and caption checks for all, both formats
nohup node scripts/batch.mjs <slug1> <slug2> ... > output/batch.log 2>&1 &   # video and reel per slug
```

The batch produces both formats for every slug: the 16:9 video for YouTube, and then the 9:16 reel whenever that `exercise.json` has a `reel` section (section 7). An exercise without one is still produced as a video and is listed in the summary as "sin reel", so write the section and pass that slug again with `--only-reel`. `--no-reel` produces only the video.

Use `nohup` (or a terminal you keep open): a full exam takes 20 to 30 minutes for the videos, plus about 3 minutes per reel. The batch continues after a failing format or exercise and prints a summary at the end; rerun only the failed slugs, since `tts.mjs` skips the MP3s that already exist. Validate each MP4 with `ffprobe` and `ffmpeg -f null` and look at each `thumbnail.png` and `cover.png` before uploading. Reference run: PAU Madrid Junio 2026, six questions, about 16,700 credits and 18 minutes for the videos, 3,500 credits and 16 minutes for the six reels.
