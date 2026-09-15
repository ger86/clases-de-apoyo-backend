// Layout numbers for the two frame shapes the tool renders.
//
// Horizontal (1920x1080) is the YouTube explainer. Vertical (1080x1920) is the
// reel for Instagram and TikTok, where both apps draw their own interface on
// top of the frame: a column of buttons on the right and the post text at the
// bottom. The vertical numbers keep every piece of content inside a safe box
// and push the captions up into the middle third.
//
// The horizontal values are literal copies of what the components used before
// this file existed, so the 16:9 videos render byte for byte the same.

import { useVideoConfig } from "remotion";

export type Layout = {
  width: number;
  height: number;
  vertical: boolean;

  // Content box. Everything readable stays inside it.
  contentLeft: number;
  contentRight: number;
  contentWidth: number;

  // Top bar.
  barHeight: number;
  barPadding: number;
  barStacked: boolean;
  barBrandSize: number;
  barLabelSize: number;

  // Phase chip + heading.
  headingTop: number;
  headingChipSize: number;
  headingSize: number;
  headingSizeLong: number;

  // Body stack.
  stackTop: number;
  stackBottom: number;
  hookStackTop: number;
  hookStackBottom: number;
  graphStackTop: number;

  // Per-body widths.
  graphWidth: number;
  plotWidth: number;
  tableWidth: number;
  tableLabelWidth: number;
  summaryWidth: number;
  summaryLabelWidth: number;

  // Type scale for rows and hooks.
  hookTitleSize: number;
  hookKickerSize: number;
  rowSizeScale: number;

  // Captions.
  captionTop?: number; // vertical: pinned into the middle third
  captionHeight: number;
  captionSize: number;

  // Footer. Hidden on vertical: the apps cover that area anyway.
  showFooter: boolean;
  footerBottom: number;
  footerSize: number;
};

const HORIZONTAL: Omit<Layout, "width" | "height" | "vertical"> = {
  contentLeft: 0,
  contentRight: 0,
  contentWidth: 1400,

  barHeight: 92,
  barPadding: 64,
  barStacked: false,
  barBrandSize: 32,
  barLabelSize: 27,

  headingTop: 150,
  headingChipSize: 23,
  headingSize: 60,
  headingSizeLong: 52,

  stackTop: 300,
  stackBottom: 250,
  hookStackTop: 210,
  hookStackBottom: 210,
  graphStackTop: 290,

  graphWidth: 1300,
  plotWidth: 1180,
  tableWidth: 1480,
  tableLabelWidth: 150,
  summaryWidth: 1100,
  summaryLabelWidth: 420,

  hookTitleSize: 70,
  hookKickerSize: 26,
  rowSizeScale: 1,

  captionHeight: 132,
  captionSize: 34,

  showFooter: true,
  footerBottom: 150,
  footerSize: 24,
};

// 1080x1920. Right gutter of 170px keeps formulas clear of the app's button
// column; the captions sit at 58-72% of the height, above the post text.
//
// Every vertical width is derived from V_BOX, never typed by hand, so nothing
// can be drawn outside the safe box. Bodies that draw past their declared width
// (the graphs extend their axes beyond it) get that overhang subtracted here.
const V_LEFT = 50;
const V_RIGHT = 170;
const V_BOX = 1080 - V_LEFT - V_RIGHT;
const V_TABLE_LABEL = 120;

const VERTICAL: Omit<Layout, "width" | "height" | "vertical"> = {
  contentLeft: V_LEFT,
  contentRight: V_RIGHT,
  contentWidth: V_BOX,

  barHeight: 170,
  barPadding: 50,
  barStacked: true,
  barBrandSize: 34,
  barLabelSize: 26,

  headingTop: 225,
  headingChipSize: 21,
  headingSize: 54,
  headingSizeLong: 45,

  stackTop: 360,
  stackBottom: 880,
  hookStackTop: 300,
  hookStackBottom: 960,
  graphStackTop: 400,

  graphWidth: V_BOX - 2 * 20, // NormalBody draws its axis 20px past each side
  plotWidth: V_BOX - 2 * 10, // PlotBody draws its axes 10px past each side
  tableWidth: V_BOX - V_TABLE_LABEL, // width for the cells; the label column is extra
  tableLabelWidth: V_TABLE_LABEL,
  summaryWidth: V_BOX,
  summaryLabelWidth: 300,

  hookTitleSize: 66,
  hookKickerSize: 24,
  rowSizeScale: 1,

  captionTop: 1060,
  captionHeight: 260,
  captionSize: 42,

  showFooter: false,
  footerBottom: 430,
  footerSize: 26,
};

export const layoutFor = (width: number, height: number): Layout => {
  const vertical = height > width;
  return { width, height, vertical, ...(vertical ? VERTICAL : HORIZONTAL) };
};

export const useLayout = (): Layout => {
  const { width, height } = useVideoConfig();
  return layoutFor(width, height);
};
