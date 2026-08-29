export declare type SortDir = 1 | -1;

export interface SortKey<T> {
    key?: keyof T;
    compareFn?: (a: T, b: T) => number;
    nullsLast?: boolean;
}

export interface SortCriteria<T> {
    column: keyof T;
    dir: SortDir;
}
