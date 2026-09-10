import fs from "node:fs";
import path from "node:path";
import { execFileSync } from "node:child_process";

export const ROOT = path.resolve(import.meta.dirname, "..");
export const REPO = path.resolve(ROOT, "../..");

export const exerciseDir = (slug) => path.join(ROOT, "public/exercises", slug);
export const audioDir = (slug) => path.join(exerciseDir(slug), "audio");
export const propsPath = (slug) => path.join(exerciseDir(slug), "props.json");

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
