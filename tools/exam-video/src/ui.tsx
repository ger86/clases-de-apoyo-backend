import React from "react";
import { AbsoluteFill, useCurrentFrame, interpolate } from "remotion";
import { COLORS, FONT_UI, WIDTH, FPS, color } from "./theme";
import { usePop, useReveal, Tex } from "./math";
import type { Badge, Chip } from "./types";

// ---------------------------------------------------------------------------
// Background: layered deep-teal radials + faint grid.
// ---------------------------------------------------------------------------
export const Background: React.FC = () => (
  <AbsoluteFill
    style={{
      background: `radial-gradient(1200px 700px at 78% -10%, ${COLORS.bg2}, rgba(0,0,0,0) 60%),
                   radial-gradient(1100px 800px at 8% 115%, #0c2233, rgba(0,0,0,0) 55%),
                   linear-gradient(160deg, ${COLORS.bg1} 0%, ${COLORS.bg0} 100%)`,
    }}
  >
    <AbsoluteFill
      style={{
        backgroundImage: `linear-gradient(${COLORS.grid} 1px, transparent 1px),
                          linear-gradient(90deg, ${COLORS.grid} 1px, transparent 1px)`,
        backgroundSize: "64px 64px",
        maskImage: "radial-gradient(1400px 900px at 50% 42%, black, transparent 78%)",
        WebkitMaskImage: "radial-gradient(1400px 900px at 50% 42%, black, transparent 78%)",
        opacity: 0.9,
      }}
    />
  </AbsoluteFill>
);

// ---------------------------------------------------------------------------
// Top bar: brand wordmark + exam line + overall progress.
// ---------------------------------------------------------------------------
export const TopBar: React.FC<{ progress: number; examLabel: string; site: string }> = ({
  progress,
  examLabel,
  site,
}) => {
  const [name, tld] = splitSite(site);
  return (
    <div
      style={{
        position: "absolute",
        top: 0,
        left: 0,
        width: WIDTH,
        height: 92,
        display: "flex",
        alignItems: "center",
        justifyContent: "space-between",
        padding: "0 64px",
        fontFamily: FONT_UI,
      }}
    >
      <div style={{ fontSize: 32, fontWeight: 800, letterSpacing: 0.3 }}>
        <span style={{ color: COLORS.ink }}>{name}</span>
        <span style={{ color: COLORS.teal }}>{tld}</span>
      </div>
      <div style={{ fontSize: 27, fontWeight: 600, color: COLORS.inkSoft, letterSpacing: 0.4 }}>
        {examLabel}
      </div>
      <div style={{ position: "absolute", left: 0, bottom: 0, width: WIDTH, height: 4, background: "rgba(255,255,255,0.06)" }}>
        <div
          style={{
            width: `${progress * 100}%`,
            height: "100%",
            background: `linear-gradient(90deg, ${COLORS.teal}, ${COLORS.pink})`,
            boxShadow: `0 0 12px ${COLORS.teal}`,
          }}
        />
      </div>
    </div>
  );
};

const splitSite = (site: string): [string, string] => {
  const i = site.lastIndexOf(".");
  return i > 0 ? [site.slice(0, i), site.slice(i)] : [site, ""];
};

// ---------------------------------------------------------------------------
// Phase chip + heading.
// ---------------------------------------------------------------------------
export const Heading: React.FC<{ phase: string; heading: string; accent: string }> = ({
  phase,
  heading,
  accent,
}) => {
  const chip = useReveal(2, { distance: 14 });
  const head = useReveal(7, { distance: 18 });
  return (
    <div style={{ position: "absolute", top: 150, left: 96, right: 96, fontFamily: FONT_UI }}>
      <div
        style={{
          ...chip,
          display: "inline-flex",
          alignItems: "center",
          gap: 12,
          padding: "9px 18px",
          borderRadius: 999,
          background: `${accent}1f`,
          border: `1.5px solid ${accent}66`,
        }}
      >
        <span style={{ width: 11, height: 11, borderRadius: 99, background: accent }} />
        <span style={{ fontSize: 23, fontWeight: 800, letterSpacing: 2.2, textTransform: "uppercase", color: accent }}>
          {phase}
        </span>
      </div>
      <div
        style={{
          ...head,
          marginTop: 18,
          fontSize: heading.length > 46 ? 52 : 60,
          fontWeight: 800,
          color: COLORS.ink,
          lineHeight: 1.08,
          maxWidth: 1500,
          letterSpacing: -0.5,
        }}
      >
        {heading}
      </div>
    </div>
  );
};

