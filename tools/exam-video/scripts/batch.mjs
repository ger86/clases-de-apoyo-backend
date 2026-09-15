// Run the full pipeline (tts, build-props, render, youtube) for several exercises, one after another.
//   node scripts/batch.mjs <slug> [<slug> ...]          full run
//   node scripts/batch.mjs --dry-run <slug> [...]       only the credit count and caption checks
// A failing step stops that exercise and the batch continues with the next one; the summary lists what failed.
import { spawnSync } from "node:child_process";
import path from "node:path";
import { ROOT } from "./lib.mjs";

const args = process.argv.slice(2);
const dryRun = args.includes("--dry-run");
const slugs = args.filter((a) => !a.startsWith("--"));
if (slugs.length === 0) throw new Error("Usage: node scripts/batch.mjs [--dry-run] <slug> [<slug> ...]");

const run = (script, slug, extra = []) => {
  const r = spawnSync("node", [path.join(ROOT, "scripts", script), slug, ...extra], { cwd: ROOT, stdio: "inherit" });
  return r.status === 0;
};

const results = [];
for (const slug of slugs) {
  console.log(`\n===== ${slug} =====`);
  const started = Date.now();
  const steps = dryRun ? [["tts.mjs", ["--dry-run"]]] : [["tts.mjs"], ["build-props.mjs"], ["render.mjs"], ["youtube.mjs"]];
  let failed = null;
  for (const [script, extra] of steps) {
    if (!run(script, slug, extra ?? [])) {
      failed = script;
      break;
    }
  }
  results.push({ slug, failed, minutes: ((Date.now() - started) / 60000).toFixed(1) });
}

console.log("\n===== batch summary =====");
for (const r of results) console.log(`${r.failed ? "FAILED at " + r.failed : "ok"}  ${r.slug}  (${r.minutes} min)`);
process.exit(results.some((r) => r.failed) ? 1 : 0);
