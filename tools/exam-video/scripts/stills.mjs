// Render one review still per scene (post-reveal frame).
//   node scripts/stills.mjs <slug> [--reel]
import fs from "node:fs";
import path from "node:path";
import { bundle } from "@remotion/bundler";
import { renderStill, selectComposition } from "@remotion/renderer";
import { ROOT, propsPath, outputDir, compositionId, cli } from "./lib.mjs";

const { slug, reel } = cli();
const props = JSON.parse(fs.readFileSync(propsPath(slug, reel), "utf8"));
const FPS = 30;
const outDir = path.join(outputDir(slug, reel), "stills");
fs.mkdirSync(outDir, { recursive: true });

const serveUrl = await bundle({ entryPoint: path.join(ROOT, "src/index.ts") });
const composition = await selectComposition({ serveUrl, id: compositionId(reel), inputProps: props });

let from = 0;
for (const scene of props.scenes) {
  const t = props.timing.scenes.find((x) => x.id === scene.id);
  const frames = Math.round(t.durSec * FPS) + Math.round((scene.tail ?? 0.4) * FPS);
  const frame = from + Math.min(frames - 5, Math.round(frames * 0.8));
  await renderStill({ composition, serveUrl, frame, inputProps: props, output: path.join(outDir, `${scene.id}.png`), imageFormat: "png" });
  console.log(`still ${scene.id} @ ${frame}`);
  from += frames;
}
console.log(`done -> ${path.relative(process.cwd(), outDir)}`);
