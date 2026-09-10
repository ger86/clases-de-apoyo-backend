// Merge exercise.json with measured audio durations and caption start times into props.json.
//   node scripts/build-props.mjs <slug>
import fs from "node:fs";
import path from "node:path";
import { loadExercise, audioDir, propsPath, audioDuration, splitSentences, stripTags } from "./lib.mjs";

const slug = process.argv[2];
const exercise = loadExercise(slug);
const dir = audioDir(slug);

// Start time (s) of each caption, by locating each narration sentence in the alignment text.
const captionStarts = (scene, alignment) => {
  if (!alignment?.characters) return undefined;
  const sentences = splitSentences(scene.narration);
  const captions = scene.captions ?? sentences;
  if (captions.length !== sentences.length) return undefined;
  const joined = alignment.characters.join("");
  const times = alignment.character_start_times_seconds;
  let from = 0;
  const starts = [];
  for (const sentence of sentences) {
    const probe = stripTags(sentence).slice(0, 12);
    const idx = joined.indexOf(probe, from);
    if (idx < 0) return undefined;
    starts.push(starts.length === 0 ? 0 : Number(times[idx].toFixed(3)));
    from = idx + probe.length;
  }
  return starts;
};

const scenes = exercise.scenes.map((scene) => {
  const mp3 = path.join(dir, `${scene.id}.mp3`);
  if (!fs.existsSync(mp3)) {
    console.log(`no audio for ${scene.id}; estimating duration`);
    return { id: scene.id, hasAudio: false, durSec: Math.max(4, stripTags(scene.narration).length / 15) };
  }
  const alignFile = path.join(dir, `${scene.id}.alignment.json`);
  const alignment = fs.existsSync(alignFile) ? JSON.parse(fs.readFileSync(alignFile, "utf8")).alignment : null;
  const starts = captionStarts(scene, alignment);
  if (!starts) console.log(`no caption alignment for ${scene.id}; using length-based spread`);
  return { id: scene.id, hasAudio: true, durSec: Number(audioDuration(mp3).toFixed(3)), captionStarts: starts };
});

const props = { ...exercise, timing: { scenes } };
fs.writeFileSync(propsPath(slug), JSON.stringify(props, null, 2));
const total = scenes.reduce((s, t) => s + t.durSec + (exercise.scenes.find((x) => x.id === t.id).tail ?? 0.4), 0);
console.log(`wrote ${path.relative(process.cwd(), propsPath(slug))}; video length about ${Math.floor(total / 60)}:${String(Math.round(total % 60)).padStart(2, "0")}`);
