import React from "react";
import { AbsoluteFill, Audio, Sequence, staticFile, useCurrentFrame } from "remotion";
import { Background, TopBar, Heading, Footer, Captions } from "./ui";
import { SceneBody } from "./bodies";
import { buildTimeline, totalFrames } from "./timeline";
import { COLORS, color } from "./theme";
import type { VideoProps, TimedScene } from "./types";

const DEFAULT_PHASE_COLORS: Record<string, string> = {
  "Idea clave": "tealSoft",
  Planteamiento: "teal",
  "Método": "teal",
  "Resumen final": "tealSoft",
};

const SceneView: React.FC<{ scene: TimedScene; props: VideoProps; total: number }> = ({ scene, props, total }) => {
  const accentName = props.phaseColors?.[scene.phase] ?? DEFAULT_PHASE_COLORS[scene.phase] ?? "tealSoft";
  const isHook = scene.body.type === "hook";
  return (
    <AbsoluteFill>
      {isHook ? null : <Heading phase={scene.phase} heading={scene.heading} accent={color(accentName, COLORS.tealSoft)} />}
      <SceneBody body={scene.body} />
      {isHook ? null : <Footer index={scene.index} total={total} question={props.exam.question} topic={props.exam.topic} />}
      <Captions captions={scene.captions} narrationFrames={scene.narrationFrames} starts={scene.captionStarts} />
    </AbsoluteFill>
  );
};

export const ExamVideo: React.FC<VideoProps> = (props) => {
  const frame = useCurrentFrame();
  const timeline = buildTimeline(props);
  const total = totalFrames(timeline);
  return (
    <AbsoluteFill style={{ backgroundColor: COLORS.bg0 }}>
      <Background />
      {timeline.map((scene) => (
        <Sequence key={scene.id} from={scene.from} durationInFrames={scene.frames} name={scene.id}>
          {scene.hasAudio ? <Audio src={staticFile(`exercises/${props.slug}/audio/${scene.id}.mp3`)} /> : null}
          <SceneView scene={scene} props={props} total={timeline.length} />
        </Sequence>
      ))}
      <TopBar progress={Math.min(1, frame / total)} examLabel={props.exam.label} site={props.exam.site ?? "ClasesDeApoyo.com"} />
    </AbsoluteFill>
  );
};
