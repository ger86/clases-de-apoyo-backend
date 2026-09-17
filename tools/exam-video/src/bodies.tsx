import React from "react";
import { useCurrentFrame, interpolate } from "remotion";
import { COLORS, FONT_UI, color } from "./theme";
import { useLayout } from "./layout";
import { Tex, RevealUp, usePop } from "./math";
import { ResultBadge, ChipRow, Highlighted } from "./ui";
import type { Body, Row } from "./types";

// Reveal schedule: first element appears at START, then STEP frames per element.
const START = 6;
const STEP = 12;

// Centres its children inside the layout's content box. On vertical frames that
// box stops short of the right edge, where the apps draw their button column.
const CenterStack: React.FC<{ top?: number; bottom?: number; gap?: number; children: React.ReactNode }> = ({
  top,
  bottom,
  gap = 36,
  children,
}) => {
  const l = useLayout();
  return (
    <div
      style={{
        position: "absolute",
        top: top ?? l.stackTop,
        bottom: bottom ?? l.stackBottom,
        left: l.contentLeft,
        right: l.contentRight,
        display: "flex",
        flexDirection: "column",
        alignItems: "center",
        justifyContent: "center",
        gap,
      }}
    >
      {children}
    </div>
  );
};

// One content row: optional label + tex or text.
const RowView: React.FC<{ row: Row; delay: number; defaultSize?: number }> = ({ row, delay, defaultSize = 56 }) => {
  const l = useLayout();
  const c = color(row.color, COLORS.ink);
  const size = row.size ?? defaultSize;
  return (
    <RevealUp delay={delay} distance={18}>
      <div style={{ display: "flex", alignItems: "center", gap: l.vertical ? 18 : 28, fontFamily: FONT_UI }}>
        {row.label ? (
          <span
            style={{
              fontSize: 22,
              fontWeight: 800,
              letterSpacing: 2,
              // Uppercasing would turn Greek letters such as λ into Λ.
              textTransform: /^[\x00-\x7F]*$/.test(row.label) ? "uppercase" : "none",
              color: COLORS.muted,
              minWidth: l.vertical ? 90 : 150,
              textAlign: "right",
            }}
          >
            {row.label}
          </span>
        ) : null}
        {row.tex ? (
          <Tex tex={row.tex} display={false} size={size} color={c} />
        ) : (
          <span style={{ fontSize: size * 0.8, fontWeight: 700, color: c, maxWidth: l.contentWidth, textAlign: "center" }}>
            {row.text}
          </span>
        )}
      </div>
    </RevealUp>
  );
};

// ---------------------------------------------------------------------------
const HookBody: React.FC<{ body: Extract<Body, { type: "hook" }> }> = ({ body }) => {
  const l = useLayout();
  return (
  <div style={{ position: "absolute", inset: 0, fontFamily: FONT_UI }}>
    <CenterStack top={l.hookStackTop} bottom={l.hookStackBottom} gap={44}>
      {body.kicker ? (
        <RevealUp delay={2} distance={12}>
          <span style={{ fontSize: l.hookKickerSize, fontWeight: 800, letterSpacing: 3, color: COLORS.tealSoft, textTransform: "uppercase", textAlign: "center", display: "block" }}>
            {body.kicker}
          </span>
        </RevealUp>
      ) : null}
      <RevealUp delay={6} distance={20}>
        <div
          style={{
            fontSize: l.hookTitleSize,
            fontWeight: 800,
            color: COLORS.ink,
            textAlign: "center",
            maxWidth: l.vertical ? l.contentWidth : 1500,
            lineHeight: 1.06,
            letterSpacing: -0.8,
            whiteSpace: "pre-line",
          }}
        >
          <Highlighted text={body.title} />
        </div>
      </RevealUp>
      {body.tex ? (
        <RevealUp delay={22} distance={24}>
          <div
            style={{
              padding: l.vertical ? "16px 28px" : "18px 48px",
              borderRadius: 26,
              background: COLORS.panel,
              border: `1.5px solid ${COLORS.panelStroke}`,
              boxShadow: `0 0 60px ${color(body.texColor, COLORS.teal)}33`,
            }}
          >
            <Tex tex={body.tex} size={body.texSize ?? 100} color={color(body.texColor, COLORS.teal)} />
          </div>
        </RevealUp>
      ) : null}
      <ChipRow chips={body.chips} delay={34} />
    </CenterStack>
  </div>
  );
};

