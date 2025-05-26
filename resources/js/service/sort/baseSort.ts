import type { SortDir } from '@/types/types';

import { CompareStrategies } from '@/service/sort/strategies';

export function sortObject<T>(column: keyof T, direction: SortDir = 1, dateColumns: string[] = ['date', 'date_released']) {
    return (a: T, b: T): number => {
        let valueA = a[column];
        let valueB = b[column];

        if ((valueA instanceof Date && valueB instanceof Date) || dateColumns.includes(String(column))) {
            let dateA = new Date(String(valueA));
            let dateB = new Date(String(valueB));
            return (dateB.getTime() - dateA.getTime()) * direction;
        }

        let numA = parseFloat(valueA as any);
        let numB = parseFloat(valueB as any);

        if (!isNaN(numA) && !isNaN(numB)) {
            return (numA - numB) * direction;
        }
        return String(valueA).toLowerCase().replace(/\s+/g, ' ').localeCompare(String(valueB).toLowerCase().replace(/\s+/g, ' ')) * direction;
    };
}

export interface SortKey<T> {
    key?: keyof T;
    compareFn?: (a: T, b: T) => number;
}

export function sortObjectNew<T>(keys: SortKey<T>[], direction: SortDir = 1) {
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
        return 0;
    };
}

function defaultCompare(a: any, b: any): number {
    const numA = parseFloat(a);
    const numB = parseFloat(b);

    if (!isNaN(numA) && !isNaN(numB)) {
        return numA - numB;
    }

    const dateA = new Date(a);
    const dateB = new Date(b);
    if (!isNaN(dateA.getTime()) && !isNaN(dateB.getTime())) {
        return dateA.getTime() - dateB.getTime();
    }

    return CompareStrategies.stringInsensitive(a, b);
}
