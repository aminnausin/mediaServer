import type { VideoResource } from '@/types/resources';

export const CompareStrategies: { [key: string]: (a: any, b: any) => number } = {
    stringInsensitive: (a: any, b: any) => {
        const strA = normalizeString(a);
        const strB = normalizeString(b);
        const result = strA.localeCompare(strB, 'en', { sensitivity: 'base' });
        if (result !== 0) return result;
        // tiebreaker
        if (strA < strB) return -1;
        return strA > strB ? 1 : 0;
    },

    date: (a: any, b: any) => {
        return parseDate(a) - parseDate(b);
    },

    number: (a: any, b: any) => {
        const numA = Number.parseFloat(a);
        const numB = Number.parseFloat(b);
        if (Number.isNaN(numA) && Number.isNaN(numB)) return 0;
        if (Number.isNaN(numA)) return 1;
        if (Number.isNaN(numB)) return -1;
        return numA - numB;
    },

    episode: (a: VideoResource, b: VideoResource) => {
        const seasonA = a.season ?? 0;
        const seasonB = b.season ?? 0;

        if (seasonA !== seasonB) return seasonA - seasonB;

        const episodeA = a.episode ?? 0;
        const episodeB = b.episode ?? 0;

        return episodeA - episodeB;
    },
};

function parseDate(value: any) {
    const time = new Date(value).getTime();
    return Number.isNaN(time) ? Infinity : time;
}

function normalizeString(value: any): string {
    return String(value ?? '')
        .toLowerCase()
        .replace(/\s+/g, ' ')
        .trim();
}