// ---------------------------------------------------------------------------
const RowsBody: React.FC<{ body: Extract<Body, { type: "rows" }> }> = ({ body }) => {
  const l = useLayout();
  const n = body.rows.length;
  // Density: rows plus the space a badge / chip row takes. Dense scenes shrink so nothing touches the heading.
  // Rows with a cases block or a matrix of 3+ rows take about three lines of height.
  const tall = body.rows.filter((r) => r.tex && (r.tex.includes("\\begin{cases}") || (r.tex.includes("matrix}") && (r.tex.match(/\\\\/g) ?? []).length >= 2))).length;
  const density = n + tall * 2 + (body.badge ? 2 : 0) + (body.chips?.length ? 1 : 0);
  const scale = density >= 6 ? 0.8 : density >= 5 ? 0.88 : 1;
  const base = l.vertical ? (n >= 5 ? 40 : n === 4 ? 46 : 54) : n >= 5 ? 44 : n === 4 ? 50 : 58;
  const defaultSize = base * scale;
  return (
    <CenterStack gap={density >= 5 ? 18 : n >= 4 ? 26 : 34}>
      {body.rows.map((row, i) => (
        <RowView key={i} row={{ ...row, size: row.size ? row.size * scale : undefined }} delay={START + i * STEP} defaultSize={defaultSize} />
      ))}
      <ChipRow chips={body.chips} delay={START + n * STEP + 4} />
      {body.badge ? (
        <div style={{ marginTop: 10 }}>
          <ResultBadge badge={body.badge} delay={START + n * STEP + (body.chips?.length ?? 0) * 9 + 8} />
        </div>
      ) : null}
    </CenterStack>
  );
};

// ---------------------------------------------------------------------------
// Dots: N items (olives, bags, trials...) that pop in one by one.
const Dot: React.FC<{ delay: number; fill: string; highlighted: boolean }> = ({ delay, fill, highlighted }) => {
  const pop = usePop(delay);
  return (
    <div
      style={{
        ...pop,
        width: 64,
        height: 64,
        borderRadius: "50% 50% 50% 50% / 60% 60% 40% 40%",
        background: `radial-gradient(circle at 35% 30%, ${fill}ee, ${fill}88 70%)`,
        border: `2.5px solid ${highlighted ? fill : "rgba(255,255,255,0.18)"}`,
        boxShadow: highlighted ? `0 0 26px ${fill}88` : `0 2px 10px rgba(0,0,0,0.5)`,
      }}
    />
  );
};

const DotsBody: React.FC<{ body: Extract<Body, { type: "dots" }> }> = ({ body }) => {
  const base = color(body.color, "#5e8a3a");
  const hi = color(body.highlightColor, COLORS.danger);
  const set = new Set(body.highlight ?? []);
  const columns = body.columns ?? Math.min(body.count, 12);
  const dotsDone = START + body.count * 3;
  return (
    <CenterStack gap={40}>
      {body.lead ? <RowView row={body.lead} delay={START} defaultSize={52} /> : null}
      <div
        style={{
          display: "grid",
          gridTemplateColumns: `repeat(${columns}, 64px)`,
          gap: 22,
          padding: "30px 40px",
          borderRadius: 28,
          background: COLORS.panel,
          border: `1.5px solid ${COLORS.panelStroke}`,
        }}
      >
        {Array.from({ length: body.count }, (_, i) => (
          <Dot key={i} delay={START + 8 + i * 3} fill={set.has(i) ? hi : base} highlighted={set.has(i)} />
        ))}
      </div>
      <ChipRow chips={body.chips} delay={dotsDone + 8} />
      {body.badge ? <ResultBadge badge={body.badge} delay={dotsDone + 8 + (body.chips?.length ?? 0) * 9 + 8} /> : null}
    </CenterStack>
  );
};

