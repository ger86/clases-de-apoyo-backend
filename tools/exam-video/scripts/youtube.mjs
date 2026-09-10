// Build the YouTube package: title/description/tags (youtube.md), Spanish subtitles (.srt) and a thumbnail PNG.
//   node scripts/youtube.mjs <slug>
import fs from "node:fs";
import path from "node:path";
import { spawnSync } from "node:child_process";
import { ROOT, propsPath, splitSentences } from "./lib.mjs";

const slug = process.argv[2];
const props = JSON.parse(fs.readFileSync(propsPath(slug), "utf8"));
if (!props.youtube) throw new Error('exercise.json has no "youtube" section');
const outDir = path.join(ROOT, "output", slug);
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

const srt = cues.map((c, i) => `${i + 1}\n${srtTime(c.start)} --> ${srtTime(c.end)}\n${c.text}\n`).join("\n");
fs.writeFileSync(path.join(outDir, `${slug}.es.srt`), srt);

const yt = props.youtube;
const description = `${yt.description.trim()}\n\nCapítulos:\n${chapters.join("\n")}\n`;
const md = `# YouTube: ${slug}\n\n## Título (${yt.title.length}/100 caracteres)\n\n${yt.title}\n\n## Descripción\n\n${description}\n## Etiquetas\n\n${yt.tags.join(", ")}\n\n## Ficheros\n\n- Vídeo: ${slug}.mp4\n- Miniatura: thumbnail.png (1280x720)\n- Subtítulos en español: ${slug}.es.srt (subir en YouTube Studio > Subtítulos)\n- Duración: ${mmss(from)}\n`;
fs.writeFileSync(path.join(outDir, "youtube.md"), md);

const r = spawnSync("npx", ["remotion", "still", "Thumbnail", path.join(outDir, "thumbnail.png"), `--props=${propsPath(slug)}`, "--image-format=png"], { cwd: ROOT, stdio: "inherit" });
if (r.status !== 0) process.exit(r.status ?? 1);
console.log(`wrote ${path.relative(process.cwd(), outDir)}/{youtube.md, ${slug}.es.srt, thumbnail.png}`);
