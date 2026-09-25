import React from "react";
import { AbsoluteFill } from "remotion";
import { Background } from "./ui";
import { Tex } from "./math";
import { COLORS, FONT_UI } from "./theme";
import type { VideoProps } from "./types";

// 1080x1920 cover for the reel, used as the grid image on Instagram.
// Content comes from exercise.json "reel.cover"; the safe box matches the video.
export const ReelCover: React.FC<VideoProps> = (props) => {
  const c = props.reel?.cover ?? {
    kicker: props.exam.label,
    line1: props.exam.question,
    line2: props.exam.topic,
  };
  const site = props.exam.site ?? "ClasesDeApoyo.com";
  const i = site.lastIndexOf(".");
  // A one-line formula is set at 58, but a 3x3 determinant is three lines and at that
  // size it would run into the footer. Shrink it so the whole block still fits the
  // roughly 420 px between the formula box and the site name.
  const texRows = c.tex ? c.tex.split("\\\\").length : 1;
  const texSize = Math.min(58, Math.round(114 / texRows));
  return (
    <AbsoluteFill style={{ backgroundColor: COLORS.bg0, fontFamily: FONT_UI }}>
      <Background />
      <div style={{ position: "absolute", left: 70, right: 170, top: 300, fontSize: 28, fontWeight: 800, letterSpacing: 3, color: COLORS.tealSoft, textTransform: "uppercase", lineHeight: 1.3 }}>
        {c.kicker}
      </div>
      <div style={{ position: "absolute", left: 70, right: 170, top: 400, fontSize: 104, fontWeight: 900, lineHeight: 0.98, letterSpacing: -3, color: COLORS.ink, whiteSpace: "pre-line" }}>
        {c.line1}
      </div>
      <div style={{ position: "absolute", left: 70, right: 170, top: 760, fontSize: 52, fontWeight: 800, lineHeight: 1.12, letterSpacing: -0.5, color: COLORS.solved, whiteSpace: "pre-line" }}>
        {c.line2}
      </div>
      {/* Formula and badge stack in one column, so the badge always clears the formula
          box however tall it is: a 3x3 determinant is three lines, not one. */}
      <div
        style={{
          position: "absolute",
          left: 70,
          right: 170,
          top: 1000,
          display: "flex",
          flexDirection: "column",
          alignItems: "flex-start",
          gap: 24,
        }}
      >
        {c.tex ? (
          <div
            style={{
              padding: "26px 36px",
              borderRadius: 30,
              background: COLORS.panel,
              border: `2px solid ${COLORS.panelStroke}`,
              boxShadow: `0 0 80px ${COLORS.teal}44`,
            }}
          >
            <Tex tex={c.tex} size={texSize} color={COLORS.tealSoft} />
          </div>
        ) : null}
        {c.badge ? (
          <div
            style={{
              padding: "20px 38px",
              borderRadius: 999,
              background: `${COLORS.danger}22`,
              border: `3px solid ${COLORS.danger}`,
              color: COLORS.danger,
              fontSize: 46,
              fontWeight: 900,
              boxShadow: `0 0 50px ${COLORS.danger}55`,
            }}
          >
            {c.badge}
          </div>
        ) : null}
      </div>
      <div style={{ position: "absolute", left: 70, bottom: 420, fontSize: 44, fontWeight: 800 }}>
        <span style={{ color: COLORS.ink }}>{i > 0 ? site.slice(0, i) : site}</span>
        <span style={{ color: COLORS.teal }}>{i > 0 ? site.slice(i) : ""}</span>
      </div>
      <div style={{ position: "absolute", left: 70, right: 170, bottom: 360, fontSize: 30, fontWeight: 700, color: COLORS.inkSoft }}>
        {props.exam.question} · solución completa en YouTube
      </div>
    </AbsoluteFill>
  );
};