// ---------------------------------------------------------------------------
// Normal curve with a shaded tail. The visual argument for "this is rare".
const NormalBody: React.FC<{ body: Extract<Body, { type: "normal" }> }> = ({ body }) => {
  const l = useLayout();
  const frame = useCurrentFrame();
  const W = l.graphWidth;
  const H = 400;
  const pad = 60;
  const x0 = body.mu - 4 * body.sigma;
  const x1 = body.mu + 4 * body.sigma;
  const toX = (x: number) => pad + ((x - x0) / (x1 - x0)) * (W - 2 * pad);
  const pdf = (x: number) => Math.exp(-0.5 * ((x - body.mu) / body.sigma) ** 2);
  const baseY = H - 70;
  const toY = (x: number) => baseY - pdf(x) * (H - 140);
  const steps = 200;
  const pts: string[] = [];
  for (let i = 0; i <= steps; i += 1) {
    const x = x0 + ((x1 - x0) * i) / steps;
    pts.push(`${toX(x).toFixed(1)},${toY(x).toFixed(1)}`);
  }
  const curveDraw = interpolate(frame, [START, START + 40], [0, 1], { extrapolateLeft: "clamp", extrapolateRight: "clamp" });
  const visibleCount = Math.max(2, Math.round(pts.length * curveDraw));
  const curvePath = `M ${pts.slice(0, visibleCount).join(" L ")}`;

  const tailStart = body.tail === "right" ? body.cut : x0;
  const tailEnd = body.tail === "right" ? x1 : body.cut;
  const tailPts: string[] = [];
  for (let i = 0; i <= 80; i += 1) {
    const x = tailStart + ((tailEnd - tailStart) * i) / 80;
    tailPts.push(`${toX(x).toFixed(1)},${toY(x).toFixed(1)}`);
  }
  const areaPath = `M ${toX(tailStart).toFixed(1)},${baseY} L ${tailPts.join(" L ")} L ${toX(tailEnd).toFixed(1)},${baseY} Z`;
  const areaIn = interpolate(frame, [START + 46, START + 66], [0, 1], { extrapolateLeft: "clamp", extrapolateRight: "clamp" });
  const cutIn = interpolate(frame, [START + 44, START + 56], [0, 1], { extrapolateLeft: "clamp", extrapolateRight: "clamp" });
  const cutX = toX(body.cut);
  const muX = toX(body.mu);
  const areaLabelX = body.tail === "right" ? Math.min(cutX + 150, W - pad - 40) : Math.max(cutX - 150, pad + 40);

  return (
    <CenterStack gap={22} top={l.graphStackTop}>
      {body.lead ? <RowView row={body.lead} delay={START} defaultSize={50} /> : null}
      <RevealUp delay={START + 4} distance={16}>
        <svg width={W} height={H} viewBox={`0 0 ${W} ${H}`} style={{ overflow: "visible", fontFamily: FONT_UI }}>
          <line x1={pad - 20} y1={baseY} x2={W - pad + 20} y2={baseY} stroke="#3a566b" strokeWidth={3} strokeLinecap="round" />
          <path d={areaPath} fill={COLORS.danger} opacity={0.55 * areaIn} />
          <path d={curvePath} fill="none" stroke={COLORS.tealSoft} strokeWidth={5} strokeLinecap="round" strokeLinejoin="round" />
          <line x1={muX} y1={baseY} x2={muX} y2={toY(body.mu)} stroke={COLORS.muted} strokeWidth={2} strokeDasharray="8 8" />
          <text x={muX} y={baseY + 40} fill={COLORS.inkSoft} fontSize={28} fontWeight={700} textAnchor="middle">
            μ = {String(body.mu).replace(".", ",")}
          </text>
          <g opacity={cutIn}>
            <line x1={cutX} y1={baseY} x2={cutX} y2={baseY - (H - 140) * 0.9} stroke={COLORS.danger} strokeWidth={4} strokeLinecap="round" />
            <text x={cutX} y={baseY + 40} fill={COLORS.danger} fontSize={28} fontWeight={800} textAnchor="middle">
              {body.cutLabel ?? String(body.cut).replace(".", ",")}
            </text>
          </g>
          {body.areaLabel ? (
            <text x={areaLabelX} y={baseY - 120} fill={COLORS.danger} fontSize={30} fontWeight={800} textAnchor="middle" opacity={areaIn}>
              {body.areaLabel}
            </text>
          ) : null}
        </svg>
      </RevealUp>
      <ChipRow chips={body.chips} delay={START + 70} />
    </CenterStack>
  );
};

