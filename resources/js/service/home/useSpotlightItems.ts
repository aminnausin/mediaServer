import type { FolderResource } from '@/contracts/media';

export type SpotlightItem = { folder: FolderResource; label: string };

export function interleaveSpotlightItems(groups: { items: FolderResource[]; label: string }[], perGroup = 4): SpotlightItem[] {
    const seen = new Set<number>();

    const queues = groups.map(({ items, label }) => {
        const windowed = items.slice(0, perGroup);
        const deduped = windowed.filter((f) => (seen.has(f.id) ? false : (seen.add(f.id), true)));
        return { label, items: deduped };
    });

    const result: SpotlightItem[] = [];
    const maxLen = Math.max(...queues.map((q) => q.items.length));

    for (let i = 0; i < maxLen; i++) {
        for (const q of queues) if (q.items[i]) result.push({ folder: q.items[i], label: q.label });
    }

    return result;
}
