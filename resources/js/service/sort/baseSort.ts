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
        for (const { key, compareFn, nullsLast } of keys) {
            // Sort null values to end of list

            const nullCheck = nullCompare(a, b, key, nullsLast);
            if (nullCheck !== undefined) return nullCheck;

            const valueA = parseKeyedValue(a, key);
            const valueB = parseKeyedValue(b, key);

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
    const numA = typeof a === 'number' || (typeof a === 'string' && a.trim() !== '' && !Number.isNaN(Number(a))) ? Number(a) : Number.NaN;
    const numB = typeof b === 'number' || (typeof b === 'string' && b.trim() !== '' && !Number.isNaN(Number(b))) ? Number(b) : Number.NaN;

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

function nullCompare<T>(a: T, b: T, key?: keyof T, nullsLast?: boolean): number | undefined {
    if (key && nullsLast) {
        const aNull = a[key] == null;
        const bNull = b[key] == null;

        if (aNull && !bNull) return 1;
        if (!aNull && bNull) return -1;
        if (aNull && bNull) return 0;
    }
}

function parseKeyedValue<T>(obj: T, key?: keyof T, defaultValue: unknown = '') {
    return key ? (obj[key] ?? defaultValue) : undefined;
}
