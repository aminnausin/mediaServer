export function toTitleCase(str: string) {
    return str.toLowerCase().replace(/(?:^|\s)\w/g, function (match) {
        return match.toUpperCase();
    });
}

export function toTimeSpan(rawDate: Date | string) {
    if (!rawDate) return '';
    if (typeof rawDate === 'string') {
        rawDate = new Date(rawDate + ' EST');
    }

    const rawAge = Date.now() - rawDate.getTime();

    const weeks = Math.round(rawAge / (1000 * 3600 * 24 * 7));
    const days = Math.round(rawAge / (1000 * 3600 * 24));
    const hours = Math.round(rawAge / (1000 * 3600));
    const minutes = Math.round(rawAge / (1000 * 60));
    const seconds = Math.round(rawAge / 1000);

    const timeSpan =
        weeks > 0
            ? `${weeks} week${weeks > 1 ? 's' : ''} ago`
            : days > 0
              ? `${days} day${days > 1 ? 's' : ''} ago`
              : hours > 0
                ? `${hours} hour${hours > 1 ? 's' : ''} ago`
                : minutes > 0
                  ? `${minutes}m ago`
                  : `${seconds}s ago`;

    return timeSpan;
}

export function toFormattedDate(rawDate: Date) {
    return rawDate
        .toLocaleString([], { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: true })
        .toLocaleUpperCase()
        .replaceAll('.', '');
}

export function toFormattedDuration(rawSeconds: number = 0) {
    if (isNaN(parseInt(rawSeconds.toString()))) return null;
    const hours = Math.floor(rawSeconds / 3600);
    const minutes = Math.floor((rawSeconds % 3600) / 60);
    const seconds = Math.floor(rawSeconds % 60);

    const duration = `${hours > 0 ? `${hours}h ` : ''}${minutes > 0 ? `${minutes}m ` : ''}${`${formatInteger(seconds)}s`}`;

    return duration;
}

export function formatInteger(integer: number, minimumDigits = 2) {
    return integer.toLocaleString('en-CA', { minimumIntegerDigits: minimumDigits });
}

export function toCalendarFormattedDate(date: string) {
    let rawDate = new Date(date + ' EST');

    return rawDate.toLocaleDateString('en-CA', { month: 'long', day: '2-digit', year: 'numeric' }).replaceAll('.', '');
}

export function pulseFormatDate(value: string = '') {
    if (value.match(/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/) === null) {
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

export function periodForHumans(period: string) {
    if (period === '1_hour') return 'hour';
    return period.replace('_', ' ');
}
