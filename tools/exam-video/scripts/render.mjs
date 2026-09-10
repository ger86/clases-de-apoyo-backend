// Render the final MP4.
//   node scripts/render.mjs <slug>
import fs from "node:fs";
import path from "node:path";
import { spawnSync } from "node:child_process";
import { ROOT, propsPath } from "./lib.mjs";

const slug = process.argv[2];
if (!fs.existsSync(propsPath(slug))) throw new Error(`Run build-props first: ${propsPath(slug)} is missing`);
const out = path.join(ROOT, "output", slug, `${slug}.mp4`);
fs.mkdirSync(path.dirname(out), { recursive: true });
const r = spawnSync("npx", ["remotion", "render", "ExamVideo", out, `--props=${propsPath(slug)}`, "--log=info"], {
  cwd: ROOT,
  stdio: "inherit",
});
process.exit(r.status ?? 1);