// ---------------------------------------------------------------------------
// Caption band. Uses TTS alignment times when available, else a length-based spread.
// ---------------------------------------------------------------------------
export const Captions: React.FC<{
  captions: string[];
  narrationFrames: number;
  starts?: number[];
}> = ({ captions, narrationFrames, starts }) => {
  const frame = useCurrentFrame();
  let bounds: number[];
  if (starts && starts.length === captions.length) {
    bounds = starts.map((s) => Math.round(s * FPS));
  } else {
    const weights = captions.map((c) => c.length);
    const totalW = weights.reduce((a, b) => a + b, 0) || 1;
    let acc = 0;
    bounds = weights.map((w) => {
      const start = (acc / totalW) * narrationFrames;
      acc += w;
      return start;
    });
  }
  let idx = 0;
  for (let i = 0; i < bounds.length; i += 1) {
    if (frame >= bounds[i]) idx = i;
  }
  const fade = interpolate(frame - bounds[idx], [0, 7], [0, 1], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
  });
  return (
    <div
      style={{
        position: "absolute",
        left: 0,
        bottom: 0,
        width: WIDTH,
        height: 132,
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        background: "linear-gradient(180deg, rgba(7,15,24,0) 0%, rgba(5,11,18,0.92) 55%)",
        fontFamily: FONT_UI,
      }}
    >
      <div
        style={{
          opacity: fade,
          fontSize: 34,
          fontWeight: 600,
          color: COLORS.ink,
          textAlign: "center",
          maxWidth: 1480,
          padding: "0 40px",
          textShadow: "0 2px 18px rgba(0,0,0,0.6)",
        }}
      >
        {captions[idx]}
      </div>
    </div>
  );
};

// ---------------------------------------------------------------------------
// Result badge: the verdict. Pops in, color = meaning.
// ---------------------------------------------------------------------------
export const ResultBadge: React.FC<{ badge: Badge; delay?: number }> = ({ badge, delay = 0 }) => {
  const pop = usePop(delay);
  const c = color(badge.color);
  return (
    <div
      style={{
        ...pop,
        display: "inline-flex",
        flexDirection: "column",
        alignItems: "center",
        gap: 6,
        padding: "20px 42px",
        borderRadius: 20,
        background: `${c}1c`,
        border: `2.5px solid ${c}`,
        boxShadow: `0 0 46px ${c}44`,
        fontFamily: FONT_UI,
      }}
    >
      <span style={{ fontSize: 46, fontWeight: 800, color: c, letterSpacing: -0.4 }}>{badge.text}</span>
      {badge.sub ? <span style={{ fontSize: 26, fontWeight: 600, color: COLORS.inkSoft }}>{badge.sub}</span> : null}
    </div>
  );
};

// Pill chip (a value, a step, a label). Carries semantic color.
export const ChipView: React.FC<{ chip: Chip; delay?: number }> = ({ chip, delay = 0 }) => {
  const pop = usePop(delay);
  const c = color(chip.color);
  const size = chip.big ? 40 : 30;
  return (
    <div
      style={{
        ...pop,
        display: "inline-flex",
        alignItems: "center",
        gap: 10,
        padding: chip.big ? "16px 30px" : "11px 22px",
        borderRadius: 999,
        background: `${c}1f`,
        border: `2px solid ${c}`,
        color: c,
        fontFamily: FONT_UI,
        fontWeight: 800,
        fontSize: size,
        boxShadow: `0 0 26px ${c}33`,
      }}
    >
      {chip.tex ? <Tex tex={chip.tex} display={false} size={size} color={c} /> : chip.text}
    </div>
  );
};

export const ChipRow: React.FC<{ chips?: Chip[]; delay: number; step?: number }> = ({ chips, delay, step = 9 }) =>
  chips && chips.length ? (
    <div style={{ display: "flex", gap: 24, marginTop: 6, flexWrap: "wrap", justifyContent: "center" }}>
      {chips.map((chip, i) => (
        <ChipView key={i} chip={chip} delay={delay + i * step} />
      ))}
    </div>
  ) : null;

// Footer line shared by content scenes.
export const Footer: React.FC<{ index: number; total: number; question: string; topic: string }> = ({
  index,
  total,
  question,
  topic,
}) => (
  <div
    style={{
      position: "absolute",
      left: 96,
      right: 96,
      bottom: 150,
      display: "flex",
      justifyContent: "space-between",
      alignItems: "center",
      fontFamily: FONT_UI,
      fontSize: 24,
      fontWeight: 600,
      color: COLORS.muted,
    }}
  >
    <span>
      {question} · {topic}
    </span>
    <span style={{ color: COLORS.inkSoft, fontWeight: 800 }}>
      {String(index + 1).padStart(2, "0")} / {String(total).padStart(2, "0")}
    </span>
  </div>
);
