// The exercise.json contract. Everything a video needs is data; no per-exercise code.

export type Row = {
  tex?: string; // KaTeX source
  text?: string; // plain text alternative
  label?: string; // small uppercase label shown before the content
  color?: string; // COLORS key
  size?: number; // font size in px
};

export type Chip = { tex?: string; text?: string; color: string; big?: boolean };

export type Badge = { text: string; sub?: string; color: string };

export type SummaryRow = { label: string; tex?: string; text?: string; color: string };

export type Body =
  | {
      type: "hook";
      kicker?: string;
      title: string; // wrap a fragment in *asterisks* to highlight it
      tex?: string;
      texColor?: string;
      chips?: Chip[];
    }
  | { type: "rows"; rows: Row[]; chips?: Chip[]; badge?: Badge }
  | {
      type: "dots";
      count: number;
      columns?: number;
      highlight?: number[]; // zero-based indexes drawn in highlightColor
      color?: string;
      highlightColor?: string;
      lead?: Row;
      chips?: Chip[];
      badge?: Badge;
    }
  | {
      type: "normal";
      mu: number;
      sigma: number;
      cut: number;
      tail: "right" | "left";
      cutLabel?: string;
      areaLabel?: string;
      lead?: Row;
      chips?: Chip[];
    }
  | {
      type: "table"; // sign table: header cells and rows of colored cells
      columns: string[]; // TeX per column header
      rows: { label: string; cells: { tex?: string; text?: string; color?: string }[] }[];
      lead?: Row;
      chips?: Chip[];
    }
  | {
      type: "plot"; // graph of one function, optional marked points and shaded area
      fn: string; // JavaScript expression in x, e.g. "(x*x+1)/(Math.abs(x)+1)"
      xMin: number;
      xMax: number;
      yMin: number;
      yMax: number;
      points?: { x: number; y: number; label?: string; color?: string }[];
      shade?: { from: number; to: number; color?: string; label?: string };
      xTicks?: { x: number; label: string }[];
      lead?: Row;
      chips?: Chip[];
    }
  | { type: "summary"; rows: SummaryRow[]; closing?: string };

export type SceneDef = {
  id: string;
  phase: string;
  heading: string;
  narration: string; // text sent to TTS
  captions?: string[]; // one per narration sentence; defaults to the sentences
  tail?: number; // seconds of breathing room after the narration
  body: Body;
};

export type Exercise = {
  slug: string;
  exam: { label: string; question: string; topic: string; site?: string };
  voice: {
    provider: "elevenlabs";
    model: string;
    voiceId: string;
    fallbackVoiceId?: string;
  };
  phaseColors?: Record<string, string>;
  youtube?: {
    title: string;
    description: string; // chapters are appended automatically
    tags: string[];
    thumbnail: { kicker: string; line1: string; line2: string; tex?: string; badge?: string };
  };
  scenes: SceneDef[];
};

export type SceneTiming = {
  id: string;
  durSec: number; // measured narration length
  hasAudio: boolean;
  captionStarts?: number[]; // seconds from scene start, from TTS alignment
};

export type VideoProps = Exercise & {
  timing: { scenes: SceneTiming[] };
};

export type TimedScene = SceneDef & {
  index: number;
  from: number;
  frames: number;
  narrationFrames: number;
  hasAudio: boolean;
  captions: string[];
  captionStarts?: number[];
};
