import fs from "node:fs";
import path from "node:path";
import { execFileSync } from "node:child_process";

export const ROOT = path.resolve(import.meta.dirname, "..");
export const REPO = path.resolve(ROOT, "../..");

export const exerciseDir = (slug) => path.join(ROOT, "public/exercises", slug);

// Every path comes in two flavours: the 16:9 explainer and the 9:16 reel. The
// reel keeps its own audio folder, props file and output folder so both can
// exist for the same exercise without overwriting each other.
export const audioDir = (slug, reel = false) => path.join(exerciseDir(slug), reel ? "audio/reel" : "audio");
export const audioPathProp = (reel = false) => (reel ? "audio/reel" : "audio");
export const propsPath = (slug, reel = false) => path.join(exerciseDir(slug), reel ? "props.reel.json" : "props.json");
export const outputDir = (slug, reel = false) => path.join(ROOT, "output", slug, ...(reel ? ["reel"] : []));
export const compositionId = (reel = false) => (reel ? "ExamReel" : "ExamVideo");
export const videoName = (slug, reel = false) => (reel ? `${slug}-reel.mp4` : `${slug}.mp4`);

// Shared "<slug> [--reel] [--flag=value]" parsing.
export const cli = () => {
  const args = process.argv.slice(2);
  return {
    args,
    slug: args.find((a) => !a.startsWith("--")),
    reel: args.includes("--reel"),
    has: (name) => args.includes(`--${name}`),
    flag: (name) => args.find((a) => a.startsWith(`--${name}=`))?.split("=")[1],
  };
};

// The scenes a command works on: the exercise's own, or the reel's.
export const scenesOf = (exercise, reel = false) => {
  if (!reel) return exercise.scenes;
  if (!exercise.reel?.scenes?.length) throw new Error(`${exercise.slug}: exercise.json has no "reel" section with scenes`);
  return exercise.reel.scenes;
};

export const loadExercise = (slug) => {
  if (!slug) throw new Error("Usage: <script> <exercise-slug>");
  const file = path.join(exerciseDir(slug), "exercise.json");
  if (!fs.existsSync(file)) throw new Error(`Missing ${file}`);
  const exercise = JSON.parse(fs.readFileSync(file, "utf8"));
  if (exercise.slug !== slug) throw new Error(`exercise.json slug "${exercise.slug}" does not match folder "${slug}"`);
  return exercise;
};

// Read KEY=value pairs from the backend .env.local without printing them.
export const loadEnv = () => {
  const file = path.join(REPO, ".env.local");
  if (!fs.existsSync(file)) return {};
  return Object.fromEntries(
    fs
      .readFileSync(file, "utf8")
      .split(/\n/)
      .filter((line) => line.includes("=") && !line.trim().startsWith("#"))
      .map((line) => {
        const i = line.indexOf("=");
        return [line.slice(0, i).trim(), line.slice(i + 1).trim().replace(/^['"]|['"]$/g, "")];
      }),
  );
};

export const stripTags = (text) => text.replace(/\[[^\]]*\]\s*/g, "").replace(/\s+/g, " ").trim();

export const splitSentences = (text) =>
  stripTags(text)
    .split(/(?<=[.!?…])\s+(?=[A-ZÁÉÍÓÚÑ¿¡"(])/u)
    .map((s) => s.trim())
    .filter(Boolean);

export const audioDuration = (file) =>
  Number(
    execFileSync("ffprobe", ["-v", "error", "-show_entries", "format=duration", "-of", "csv=p=0", file], {
      encoding: "utf8",
    }).trim(),
  );
