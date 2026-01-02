const controlsHideTime: number = 2500; // Time before controls auto hide
const playbackDataBuffer: number = 5; // Number of seeks needed to upload
const playerHealthBuffer: number = 12; // Number of player yime updates needed to fetch info
const volumeDelta: number = 0.05; // Volume change rate
const playbackDelta: number = 0.05; // Speed change rate
const playbackMin: number = 0.1; // Min speed
const playbackMax: number = 3; // Max speed

export { controlsHideTime, playbackDataBuffer, playerHealthBuffer, volumeDelta, playbackDelta, playbackMin, playbackMax };
