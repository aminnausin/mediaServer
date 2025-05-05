import type { SortDir } from '@/types/types';
import type { AxiosError } from 'axios';

export function toTitleCase(str: string) {
    return str?.toLowerCase().replace(/(?:^|\s)\w/g, function (match) {
        return match.toUpperCase();
    });
}

export function toTimeSpan(rawDate: Date | string, timeZoneName = ' EST') {
    if (!rawDate) return '';
    if (typeof rawDate === 'string') {
        rawDate = new Date(rawDate + timeZoneName);
    }
    const rawAge = Date.now() - rawDate.getTime();

    const weeks = Math.floor(rawAge / (1000 * 3600 * 24 * 7));
    const days = Math.floor(rawAge / (1000 * 3600 * 24));
    const hours = Math.floor(rawAge / (1000 * 3600));
    const minutes = Math.floor(rawAge / (1000 * 60));
    const seconds = Math.floor(rawAge / 1000);

    const timeSpan =
        weeks > 0
            ? `${weeks} week${toPlural(weeks)} ago`
            : days > 0
              ? `${days} day${toPlural(days)} ago`
              : hours > 0
                ? `${hours} hour${toPlural(hours)} ago`
                : minutes > 0
                  ? `${minutes}m ago`
                  : `${Math.max(1, seconds)}s ago`;

    return timeSpan;
}

export function toFormattedDate(
    rawDate: Date,
    toUpperCase: boolean = true,
    format: Intl.DateTimeFormatOptions = {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    },
) {
    let result = rawDate
        .toLocaleString(
            ['en-CA'],
            format ?? {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true,
            },
        )
        .replaceAll('.', '');
    return toUpperCase ? result.toLocaleUpperCase() : result;
}

export function toFormattedDuration(rawSeconds: number = 0, leadingZero: boolean = true, format: 'digital' | 'analog' | 'verbose' = 'analog') {
    if (isNaN(parseInt(rawSeconds?.toString() ?? '0'))) return null;

    const hours = Math.floor(rawSeconds / 3600);
    const minutes = Math.floor((rawSeconds % 3600) / 60);
    const seconds = Math.floor(rawSeconds % 60);

    const hoursText = format === 'verbose' ? ` hour${toPlural(hours)}` : 'h';
    const minutesText = format === 'verbose' ? ` minute${toPlural(minutes)}` : 'm';
    const secondsText = format === 'verbose' ? ` second${toPlural(seconds)}` : 's';

    if (format === 'digital') {
        return `${hours > 0 ? `${formatInteger(hours)}:` : ''}${formatInteger(minutes)}:${formatInteger(seconds)}`;
    }
    return `${hours > 0 ? `${hours}${hoursText} ` : ''}${minutes > 0 ? `${minutes}${minutesText} ` : ''}${`${leadingZero ? formatInteger(seconds) : `${seconds}`}${secondsText}`}`;
}

export function formatInteger(integer: number, minimumDigits = 2) {
    return integer.toLocaleString('en-CA', { minimumIntegerDigits: minimumDigits });
}

export function toCalendarFormattedDate(date: string) {
    let rawDate = new Date(date + ' EST');

    return rawDate.toLocaleDateString('en-CA', { month: 'long', day: '2-digit', year: 'numeric' }).replaceAll('.', '');
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

/**
 * Get the current screen size in tailwind notation.
 *
 * Example: If the current screen width is greater than 1024px, return 'lg'.
 * @returns {'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'default'} the Tailwind CSS compatible screen size identifier.
 */
export function getScreenSize(): 'sm' | 'md' | 'lg' | 'xl' | '2xl' | 'default' {
    const width = window.innerWidth;

    if (width >= 1536) return '2xl';
    if (width >= 1280) return 'xl';
    if (width >= 1024) return 'lg';
    if (width >= 768) return 'md';
    if (width >= 640) return 'sm';
    return 'default';
}

export function isMobileDevice(): boolean {
    return /Mobi|Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}

/**
 * Converts a numerical file size in bytes to a human readable format in the largest applicable unit.
 *
 * Example: "2566" returns 2.51 KB.
 * @param size Size of file in bytes.
 * @param space Include a space between the numberical value and the unit ? Example: 126MB vs 126 MB.
 * @returns {string} `${formattedSize} ${unit}`.
 */
export function formatFileSize(size: number, space = true): string {
    if (isNaN(size) || size < 0) {
        return 'Invalid size';
    }

    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let unitIndex = 0;

    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
    }

    // 2 decimal places
    const formattedSize = Math.round(size * 100) / 100;
    return `${formattedSize}${space ? ' ' : ''}${units[unitIndex]}`;
}

/**
 * Checks if a date string is within 24 hours of the current time.
 *
 * @param date Raw date string.
 */
export function within24Hrs(date: string): boolean {
    const now = new Date();
    const then = new Date(date);
    const diffInHours = (now.getTime() - then.getTime()) / (1000 * 60 * 60);

    return diffInHours < 24;
}

/**
 * Converts storage URLs to the correct protocol based on the current URL.
 *
 * Example: "http://website.ca/storage/file.mp4" when the current URL is "https://website.ca" returns "https://website.ca/storage/file.mp4".
 * @param url The storage URL string.
 * @returns The storage URL string with the correct protocol.
 */
export function handleStorageURL(url: string | undefined): string | null {
    if (!url) return null;

    if (window.location.protocol === 'http:' && url.startsWith(`https://${window.location.host}`)) return url.replace('https:', 'http:');

    if (window.location.protocol === 'https:' && url.startsWith(`http://${window.location.host}`)) return url.replace('http:', 'https:');
    return url;
}

export function isInputLikeElement(element: EventTarget | null, key: string): boolean {
    if (!element) return false;

    let inputLikeTags = ['INPUT', 'TEXTAREA', 'SELECT'];

    if (key === ' ' || key === 'Enter') inputLikeTags = [...inputLikeTags, 'BUTTON'];

    return inputLikeTags.includes((element as HTMLElement).tagName);
}

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

export function isAxiosError(error: unknown): error is AxiosError {
    return (error as AxiosError).isAxiosError === true;
}

export function toPlural(value: number): string {
    return value != 1 ? 's' : '';
}