// ---------------------------------------------------------------------------
// Sign table: columns reveal left to right.
const TableBody: React.FC<{ body: Extract<Body, { type: "table" }> }> = ({ body }) => {
  const l = useLayout();
  const cols = body.columns.length;
  const cellW = Math.min(200, Math.floor(l.tableWidth / cols));
  // Narrow cells need smaller type, or a long header wraps into three lines.
  const tight = cellW / 200;
  const headSize = l.vertical ? Math.max(17, Math.round(26 * tight)) : 26;
  const cellSize = l.vertical ? Math.max(22, Math.round(34 * tight)) : 34;
  const cellStyle: React.CSSProperties = {
    width: cellW,
    height: l.vertical ? Math.max(66, Math.round(86 * tight)) : 86,
    display: "flex",
    alignItems: "center",
    justifyContent: "center",
    borderLeft: `1px solid ${COLORS.panelStroke}`,
  };
  return (
    <CenterStack gap={30}>
      {body.lead ? <RowView row={body.lead} delay={START} defaultSize={46} /> : null}
      <RevealUp delay={START + 4} distance={16}>
        <div
          style={{
            display: "grid",
            gridTemplateColumns: `${l.tableLabelWidth}px repeat(${cols}, ${cellW}px)`,
            borderRadius: 22,
            overflow: "hidden",
            background: COLORS.panel,
            border: `1.5px solid ${COLORS.panelStroke}`,
            fontFamily: FONT_UI,
          }}
        >
          <div style={{ ...cellStyle, width: l.tableLabelWidth, borderLeft: "none", borderBottom: `1px solid ${COLORS.panelStroke}` }} />
          {body.columns.map((c, i) => (
            <div key={i} style={{ ...cellStyle, borderBottom: `1px solid ${COLORS.panelStroke}` }}>
              <RevealUp delay={START + 10 + i * 6} distance={8}>
                <Tex tex={c} display={false} size={headSize} color={COLORS.inkSoft} />
              </RevealUp>
            </div>
          ))}
          {body.rows.map((row, r) => (
            <React.Fragment key={r}>
              <div style={{ ...cellStyle, width: l.tableLabelWidth, borderLeft: "none", borderTop: r > 0 ? `1px solid ${COLORS.panelStroke}` : "none" }}>
                <Tex tex={row.label} display={false} size={cellSize} color={COLORS.ink} />
              </div>
              {row.cells.map((cell, i) => {
                const c = color(cell.color, COLORS.ink);
                return (
                  <div key={i} style={{ ...cellStyle, borderTop: r > 0 ? `1px solid ${COLORS.panelStroke}` : "none" }}>
                    <RevealUp delay={START + 24 + r * 8 + i * 9} distance={10}>
                      {cell.tex ? (
                        <Tex tex={cell.tex} display={false} size={cellSize} color={c} />
                      ) : (
                        <span style={{ fontSize: cellSize + 2, fontWeight: 800, color: c }}>{cell.text}</span>
                      )}
                    </RevealUp>
                  </div>
                );
              })}
            </React.Fragment>
          ))}
        </div>
      </RevealUp>
      <ChipRow chips={body.chips} delay={START + 24 + body.rows.length * 8 + cols * 9 + 6} />
    </CenterStack>
  );
};

