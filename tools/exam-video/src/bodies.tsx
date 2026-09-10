import React from "react";
import { useCurrentFrame, interpolate } from "remotion";
import { COLORS, FONT_UI, color } from "./theme";
import { Tex, RevealUp, usePop } from "./math";
import { ResultBadge, ChipRow } from "./ui";
import type { Body, Row } from "./types";

// Reveal schedule: first element appears at START, then STEP frames per element.
const START = 6;
const STEP = 12;

const CenterStack: React.FC<{ top?: number; bottom?: number; gap?: number; children: React.ReactNode }> = ({
  top = 300,
  bottom = 250,
  gap = 36,
  children,
}) => (
  <div
    style={{
      position: "absolute",
      top,
      bottom,
      left: 0,
      right: 0,
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

// One content row: optional label + tex or text.
const RowView: React.FC<{ row: Row; delay: number; defaultSize?: number }> = ({ row, delay, defaultSize = 56 }) => {
  const c = color(row.color, COLORS.ink);
  const size = row.size ?? defaultSize;
  return (
    <RevealUp delay={delay} distance={18}>
      <div style={{ display: "flex", alignItems: "center", gap: 28, fontFamily: FONT_UI }}>
        {row.label ? (
          <span
            style={{
              fontSize: 22,
              fontWeight: 800,
              letterSpacing: 2,
              textTransform: "uppercase",
              color: COLORS.muted,
              minWidth: 150,
              textAlign: "right",
            }}
          >
            {row.label}
          </span>
        ) : null}
        {row.tex ? (
          <Tex tex={row.tex} display={false} size={size} color={c} />
        ) : (
          <span style={{ fontSize: size * 0.8, fontWeight: 700, color: c, maxWidth: 1400, textAlign: "center" }}>
            {row.text}
          </span>
        )}
      </div>
    </RevealUp>
  );
};

// *highlight* markup in hook titles.
const Highlighted: React.FC<{ text: string }> = ({ text }) => (
  <>
    {text.split(/(\*[^*]+\*)/g).map((part, i) =>
      part.startsWith("*") && part.endsWith("*") ? (
        <span key={i} style={{ color: COLORS.teal }}>
          {part.slice(1, -1)}
        </span>
      ) : (
        <React.Fragment key={i}>{part}</React.Fragment>
      ),
    )}
  </>
);

// ---------------------------------------------------------------------------
const HookBody: React.FC<{ body: Extract<Body, { type: "hook" }> }> = ({ body }) => (
  <div style={{ position: "absolute", inset: 0, fontFamily: FONT_UI }}>
    <CenterStack top={210} bottom={210} gap={44}>
      {body.kicker ? (
        <RevealUp delay={2} distance={12}>
          <span style={{ fontSize: 26, fontWeight: 800, letterSpacing: 3, color: COLORS.tealSoft, textTransform: "uppercase" }}>
            {body.kicker}
          </span>
        </RevealUp>
      ) : null}
      <RevealUp delay={6} distance={20}>
        <div
          style={{
            fontSize: 70,
            fontWeight: 800,
            color: COLORS.ink,
            textAlign: "center",
            maxWidth: 1500,
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
              padding: "18px 48px",
              borderRadius: 26,
              background: COLORS.panel,
              border: `1.5px solid ${COLORS.panelStroke}`,
              boxShadow: `0 0 60px ${color(body.texColor, COLORS.teal)}33`,
            }}
          >
            <Tex tex={body.tex} size={100} color={color(body.texColor, COLORS.teal)} />
          </div>
        </RevealUp>
      ) : null}
      <ChipRow chips={body.chips} delay={34} />
    </CenterStack>
  </div>
);

// ---------------------------------------------------------------------------
const RowsBody: React.FC<{ body: Extract<Body, { type: "rows" }> }> = ({ body }) => {
  const n = body.rows.length;
  // Density: rows plus the space a badge / chip row takes. Dense scenes shrink so nothing touches the heading.
  const tall = body.rows.filter((r) => r.tex?.includes("\\begin{cases}")).length;
  const density = n + tall * 2 + (body.badge ? 2 : 0) + (body.chips?.length ? 1 : 0);
  const scale = density >= 6 ? 0.8 : density >= 5 ? 0.88 : 1;
  const defaultSize = (n >= 5 ? 44 : n === 4 ? 50 : 58) * scale;
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
  const frame = useCurrentFrame();
  const W = 1300;
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
    <CenterStack gap={22} top={290}>
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
  const cols = body.columns.length;
  const cellW = Math.min(200, Math.floor(1480 / cols));
  const cellStyle: React.CSSProperties = {
    width: cellW,
    height: 86,
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
            gridTemplateColumns: `150px repeat(${cols}, ${cellW}px)`,
            borderRadius: 22,
            overflow: "hidden",
            background: COLORS.panel,
            border: `1.5px solid ${COLORS.panelStroke}`,
            fontFamily: FONT_UI,
          }}
        >
          <div style={{ ...cellStyle, width: 150, borderLeft: "none", borderBottom: `1px solid ${COLORS.panelStroke}` }} />
          {body.columns.map((c, i) => (
            <div key={i} style={{ ...cellStyle, borderBottom: `1px solid ${COLORS.panelStroke}` }}>
              <RevealUp delay={START + 10 + i * 6} distance={8}>
                <Tex tex={c} display={false} size={26} color={COLORS.inkSoft} />
              </RevealUp>
            </div>
          ))}
          {body.rows.map((row, r) => (
            <React.Fragment key={r}>
              <div style={{ ...cellStyle, width: 150, borderLeft: "none", borderTop: r > 0 ? `1px solid ${COLORS.panelStroke}` : "none" }}>
                <Tex tex={row.label} display={false} size={34} color={COLORS.ink} />
              </div>
              {row.cells.map((cell, i) => {
                const c = color(cell.color, COLORS.ink);
                return (
                  <div key={i} style={{ ...cellStyle, borderTop: r > 0 ? `1px solid ${COLORS.panelStroke}` : "none" }}>
                    <RevealUp delay={START + 24 + r * 8 + i * 9} distance={10}>
                      {cell.tex ? (
                        <Tex tex={cell.tex} display={false} size={34} color={c} />
                      ) : (
                        <span style={{ fontSize: 36, fontWeight: 800, color: c }}>{cell.text}</span>
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
  const frame = useCurrentFrame();
  const W = 1180;
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
    <CenterStack gap={22} top={290}>
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
const SummaryBody: React.FC<{ body: Extract<Body, { type: "summary" }> }> = ({ body }) => (
  <CenterStack gap={26} top={300} bottom={240}>
    {body.rows.map((r, i) => {
      const c = color(r.color);
      return (
        <RevealUp key={i} delay={START + i * 16} distance={18}>
          <div
            style={{
              display: "flex",
              alignItems: "center",
              gap: 34,
              padding: "16px 36px",
              borderRadius: 20,
              background: `${c}12`,
              border: `2px solid ${c}55`,
              minWidth: 1100,
              fontFamily: FONT_UI,
            }}
          >
            <span style={{ fontSize: 30, fontWeight: 800, color: c, minWidth: 420 }}>{r.label}</span>
            {r.tex ? (
              <Tex tex={r.tex} display={false} size={44} color={COLORS.ink} />
            ) : (
              <span style={{ fontSize: 36, fontWeight: 700, color: COLORS.ink }}>{r.text}</span>
            )}
          </div>
        </RevealUp>
      );
    })}
    {body.closing ? (
      <RevealUp delay={START + body.rows.length * 16 + 10} distance={14} style={{ marginTop: 10 }}>
        <span style={{ fontFamily: FONT_UI, fontSize: 38, fontWeight: 700, color: COLORS.tealSoft, textAlign: "center" }}>
          {body.closing}
        </span>
      </RevealUp>
    ) : null}
  </CenterStack>
);

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
    default:
      return null;
  }
};
