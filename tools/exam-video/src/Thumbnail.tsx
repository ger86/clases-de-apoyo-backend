import React from "react";
import { AbsoluteFill } from "remotion";
import { Background } from "./ui";
import { Tex } from "./math";
import { COLORS, FONT_UI } from "./theme";
import type { VideoProps } from "./types";

// 1280x720 YouTube thumbnail: big words, one formula, brand. Content comes from exercise.json "youtube.thumbnail".
export const Thumbnail: React.FC<VideoProps> = (props) => {
  const t = props.youtube?.thumbnail ?? { kicker: props.exam.label, line1: props.exam.question, line2: props.exam.topic };
  const site = props.exam.site ?? "ClasesDeApoyo.com";
  const i = site.lastIndexOf(".");
  return (
    <AbsoluteFill style={{ backgroundColor: COLORS.bg0, fontFamily: FONT_UI }}>
      <Background />
      <div style={{ position: "absolute", left: 72, top: 64, fontSize: 30, fontWeight: 800, letterSpacing: 3, color: COLORS.tealSoft, textTransform: "uppercase" }}>
        {t.kicker}
      </div>
      <div style={{ position: "absolute", left: 72, top: 128, width: 760, fontSize: 96, fontWeight: 900, lineHeight: 1.0, letterSpacing: -2, color: COLORS.ink, whiteSpace: "pre-line" }}>
        {t.line1}
      </div>
      <div style={{ position: "absolute", left: 72, top: 360, width: 760, fontSize: 60, fontWeight: 800, lineHeight: 1.05, letterSpacing: -1, color: COLORS.solved, whiteSpace: "pre-line" }}>
        {t.line2}
      </div>
      {t.tex ? (
        <div
          style={{
            position: "absolute",
            right: 64,
            top: 170,
            padding: "22px 34px",
            borderRadius: 30,
            background: COLORS.panel,
            border: `2px solid ${COLORS.panelStroke}`,
            boxShadow: `0 0 80px ${COLORS.teal}44`,
          }}
        >
          <Tex tex={t.tex} size={54} color={COLORS.tealSoft} />
        </div>
      ) : null}
      {t.badge ? (
        <div
          style={{
            position: "absolute",
            right: 64,
            top: 500,
            padding: "18px 34px",
            borderRadius: 999,
            background: `${COLORS.danger}22`,
            border: `3px solid ${COLORS.danger}`,
            color: COLORS.danger,
            fontSize: 44,
            fontWeight: 900,
            boxShadow: `0 0 50px ${COLORS.danger}55`,
          }}
        >
          {t.badge}
        </div>
      ) : null}
      <div style={{ position: "absolute", left: 72, bottom: 56, fontSize: 40, fontWeight: 800 }}>
        <span style={{ color: COLORS.ink }}>{i > 0 ? site.slice(0, i) : site}</span>
        <span style={{ color: COLORS.teal }}>{i > 0 ? site.slice(i) : ""}</span>
      </div>
      <div style={{ position: "absolute", right: 72, bottom: 60, fontSize: 30, fontWeight: 700, color: COLORS.inkSoft }}>
        {props.exam.question} · resuelta paso a paso
      </div>
    </AbsoluteFill>
  );
};
