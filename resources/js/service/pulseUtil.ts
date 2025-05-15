export function getMaxReading(readings: { [key: string]: { [key: string]: string | null } }, sampleRate: number = 10): number {
    // Flatten the nested object structure and convert values to numbers
    // const flattenedValues = Object.values(readings).flatMap((reading) => Object.values(reading));
    // const numberValues = flattenedValues.map((value) => (value !== null && !isNaN(Number(value)) ? Number(value) : -Infinity));

    return (
        Math.max(
            ...Object.values(readings).map((dataset) => Math.max(...Object.values(dataset).map((value) => (value !== null && !isNaN(Number(value)) ? Number(value) : -Infinity)))),
        ) *
        (1 / sampleRate)
    );
}

/**
 * LARAVEL PULSE FUNCTION:
 *
 * @param value
 * @returns
 */
export function pulseFormatDate(value: string = '') {
    if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.exec(value) === null) {
        throw new Error(`Unknown date format [${value}].`);
    }

    const [date, time] = value.split(' ');
    const [year, month, day] = date.split('-').map(Number);
    const [hour, minute, second] = time.split(':').map(Number);

    return new Date(Date.UTC(year, month - 1, day, hour, minute, second, 0)).toLocaleString(undefined, {
        year: '2-digit',
        day: '2-digit',
        month: '2-digit',
        hourCycle: 'h24',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZoneName: 'short',
    });
}

/**
 * LARAVEL PULSE FUNCTION:
 *
 * @param period
 * @returns
 */
export function periodForHumans(period: string) {
    if (period === '1_hour') return 'hour';
    return period.replace('_', ' ');
}

/**
 * LARAVEL PULSE FUNCTION:
 * @param value
 * @returns
 */
export function format_number(value: any) {
    return Intl.NumberFormat().format(value);
}
