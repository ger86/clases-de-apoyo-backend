import React from "react";
import katex from "katex";
import "katex/dist/katex.min.css";
import { useCurrentFrame, useVideoConfig, spring, interpolate } from "remotion";
import { COLORS } from "./theme";

// Real math typesetting (KaTeX, rendered in Chromium).
export const Tex: React.FC<{
  tex: string;
  display?: boolean;
  size?: number;
  color?: string;
  style?: React.CSSProperties;
}> = ({ tex, display = true, size = 92, color = COLORS.ink, style }) => {
  const html = katex.renderToString(tex, {
    displayMode: display,
    throwOnError: false,
    strict: false,
    output: "html",
  });
  return (
    <div
      style={{ fontSize: size, color, lineHeight: 1.1, ...style }}
      dangerouslySetInnerHTML={{ __html: html }}
    />
  );
};

// Spring-based entrance: fade + rise.
export const useReveal = (delay = 0, opts?: { distance?: number; damping?: number }) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const s = spring({
    frame: frame - delay,
    fps,
    config: { damping: opts?.damping ?? 200, mass: 0.7, stiffness: 120 },
  });
  const distance = opts?.distance ?? 26;
  return {
    opacity: interpolate(s, [0, 1], [0, 1]),
    transform: `translateY(${interpolate(s, [0, 1], [distance, 0])}px)`,
    progress: s,
  };
};

export const RevealUp: React.FC<{
  delay?: number;
  distance?: number;
  style?: React.CSSProperties;
  children: React.ReactNode;
}> = ({ delay = 0, distance, style, children }) => {
  const r = useReveal(delay, { distance });
  return (
    <div style={{ opacity: r.opacity, transform: r.transform, ...style }}>
      {children}
    </div>
  );
};

// Pop-in for badges/chips: overshoot scale + fade.
export const usePop = (delay = 0) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const s = spring({
    frame: frame - delay,
    fps,
    config: { damping: 12, mass: 0.8, stiffness: 170 },
  });
  return {
    opacity: interpolate(frame - delay, [0, 6], [0, 1], {
      extrapolateLeft: "clamp",
      extrapolateRight: "clamp",
    }),
    transform: `scale(${interpolate(s, [0, 1], [0.6, 1])})`,
  };
};
