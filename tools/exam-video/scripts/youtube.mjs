// Build the publishing package.
//   node scripts/youtube.mjs <slug>          -> youtube.md, <slug>.es.srt, thumbnail.png (1280x720)
//   node scripts/youtube.mjs <slug> --reel   -> reel.md, <slug>-reel.es.srt, cover.png (1080x1920)
// Text is re-read from exercise.json on every run, so edits need no props rebuild.
import fs from "node:fs";
import path from "node:path";
import { spawnSync } from "node:child_process";
import { ROOT, propsPath, outputDir, splitSentences, loadExercise, scenesOf, cli } from "./lib.mjs";

const { slug, reel } = cli();
const exercise = loadExercise(slug);
const file = propsPath(slug, reel);
if (!fs.existsSync(file)) throw new Error(`Run build-props first: ${file} is missing`);
const props = {
  ...JSON.parse(fs.readFileSync(file, "utf8")),
  exam: exercise.exam,
  youtube: exercise.youtube,
  reel: exercise.reel,
  scenes: scenesOf(exercise, reel),
};
fs.writeFileSync(file, JSON.stringify(props, null, 2));
const meta = reel ? props.reel : props.youtube;
if (!meta) throw new Error(`exercise.json has no "${reel ? "reel" : "youtube"}" section`);
const outDir = outputDir(slug, reel);
fs.mkdirSync(outDir, { recursive: true });

const mmss = (s) => `${Math.floor(s / 60)}:${String(Math.floor(s % 60)).padStart(2, "0")}`;
const srtTime = (s) => {
  const h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60), sec = Math.floor(s % 60), ms = Math.round((s % 1) * 1000);
  return `${String(h).padStart(2, "0")}:${String(m).padStart(2, "0")}:${String(sec).padStart(2, "0")},${String(ms).padStart(3, "0")}`;
};

// Walk the timeline exactly like src/timeline.ts does.
let from = 0;
const chapters = [];
const cues = [];
for (const scene of props.scenes) {
  const t = props.timing.scenes.find((x) => x.id === scene.id);
  const captions = scene.captions ?? splitSentences(scene.narration);
  chapters.push(`${mmss(from)} ${scene.heading}`);
  let starts = t.captionStarts;
  if (!starts || starts.length !== captions.length) {
    const w = captions.map((c) => c.length), total = w.reduce((a, b) => a + b, 0) || 1;
    let acc = 0;
    starts = w.map((x) => { const s = (acc / total) * t.durSec; acc += x; return s; });
  }
  captions.forEach((text, i) => {
    const start = from + starts[i];
    const end = from + (i + 1 < starts.length ? starts[i + 1] - 0.05 : t.durSec);
    cues.push({ start, end, text });
  });
  from += t.durSec + (scene.tail ?? 0.4);
}

const srtName = reel ? `${slug}-reel.es.srt` : `${slug}.es.srt`;
const srt = cues.map((c, i) => `${i + 1}\n${srtTime(c.start)} --> ${srtTime(c.end)}\n${c.text}\n`).join("\n");
fs.writeFileSync(path.join(outDir, srtName), srt);

if (reel) {
  const md = `# Reel: ${slug}\n\n## Título interno\n\n${meta.title}\n\n## Texto de la publicación (${meta.caption.length} caracteres)\n\n${meta.caption.trim()}\n\n## Ficheros\n\n- Vídeo vertical: ${slug}-reel.mp4 (1080x1920, 30 fps)\n- Portada: cover.png (1080x1920)\n- Subtítulos en español: ${srtName} (para quemar o subir donde se admitan)\n- Duración: ${mmss(from)}\n`;
  fs.writeFileSync(path.join(outDir, "reel.md"), md);
} else {
  const description = `${meta.description.trim()}\n\nCapítulos:\n${chapters.join("\n")}\n`;
  const md = `# YouTube: ${slug}\n\n## Título (${meta.title.length}/100 caracteres)\n\n${meta.title}\n\n## Descripción\n\n${description}\n## Etiquetas\n\n${meta.tags.join(", ")}\n\n## Ficheros\n\n- Vídeo: ${slug}.mp4\n- Miniatura: thumbnail.png (1280x720)\n- Subtítulos en español: ${slug}.es.srt (subir en YouTube Studio > Subtítulos)\n- Duración: ${mmss(from)}\n`;
  fs.writeFileSync(path.join(outDir, "youtube.md"), md);
}

const imageId = reel ? "ReelCover" : "Thumbnail";
const imageName = reel ? "cover.png" : "thumbnail.png";
const r = spawnSync("npx", ["remotion", "still", imageId, path.join(outDir, imageName), `--props=${file}`, "--image-format=png"], { cwd: ROOT, stdio: "inherit" });
if (r.status !== 0) process.exit(r.status ?? 1);
console.log(`wrote ${path.relative(process.cwd(), outDir)}/{${reel ? "reel.md" : "youtube.md"}, ${srtName}, ${imageName}}`);
