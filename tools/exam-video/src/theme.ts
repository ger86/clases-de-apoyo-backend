// Visual design system shared by every exercise video.
// Dark "studio" theme: near-black teal background so math and color-coded
// results pop the way they do on premium math channels.

export const COLORS = {
  bg0: "#070f18",
  bg1: "#0b1622",
  bg2: "#0f2030",
  panel: "rgba(255,255,255,0.035)",
  panelStroke: "rgba(125,214,234,0.16)",

  ink: "#eef6fa",
  inkSoft: "#bcd0db",
  muted: "#6f8595",

  teal: "#19a7cc",
  tealSoft: "#7fd6ea",
  pink: "#ff2e6e",
  pinkSoft: "#ff7aa3",

  // Semantic meaning, kept consistent across a whole video.
  given: "#19a7cc", // data / neutral
  danger: "#ff2e6e", // impossible / no solution / rare event
  indet: "#38d6c6", // intermediate / "depends"
  solved: "#33d98a", // final answer
  warn: "#ffc857", // attention / caveat

  grid: "rgba(127,214,234,0.07)",
} as const;

export type ColorName = keyof typeof COLORS;

export const color = (name?: string, fallback: string = COLORS.ink): string =>
  name && name in COLORS ? COLORS[name as ColorName] : fallback;

export const FONT_UI =
  "'SF Pro Display', 'SF Pro Text', -apple-system, 'Helvetica Neue', Arial, sans-serif";

export const FPS = 30;
export const WIDTH = 1920;
export const HEIGHT = 1080;
