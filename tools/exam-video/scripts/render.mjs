// Render the final MP4.
//   node scripts/render.mjs <slug> [--reel]
import fs from "node:fs";
import path from "node:path";
import { spawnSync } from "node:child_process";
import { ROOT, propsPath, outputDir, compositionId, videoName, cli } from "./lib.mjs";

const { slug, reel } = cli();
const props = propsPath(slug, reel);
if (!fs.existsSync(props)) throw new Error(`Run build-props first: ${props} is missing`);
const out = path.join(outputDir(slug, reel), videoName(slug, reel));
fs.mkdirSync(path.dirname(out), { recursive: true });
const r = spawnSync("npx", ["remotion", "render", compositionId(reel), out, `--props=${props}`, "--log=info"], {
  cwd: ROOT,
  stdio: "inherit",
});
process.exit(r.status ?? 1);
