import type { FolderResource } from '@/contracts/media';

import { CompareStrategies } from '@/service/sort/strategies';
import { sortObjectNew } from '@/service/sort/baseSort';

export type SpotlightItem = { id: number; folder: FolderResource; label: string; subtitle?: string; timestamp: Date };

export function interleaveSpotlightItems(
    groups: { items: FolderResource[]; label: string; buildSubtitle?: (folder: FolderResource) => string | undefined; dateField: keyof FolderResource }[],
    perGroup = 4,
): SpotlightItem[] {
    const seen = new Set<number>();

    const queues = groups.map(({ items, label, buildSubtitle, dateField }) => {
        const windowed = items.slice(0, perGroup);
        const deduped = windowed.filter((f) => (seen.has(f.id) ? false : (seen.add(f.id), true)));
        return { label, items: deduped, buildSubtitle, dateField };
    });

    const result: SpotlightItem[] = [];
    const maxLen = Math.max(...queues.map((q) => q.items.length));

    for (let i = 0; i < maxLen; i++) {
        for (const q of queues)
            if (q.items[i])
                result.push({
                    id: q.items[i].id,
                    folder: q.items[i],
                    label: q.label,
                    subtitle: q.buildSubtitle?.(q.items[i]),
                    timestamp: new Date(q.items[i][q.dateField] as string),
                });
    }

    return result.sort(sortObjectNew([{ key: 'timestamp', compareFn: CompareStrategies.date }], -1));
}
