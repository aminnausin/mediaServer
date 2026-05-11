export const COMMON_VIDEO_EXTENSIONS = ['mp4', 'mkv', 'avi', 'mov', 'webm', 'm4v', 'ts'];
export const COMMON_AUDIO_EXTENSIONS = ['mp3', 'flac', 'aac', 'ogg', 'wav', 'm4a', 'opus'];
export const COMMON_SUBTITLE_EXTENSIONS = ['srt', 'ass', 'vtt', 'sub'];

export const COMMON_EXTENSIONS = new Set([...COMMON_AUDIO_EXTENSIONS, ...COMMON_VIDEO_EXTENSIONS, ...COMMON_SUBTITLE_EXTENSIONS]);
