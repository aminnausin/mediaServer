export interface FfprobeStream {
    index: number;
    codec_type: 'video' | 'audio' | 'subtitle' | 'attachment';
    codec_name?: string;
    profile?: string;
    level?: number;
    width?: number;
    height?: number;
    sample_aspect_ratio?: string;
    display_aspect_ratio?: string;
    field_order?: string;
    r_frame_rate?: string;
    avg_frame_rate?: string;
    bit_rate?: string;
    bits_per_raw_sample?: string;
    pix_fmt?: string;
    color_space?: string;
    color_transfer?: string;
    color_primaries?: string;
    refs?: number;
    nal_length_size?: string;
    is_avc?: string;
    channels?: number;
    channel_layout?: string;
    sample_rate?: string;
    disposition?: Record<string, number>;
    tags?: Record<string, string>;
}

export interface StreamField {
    label: string;
    value?: string | number;
}

export interface StreamCard {
    key: string;
    type: 'video' | 'audio' | 'subtitle';
    title: string;
    fields: StreamField[];
}

const parseFraction = (fraction?: string): number => {
    if (!fraction) return 0;
    const [n, d] = fraction.split('/').map(Number);
    return d ? n / d : n;
};

const formatBitrate = (bps?: string | number): string => {
    const n = Number(bps);
    return n ? `${Math.round(n / 1000)} kbps` : '—';
};

const formatFlag = (flag?: string | number): string => (flag && flag !== '0' ? 'Yes' : 'No');

const videoRange = (stream: FfprobeStream): { range: string; type?: string } => {
    if (stream.color_transfer === 'smpte2084') return { range: 'HDR', type: 'HDR10' };
    if (stream.color_transfer === 'arib-std-b67') return { range: 'HDR', type: 'HLG' };
    return { range: 'SDR' };
};

function buildVideoCard(s: FfprobeStream): StreamCard {
    const { range, type } = videoRange(s);
    const fps = parseFraction(s.avg_frame_rate ?? s.r_frame_rate);

    return {
        key: `stream-${s.index}`,
        type: 'video',
        title: `${s.index}. Video`,
        fields: [
            { label: 'Title', value: s.tags?.title },
            { label: 'Codec', value: s.codec_name?.toUpperCase() },
            { label: 'Profile', value: s.profile },
            { label: 'Resolution', value: `${s.width}x${s.height}` },
            { label: 'Aspect ratio', value: s.display_aspect_ratio },
            { label: 'Framerate', value: fps ? Math.round(fps).toString() : '—' },
            { label: 'Bitrate', value: formatBitrate(s.bit_rate ?? s.tags?.BPS) },
            { label: 'Bit depth', value: s.bits_per_raw_sample ? `${s.bits_per_raw_sample} bit` : '—' },
            { label: 'Video range', value: range },
            ...(type ? [{ label: 'Video range type', value: type }] : []),
            { label: 'Colour space', value: s.color_space },
            { label: 'Pixel format', value: s.pix_fmt },
        ],
    };
}

function buildAudioCard(s: FfprobeStream): StreamCard {
    return {
        key: `stream-${s.index}`,
        type: 'audio',
        title: `${s.index}. Audio`,
        fields: [
            { label: 'Title', value: s.tags?.title },
            { label: 'Language', value: s.tags?.language ?? 'und' },
            { label: 'Codec', value: s.codec_name?.toUpperCase() },
            { label: 'Layout', value: s.channel_layout },
            { label: 'Channels', value: s.channels },
            { label: 'Bitrate', value: formatBitrate(s.bit_rate ?? s.tags?.BPS) },
            { label: 'Sample rate', value: s.sample_rate ? `${s.sample_rate} Hz` : '—' },
            { label: 'Default', value: formatFlag(s.disposition?.default) },
            { label: 'Forced', value: formatFlag(s.disposition?.forced) },
        ],
    };
}

function buildSubtitleCard(s: FfprobeStream): StreamCard {
    return {
        key: `stream-${s.index}`,
        type: 'subtitle',
        title: `${s.index} Subtitle`,
        fields: [
            { label: 'Title', value: s.tags?.title },
            { label: 'Language', value: s.tags?.language ?? 'und' },
            { label: 'Codec', value: s.codec_name?.toUpperCase() },
            { label: 'Default', value: formatFlag(s.disposition?.default) },
            { label: 'Forced', value: formatFlag(s.disposition?.forced) },
        ],
    };
}

export function buildStreamCards(streams: FfprobeStream[], types: FfprobeStream['codec_type'][] = ['video', 'audio']): StreamCard[] {
    return streams
        .filter((s) => types.includes(s.codec_type))
        .map((s) => {
            switch (s.codec_type) {
                case 'video':
                    return buildVideoCard(s);
                case 'audio':
                    return buildAudioCard(s);
                case 'subtitle':
                    return buildSubtitleCard(s);
                default:
                    return undefined;
            }
        })
        .filter((s): s is StreamCard => s !== undefined);
}
