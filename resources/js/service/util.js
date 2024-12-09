export function toTitleCase(str) {
    return str.toLowerCase().replace(/(?:^|\s)\w/g, function (match) {
        return match.toUpperCase();
    });
}

export function toTimeSpan(rawDate) {
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

export function toFormattedDate(rawDate) {
    return rawDate
        .toLocaleString([], { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: true })
        .toLocaleUpperCase()
        .replaceAll('.', '');
}

export function toFormattedDuration(rawSeconds) {
    if (isNaN(parseInt(rawSeconds))) return null;
    const hours = Math.floor(rawSeconds / 3600);
    const minutes = Math.floor((rawSeconds % 3600) / 60);
    const seconds = Math.floor(rawSeconds % 60);

    const duration = `${hours > 0 ? `${hours}h ` : ''}${minutes > 0 ? `${minutes}m ` : ''}${`${formatInteger(seconds)}s`}`;

    return duration;
}

export function formatInteger(integer, minimumDigits = 2) {
    return integer.toLocaleString('en-CA', { minimumIntegerDigits: minimumDigits });
}

export function toCalendarFormattedDate(date) {
    let rawDate = new Date(date + ' EST');

    return rawDate.toLocaleDateString('en-CA', { month: 'long', day: '2-digit', year: 'numeric' }).replaceAll('.', '');
}
