import type { SortDir, SortKey } from '@/service/sort/types';

import { CompareStrategies } from '@/service/sort/strategies';

export function sortObject<T>(column: keyof T, direction: SortDir = 1, dateColumns: string[] = ['updated_at', 'released_at']) {
    return (a: T, b: T): number => {
        const valueA = a[column];
        const valueB = b[column];

        if ((valueA instanceof Date && valueB instanceof Date) || dateColumns.includes(String(column))) {
            const dateA = new Date(String(valueA));
            const dateB = new Date(String(valueB));
            return (dateB.getTime() - dateA.getTime()) * direction;
        }

        const numA = Number.parseFloat(valueA as any);
        const numB = Number.parseFloat(valueB as any);

        if (!Number.isNaN(numA) && !Number.isNaN(numB)) {
            return (numA - numB) * direction;
        }
        return String(valueA).toLowerCase().replace(/\s+/g, ' ').localeCompare(String(valueB).toLowerCase().replace(/\s+/g, ' ')) * direction;
    };
}

export function sortObjectNew<T extends { id?: any }>(keys: SortKey<T>[], direction: SortDir = 1) {
    return (a: T, b: T): number => {
        // Loops through keys and returns the first non 0 sort result (so same episode number will be skipped and move on to comparing seasons)
        for (const { key, compareFn } of keys) {
            const valueA = key ? (a[key] ?? '') : undefined;
            const valueB = key ? (b[key] ?? '') : undefined;

            let result: number;

            if (!compareFn) {
                result = defaultCompare(valueA, valueB);
            } else {
                result = key ? (compareFn as (a: any, b: any) => number)(valueA, valueB) : (compareFn as (a: T, b: T) => number)(a, b);
            }

            if (result !== 0) return result * direction;
        }
        return (a.id ?? 0) > (b.id ?? 0) ? 1 : -1;
    };
}

function defaultCompare(a: any, b: any): number {
    const numA = Number.parseFloat(a);
    const numB = Number.parseFloat(b);

    if (!Number.isNaN(numA) && !Number.isNaN(numB)) {
        return numA - numB;
    }

    const dateA = new Date(a);
    const dateB = new Date(b);
    if (!Number.isNaN(dateA.getTime()) && !Number.isNaN(dateB.getTime())) {
        return dateA.getTime() - dateB.getTime();
    }

    return CompareStrategies.stringInsensitive(a, b);
}
