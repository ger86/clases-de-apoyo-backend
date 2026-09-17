// Produce one or more exercises end to end: tts, build-props, render, youtube.
//
// Every exercise is produced in both formats: the 16:9 explainer for YouTube and,
// when exercise.json has a "reel" section, the 9:16 reel for Instagram and TikTok.
// An exercise with no "reel" section is still produced as a video, and is listed in
// the summary as "sin reel" so it does not pass unnoticed.
//
//   node scripts/batch.mjs <slug> [<slug> ...]        video + reel
//   node scripts/batch.mjs --dry-run <slug> [...]     credit count and caption checks, both formats
//   node scripts/batch.mjs --no-reel <slug> [...]     only the 16:9 video
//   node scripts/batch.mjs --only-reel <slug> [...]   only the vertical reel
//
// A failing step stops that format for that exercise; the batch continues with the
// next format and the next exercise, and the summary lists what failed.
import { spawnSync } from "node:child_process";
import path from "node:path";
import { ROOT, loadExercise } from "./lib.mjs";

const args = process.argv.slice(2);
const dryRun = args.includes("--dry-run");
const noReel = args.includes("--no-reel");
const onlyReel = args.includes("--only-reel");
const slugs = args.filter((a) => !a.startsWith("--"));
if (slugs.length === 0) throw new Error("Usage: node scripts/batch.mjs [--dry-run] [--no-reel|--only-reel] <slug> [<slug> ...]");
if (noReel && onlyReel) throw new Error("--no-reel and --only-reel are opposites; pass at most one");

const STEPS = ["tts.mjs", "build-props.mjs", "render.mjs", "youtube.mjs"];

const run = (script, slug, extra) => {
  const r = spawnSync("node", [path.join(ROOT, "scripts", script), slug, ...extra], { cwd: ROOT, stdio: "inherit" });
  return r.status === 0;
};

// One format of one exercise. Returns the script that failed, or null.
const produce = (slug, reel) => {
  const extra = reel ? ["--reel"] : [];
  const steps = dryRun ? [["tts.mjs", [...extra, "--dry-run"]]] : STEPS.map((s) => [s, extra]);
  for (const [script, flags] of steps) if (!run(script, slug, flags)) return script;
  return null;
};

const results = [];
for (const slug of slugs) {
  const hasReel = Boolean(loadExercise(slug).reel?.scenes?.length);
  const formats = [];
  if (!onlyReel) formats.push(["video", false]);
  if (!noReel && hasReel) formats.push(["reel", true]);
  if (onlyReel && !hasReel) console.log(`\n${slug}: exercise.json has no "reel" section; nothing to do`);

  const row = { slug, hasReel, failed: [], minutes: {} };
  for (const [name, reel] of formats) {
    console.log(`\n===== ${slug} (${name}) =====`);
    const started = Date.now();
    const failed = produce(slug, reel);
    row.minutes[name] = ((Date.now() - started) / 60000).toFixed(1);
    if (failed) row.failed.push(`${name} at ${failed}`);
  }
  results.push(row);
}

console.log("\n===== batch summary =====");
for (const r of results) {
  const times = Object.entries(r.minutes).map(([k, v]) => `${k} ${v} min`).join(", ") || "nada que hacer";
  const state = r.failed.length ? `FAILED ${r.failed.join("; ")}` : Object.keys(r.minutes).length ? "ok" : "--";
  console.log(`${state}  ${r.slug}${r.hasReel || onlyReel ? "" : "  (sin reel: falta la seccion \"reel\" en exercise.json)"}  (${times})`);
}
const missing = results.filter((r) => !r.hasReel && !noReel && !onlyReel);
if (missing.length) console.log(`\n${missing.length} ejercicio(s) sin reel. Escribe la seccion "reel" en su exercise.json y vuelve a pasarlos con --only-reel.`);
process.exit(results.some((r) => r.failed.length) ? 1 : 0);
