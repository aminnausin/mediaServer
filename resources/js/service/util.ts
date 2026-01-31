import type { AxiosError } from 'axios';

export function toTitleCase(str: string) {
    return str?.toLowerCase().replace(/(?:^|\s)\w/g, function (match) {
        return match.toUpperCase();
    });
}

export function toTimeSpan(rawDate: Date | string, timeZoneName = ' EST', short?: boolean) {
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

    let timeSpan: string;

    const units = { weeks: short ? 'w' : ' week', days: short ? 'd' : ' day', hours: short ? 'h' : ' hour' };

    const handlePlural = (val: number) => {
        return short ? '' : toPlural(val);
    };

    if (weeks > 0) {
        timeSpan = `${weeks}${units.weeks}${handlePlural(weeks)}`;
    } else if (days > 0) {
        timeSpan = `${days}${units.days}${handlePlural(days)}`;
    } else if (hours > 0) {
        timeSpan = `${hours}${units.hours}${handlePlural(hours)}`;
    } else if (minutes > 0) {
        timeSpan = `${minutes}m`;
    } else {
        timeSpan = `${Math.max(1, seconds)}s`;
    }

    if (!short) timeSpan += ' ago';
    return timeSpan;
}

export function toFormattedDate(
    rawDate?: Date | string,
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
    if (!rawDate) return 'Unknown';
    if (typeof rawDate === 'string') {
        rawDate = new Date(rawDate);
    }

    const result = rawDate
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

export function toFormattedDuration(rawSeconds: number = 0, leadingZero: boolean = true, format: 'digital' | 'analog' | 'verbose' = 'analog', rounded = false) {
    if (isNaN(Number(rawSeconds))) return null;

    const hours = Math.floor(rawSeconds / 3600);
    const minutes = Math.floor((rawSeconds % 3600) / 60);
    const seconds = Math.floor(rawSeconds % 60);

    const hoursText = format === 'verbose' ? ` hour${toPlural(hours)}` : 'h';
    const minutesText = format === 'verbose' ? ` minute${toPlural(minutes)}` : 'm';
    const secondsText = format === 'verbose' ? ` second${toPlural(seconds)}` : 's';

    if (format === 'digital') {
        const parts = [hours > 0 ? formatInteger(hours) : null, formatInteger(minutes), formatInteger(seconds)].filter(Boolean);
        return parts.join(':');
    }

    const parts: string[] = [];

    if (hours > 0) {
        parts.push(`${hours}${hoursText}`);
    }

    if (minutes > 0 || hours > 0) {
        parts.push(`${minutes}${minutesText}`);
    }

    const finalSeconds = leadingZero ? formatInteger(seconds) : `${seconds}`;
    parts.push(`${finalSeconds}${secondsText}`);

    return rounded && parts.length > 0 ? parts[0] : parts.join(' ');
}

export function formatInteger(integer: number, minimumDigits = 2) {
    return integer.toLocaleString('en-CA', { minimumIntegerDigits: minimumDigits });
}

export function toCalendarFormattedDate(date?: string, format: Intl.DateTimeFormatOptions = { month: 'long', day: '2-digit', year: 'numeric' }) {
    if (!date) return null;

    const rawDate = new Date(date);
    return rawDate.toLocaleDateString('en-CA', format).replaceAll('.', '');
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
export function formatFileSize(size: number, space = true, divisor: number = 1024): string {
    if (Number.isNaN(size) || size < 0) {
        return 'Invalid size';
    }

    let unitIndex = 0;
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];

    while (size >= divisor && unitIndex < units.length - 1) {
        size /= divisor;
        unitIndex++;
    }

    // 2 decimal places
    const formattedSize = Math.round(size * 100) / 100;
    return `${formattedSize}${space ? ' ' : ''}${units[unitIndex]}`;
}

export function formatBitrate(rate: number, space = true): string {
    if (Number.isNaN(rate) || rate < 0) {
        return 'Invalid rate';
    }

    return formatFileSize(rate, space, 1000).toLocaleLowerCase() + 'ps';
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

export function isAxiosError(error: unknown): error is AxiosError {
    return (error as AxiosError).isAxiosError === true;
}

export function toPlural(value: number): string {
    return value != 1 ? 's' : '';
}

export function getClientX(event: TouchEvent | MouseEvent): number {
    return 'touches' in event ? event.touches[0]?.clientX : event.clientX;
}