// ---------------------------------------------------------------------------
// Function plot: the curve draws itself, then points and shaded area appear.
const PlotBody: React.FC<{ body: Extract<Body, { type: "plot" }> }> = ({ body }) => {
  const l = useLayout();
  const frame = useCurrentFrame();
  const W = l.plotWidth;
  const H = body.lead ? 330 : body.chips?.length ? 400 : 480;
  const pad = 50;
  // eslint-disable-next-line no-new-func
  const f = React.useMemo(() => new Function("x", `return (${body.fn});`) as (x: number) => number, [body.fn]);
  const toX = (x: number) => pad + ((x - body.xMin) / (body.xMax - body.xMin)) * (W - 2 * pad);
  const toY = (y: number) => H - pad - ((y - body.yMin) / (body.yMax - body.yMin)) * (H - 2 * pad);
  const steps = 400;
  const pts: string[] = [];
  for (let i = 0; i <= steps; i += 1) {
    const x = body.xMin + ((body.xMax - body.xMin) * i) / steps;
    const y = f(x);
    if (!Number.isFinite(y) || y < body.yMin - 1 || y > body.yMax + 1) continue;
    pts.push(`${toX(x).toFixed(1)},${toY(Math.max(body.yMin, Math.min(body.yMax, y))).toFixed(1)}`);
  }
  const draw = interpolate(frame, [START + 4, START + 50], [0, 1], { extrapolateLeft: "clamp", extrapolateRight: "clamp" });
  const path = `M ${pts.slice(0, Math.max(2, Math.round(pts.length * draw))).join(" L ")}`;
  const later = interpolate(frame, [START + 54, START + 70], [0, 1], { extrapolateLeft: "clamp", extrapolateRight: "clamp" });
  const x0 = toX(0);
  const y0 = toY(0);

  let shadePath = "";
  if (body.shade) {
    const seg: string[] = [];
    for (let i = 0; i <= 120; i += 1) {
      const x = body.shade.from + ((body.shade.to - body.shade.from) * i) / 120;
      seg.push(`${toX(x).toFixed(1)},${toY(f(x)).toFixed(1)}`);
    }
    shadePath = `M ${toX(body.shade.from).toFixed(1)},${y0} L ${seg.join(" L ")} L ${toX(body.shade.to).toFixed(1)},${y0} Z`;
  }
  const shadeColor = color(body.shade?.color, COLORS.indet);

  return (
    <CenterStack gap={22} top={l.graphStackTop}>
      {body.lead ? <RowView row={body.lead} delay={START} defaultSize={48} /> : null}
      <RevealUp delay={START + 2} distance={14}>
        <svg width={W} height={H} viewBox={`0 0 ${W} ${H}`} style={{ overflow: "visible", fontFamily: FONT_UI }}>
          <line x1={pad - 10} y1={y0} x2={W - pad + 10} y2={y0} stroke="#3a566b" strokeWidth={3} />
          <polygon points={`${W - pad + 10},${y0} ${W - pad - 4},${y0 - 7} ${W - pad - 4},${y0 + 7}`} fill="#3a566b" />
          <line x1={x0} y1={H - pad + 10} x2={x0} y2={pad - 10} stroke="#3a566b" strokeWidth={3} />
          <polygon points={`${x0},${pad - 10} ${x0 - 7},${pad + 4} ${x0 + 7},${pad + 4}`} fill="#3a566b" />
          {(body.xTicks ?? []).map((t, i) => (
            <g key={i}>
              <line x1={toX(t.x)} y1={y0 - 8} x2={toX(t.x)} y2={y0 + 8} stroke={COLORS.muted} strokeWidth={2} />
              <text x={toX(t.x)} y={y0 + 34} fill={COLORS.muted} fontSize={22} fontWeight={700} textAnchor="middle">{t.label}</text>
            </g>
          ))}
          {body.shade ? <path d={shadePath} fill={shadeColor} opacity={0.45 * later} /> : null}
          {body.shade?.label ? (
            <text x={toX((body.shade.from + body.shade.to) / 2)} y={toY(f((body.shade.from + body.shade.to) / 2) * 0.28)} fill={COLORS.ink} fontSize={30} fontWeight={800} textAnchor="middle" opacity={later}>
              {body.shade.label}
            </text>
          ) : null}
          <path d={path} fill="none" stroke={COLORS.tealSoft} strokeWidth={5} strokeLinecap="round" strokeLinejoin="round" />
          {(body.points ?? []).map((p, i) => {
            const c = color(p.color, COLORS.solved);
            const pop = interpolate(frame, [START + 54 + i * 8, START + 66 + i * 8], [0, 1], { extrapolateLeft: "clamp", extrapolateRight: "clamp" });
            return (
              <g key={i} opacity={pop}>
                <circle cx={toX(p.x)} cy={toY(p.y)} r={22} fill={c} opacity={0.25} />
                <circle cx={toX(p.x)} cy={toY(p.y)} r={11} fill={c} stroke="#0b1622" strokeWidth={3} />
                {p.label ? (
                  <text x={toX(p.x)} y={toY(p.y) + (p.label.startsWith("m\u00e1x") || p.label.startsWith("máx") ? -30 : 48)} fill={c} fontSize={26} fontWeight={800} textAnchor="middle">
                    {p.label}
                  </text>
                ) : null}
              </g>
            );
          })}
        </svg>
      </RevealUp>
      <ChipRow chips={body.chips} delay={START + 72} />
    </CenterStack>
  );
};

