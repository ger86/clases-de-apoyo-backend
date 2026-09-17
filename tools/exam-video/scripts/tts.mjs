// Generate one MP3 (+ character alignment) per scene with ElevenLabs.
//   node scripts/tts.mjs <slug> [--reel] [--dry-run] [--force] [--voice=<id>] [--only=<sceneId>]
// Skips scenes whose MP3 already exists unless --force. --dry-run only counts characters.
// --reel works on the "reel" scenes and writes to public/exercises/<slug>/audio/reel/.
import fs from "node:fs";
import path from "node:path";
import { loadExercise, loadEnv, audioDir, splitSentences, cli, scenesOf } from "./lib.mjs";

const { slug, reel, flag, has } = cli();
const dryRun = has("dry-run");
const force = has("force");
const only = flag("only");

const exercise = loadExercise(slug);
const scenes = scenesOf(exercise, reel);
const env = loadEnv();
const apiKey = env.ELEVENLABS_API_KEY;

let total = 0;
const problems = [];
for (const scene of scenes) {
  total += scene.narration.length;
  const sentences = splitSentences(scene.narration);
  if (scene.captions && scene.captions.length !== sentences.length) {
    problems.push(`${scene.id}: ${scene.captions.length} captions but ${sentences.length} narration sentences`);
  }
}
console.log(`${reel ? "reel: " : ""}${scenes.length} scenes, ${total} characters (credits) in total`);
for (const p of problems) console.log(`caption mismatch: ${p}`);
// A reel has to stay watchable in one scroll: 3 to 5 scenes, 30 to 60 seconds.
if (reel) {
  if (scenes.length < 3 || scenes.length > 5) console.log(`aviso: un reel deberia tener de 3 a 5 escenas, tiene ${scenes.length}`);
  if (total < 300 || total > 600) console.log(`aviso: la narracion del reel deberia medir de 300 a 600 caracteres, mide ${total}`);
}
if (dryRun) process.exit(problems.length ? 1 : 0);
if (!apiKey) throw new Error("Missing ELEVENLABS_API_KEY in .env.local");

const dir = audioDir(slug, reel);
fs.mkdirSync(dir, { recursive: true });
let voiceId = flag("voice") || env.ELEVENLABS_VOICE_ID || exercise.voice.voiceId;

// Delivery, not identity: these shape how the cloned voice performs, never which
// voice it is. Lower stability lets it vary more (livelier, less even), higher style
// exaggerates its own manner. An exercise can override any of them with
// "voice": { "settings": { ... } }, and a reel can override them again with
// "voice": { "reelSettings": { ... } }, because a 40 second short wants more energy
// than a 5 minute explainer. See the README.
const VOICE_SETTINGS = { stability: 0.4, similarity_boost: 0.78, style: 0.45, use_speaker_boost: true, speed: 1.02 };
const voiceSettings = {
  ...VOICE_SETTINGS,
  ...(exercise.voice.settings ?? {}),
  ...(reel ? (exercise.voice.reelSettings ?? {}) : {}),
};
console.log(`voice settings: ${Object.entries(voiceSettings).map(([k, v]) => `${k}=${v}`).join(" ")}`);

const synthesize = async (scene, voice, withTimestamps = true) => {
  const endpoint = withTimestamps ? "/with-timestamps" : "";
  const res = await fetch(`https://api.elevenlabs.io/v1/text-to-speech/${voice}${endpoint}?output_format=mp3_44100_128`, {
    method: "POST",
    headers: { "xi-api-key": apiKey, "Content-Type": "application/json" },
    body: JSON.stringify({
      text: scene.narration,
      model_id: exercise.voice.model,
      voice_settings: voiceSettings,
    }),
  });
  return res;
};

for (const scene of scenes) {
  if (only && scene.id !== only) continue;
  const mp3 = path.join(dir, `${scene.id}.mp3`);
  if (fs.existsSync(mp3) && !force) {
    console.log(`skip ${scene.id} (exists)`);
    continue;
  }
  let res = await synthesize(scene, voiceId);
  if (res.status === 402 && exercise.voice.fallbackVoiceId && voiceId !== exercise.voice.fallbackVoiceId) {
    console.log(`voice ${voiceId} needs a paid plan; falling back to ${exercise.voice.fallbackVoiceId}`);
    voiceId = exercise.voice.fallbackVoiceId;
    res = await synthesize(scene, voiceId);
  }
  let json;
  if (res.ok) {
    json = await res.json();
    fs.writeFileSync(mp3, Buffer.from(json.audio_base64, "base64"));
  } else {
    const detail = await res.text();
    if (res.status !== 400 && res.status !== 422) throw new Error(`ElevenLabs failed for ${scene.id}: ${res.status} ${detail}`);
    // Timestamps endpoint rejected the request (e.g. unsupported model); retry the plain endpoint.
    console.log(`with-timestamps rejected for ${scene.id} (${res.status}); retrying without alignment`);
    const plain = await synthesize(scene, voiceId, false);
    if (!plain.ok) throw new Error(`ElevenLabs failed for ${scene.id}: ${plain.status} ${await plain.text()}`);
    fs.writeFileSync(mp3, Buffer.from(await plain.arrayBuffer()));
    json = {};
  }
  const alignment = json.normalized_alignment ?? json.alignment ?? null;
  fs.writeFileSync(path.join(dir, `${scene.id}.alignment.json`), JSON.stringify({ voiceId, alignment }, null, 0));
  console.log(`audio ${scene.id} (${scene.narration.length} chars, voice ${voiceId})`);
}
