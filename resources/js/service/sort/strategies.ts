import type { VideoResource } from '@/types/resources';

export const CompareStrategies = {
    stringInsensitive: (a: any, b: any) => normalizeString(a).localeCompare(normalizeString(b)),

    date: (a: any, b: any) => new Date(a).getTime() - new Date(b).getTime(),

    number: (a: any, b: any) => parseFloat(a) - parseFloat(b),

    episode: (a: VideoResource, b: VideoResource) => {
        const seasonA = parseFloat(`${a.season ?? 0}`);
        const seasonB = parseFloat(`${b.season ?? 0}`);

        if (seasonA !== seasonB) return seasonA - seasonB;

        const episodeA = parseFloat(`${a.episode ?? 0}`);
        const episodeB = parseFloat(`${b.episode ?? 0}`);

        return episodeA - episodeB;
    },
};

function normalizeString(value: any): string {
    return String(value ?? '')
        .toLowerCase()
        .replace(/\s+/g, ' ')
        .trim();
}