// ---------------------------------------------------------------------------
const SummaryBody: React.FC<{ body: Extract<Body, { type: "summary" }> }> = ({ body }) => {
  const l = useLayout();
  // On a vertical frame each row stacks its label over its value, so four rows
  // plus a closing line no longer fit at full size: tighten them instead of
  // letting the stack grow into the heading and the captions.
  const dense = l.vertical && body.rows.length + (body.closing ? 1 : 0) >= 4;
  return (
  <CenterStack gap={dense ? 16 : 26} top={l.vertical ? l.stackTop : 300} bottom={l.vertical ? l.stackBottom : 240}>
    {body.rows.map((r, i) => {
      const c = color(r.color);
      return (
        <RevealUp key={i} delay={START + i * 16} distance={18}>
          <div
            style={{
              display: "flex",
              alignItems: "center",
              gap: l.vertical ? (dense ? 6 : 12) : 34,
              padding: l.vertical ? (dense ? "12px 28px" : "16px 30px") : "16px 36px",
              borderRadius: 20,
              background: `${c}12`,
              border: `2px solid ${c}55`,
              minWidth: l.summaryWidth,
              ...(l.vertical ? { maxWidth: l.summaryWidth, boxSizing: "border-box" as const, flexDirection: "column" as const } : {}),
              fontFamily: FONT_UI,
            }}
          >
            <span style={{ fontSize: dense ? 26 : 30, fontWeight: 800, color: c, minWidth: l.vertical ? 0 : l.summaryLabelWidth }}>{r.label}</span>
            {r.tex ? (
              <Tex tex={r.tex} display={false} size={l.vertical ? (dense ? 32 : 38) : 44} color={COLORS.ink} />
            ) : (
              <span style={{ fontSize: dense ? 30 : 36, fontWeight: 700, color: COLORS.ink }}>{r.text}</span>
            )}
          </div>
        </RevealUp>
      );
    })}
    {body.closing ? (
      <RevealUp delay={START + body.rows.length * 16 + 10} distance={14} style={{ marginTop: dense ? 2 : 10 }}>
        <span style={{ fontFamily: FONT_UI, fontSize: dense ? 32 : 38, fontWeight: 700, color: COLORS.tealSoft, textAlign: "center", ...(l.vertical ? { display: "block", maxWidth: l.summaryWidth } : {}) }}>
          {body.closing}
        </span>
      </RevealUp>
    ) : null}
  </CenterStack>
  );
};

// ---------------------------------------------------------------------------
// Closing call to action. Ends a reel: one headline, the formula that was
// solved, and where the full solution lives.
const CtaBody: React.FC<{ body: Extract<Body, { type: "cta" }> }> = ({ body }) => {
  const l = useLayout();
  return (
    <CenterStack top={l.vertical ? 300 : 240} bottom={l.vertical ? 900 : 240} gap={l.vertical ? 34 : 40}>
      <RevealUp delay={2} distance={18}>
        <div
          style={{
            fontFamily: FONT_UI,
            fontSize: l.vertical ? 72 : 84,
            fontWeight: 900,
            color: COLORS.ink,
            textAlign: "center",
            maxWidth: l.vertical ? l.contentWidth : 1400,
            lineHeight: 1.05,
            letterSpacing: -1,
            whiteSpace: "pre-line",
          }}
        >
          <Highlighted text={body.headline} />
        </div>
      </RevealUp>
      {body.tex ? (
        <RevealUp delay={14} distance={18}>
          <div
            style={{
              padding: l.vertical ? "16px 30px" : "18px 44px",
              borderRadius: 26,
              background: COLORS.panel,
              border: `1.5px solid ${COLORS.panelStroke}`,
              boxShadow: `0 0 60px ${COLORS.solved}33`,
              // This panel is the only box on a CTA that grows with its content, so
              // it is the only one that can reach the button column. Keep it inside
              // the box; a formula too long for it spills out of the panel, where
              // the still review sees it.
              maxWidth: l.vertical ? l.contentWidth : 1400,
              boxSizing: "border-box",
            }}
          >
            <Tex tex={body.tex} size={l.vertical ? 58 : 76} color={COLORS.solved} />
          </div>
        </RevealUp>
      ) : null}
      {(body.lines ?? []).map((line, i) => (
        <RevealUp key={i} delay={26 + i * 12} distance={14}>
          <div
            style={{
              fontFamily: FONT_UI,
              fontSize: i === 0 ? (l.vertical ? 46 : 52) : l.vertical ? 36 : 40,
              fontWeight: 800,
              color: i === 0 ? COLORS.tealSoft : COLORS.inkSoft,
              textAlign: "center",
              maxWidth: l.contentWidth,
              lineHeight: 1.2,
            }}
          >
            {line}
          </div>
        </RevealUp>
      ))}
    </CenterStack>
  );
};

// ---------------------------------------------------------------------------
export const SceneBody: React.FC<{ body: Body }> = ({ body }) => {
  switch (body.type) {
    case "hook":
      return <HookBody body={body} />;
    case "rows":
      return <RowsBody body={body} />;
    case "dots":
      return <DotsBody body={body} />;
    case "normal":
      return <NormalBody body={body} />;
    case "table":
      return <TableBody body={body} />;
    case "plot":
      return <PlotBody body={body} />;
    case "summary":
      return <SummaryBody body={body} />;
    case "cta":
      return <CtaBody body={body} />;
    default:
      return null;
  }
};
