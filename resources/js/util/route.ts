import type { LocationQueryValue } from 'vue-router';

export function toParamString(param: LocationQueryValue | LocationQueryValue[] | undefined): string | undefined {
    if (param == null) return undefined;
    return Array.isArray(param) ? (param[0] ?? undefined) : param;
}

export function toParamNumber(param: LocationQueryValue | LocationQueryValue[] | undefined): number | undefined {
    const asString = toParamString(param);
    if (!asString) return undefined;
    const n = Number(asString);
    return Number.isFinite(n) ? n : undefined;
}
