import { FPS } from "./theme";
import type { SceneDef, TimedScene, VideoProps } from "./types";

// Strip ElevenLabs v3 audio tags such as "[energetic]" from the narration.
export const stripTags = (text: string): string =>
  text.replace(/\[[^\]]*\]\s*/g, "").replace(/\s+/g, " ").trim();

// Sentence split used both here (fallback captions) and by the timing script.
export const splitSentences = (text: string): string[] =>
  stripTags(text)
    .split(/(?<=[.!?…])\s+(?=[A-ZÁÉÍÓÚÑ¿¡"(])/u)
    .map((s) => s.trim())
    .filter(Boolean);

export const buildTimeline = (props: VideoProps): TimedScene[] => {
  let from = 0;
  return props.scenes.map((scene: SceneDef, index) => {
    const timing = props.timing.scenes.find((t) => t.id === scene.id);
    const durSec = timing?.durSec ?? Math.max(4, stripTags(scene.narration).length / 15);
    const narrationFrames = Math.round(durSec * FPS);
    const frames = narrationFrames + Math.round((scene.tail ?? 0.4) * FPS);
    const timed: TimedScene = {
      ...scene,
      index,
      from,
      frames,
      narrationFrames,
      hasAudio: timing?.hasAudio ?? false,
      captions: scene.captions ?? splitSentences(scene.narration),
      captionStarts: timing?.captionStarts,
    };
    from += frames;
    return timed;
  });
};

export const totalFrames = (timeline: TimedScene[]): number =>
  timeline.reduce((sum, s) => sum + s.frames, 0);
