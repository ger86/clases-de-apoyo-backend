import React from "react";
import { Composition } from "remotion";
import { ExamVideo } from "./Video";
import { Thumbnail } from "./Thumbnail";
import { buildTimeline, totalFrames } from "./timeline";
import { FPS, WIDTH, HEIGHT } from "./theme";
import type { VideoProps } from "./types";

// Placeholder shown in the studio when no --props file is given.
const DEMO: VideoProps = {
  slug: "demo",
  exam: { label: "PAU · Matemáticas", question: "Pregunta 0", topic: "Demo" },
  voice: { provider: "elevenlabs", model: "eleven_v3", voiceId: "KDuMsTRG03d18osdZP8V" },
  scenes: [
    {
      id: "01-hook",
      phase: "Idea clave",
      heading: "Demo",
      narration: "Este es un vídeo de demostración. Pasa un fichero de props para ver un ejercicio real.",
      body: { type: "hook", kicker: "exam-video", title: "Pasa *--props* con un ejercicio real", tex: "\\int_a^b f(x)\\,dx" },
    },
  ],
  timing: { scenes: [] },
};

export const RemotionRoot: React.FC = () => (
  <>
  <Composition
    id="Thumbnail"
    component={Thumbnail}
    defaultProps={DEMO}
    fps={FPS}
    width={1280}
    height={720}
    durationInFrames={1}
  />
  <Composition
    id="ExamVideo"
    component={ExamVideo}
    defaultProps={DEMO}
    fps={FPS}
    width={WIDTH}
    height={HEIGHT}
    durationInFrames={totalFrames(buildTimeline(DEMO))}
    calculateMetadata={({ props }) => ({
      durationInFrames: totalFrames(buildTimeline(props)),
    })}
  />
  </>
);
