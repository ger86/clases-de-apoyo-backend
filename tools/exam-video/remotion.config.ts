import { Config } from "@remotion/cli/config";

Config.setEntryPoint("./src/index.ts");
Config.setVideoImageFormat("jpeg");
Config.setOverwriteOutput(true);
Config.setConcurrency(4);
// Crisp text rendering for math-heavy frames.
Config.setChromiumOpenGlRenderer("angle");
