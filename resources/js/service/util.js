export function toTitleCase(str) {
    return str.toLowerCase().replace(/(?:^|\s)\w/g, function(match) {
        return match.toUpperCase();
    });
}

export function toTimeSpan(rawDate) {
    const rawAge = Date.now() - rawDate.getTime();
        
    const weeks = Math.round(rawAge / (1000 * 3600 * 24 * 7));
    const days = Math.round(rawAge / (1000 * 3600 * 24));
    const hours = Math.round(rawAge / (1000 * 3600));
    const minutes = Math.round(rawAge / (1000 * 60));
    const seconds = Math.round(rawAge / (1000));
        
    const timeSpan = weeks > 0 ? `${weeks} week${weeks > 1 ? 's' : ''} ago` : days > 0 ? `${days} day${days > 1 ? 's' : ''} ago` : hours > 0 ? `${hours} hour${hours > 1 ? 's' : ''} ago` : minutes > 0 ? `${minutes}m ago` : `${seconds}s ago`

    return timeSpan;
}

export function toFormattedDate(rawDate) {
    return (rawDate.toLocaleString([], {year: 'numeric', month: '2-digit', day:'2-digit', hour:'2-digit', minute:'2-digit', hour12: true})).toLocaleUpperCase().replaceAll('.','');
}

export function formatInteger(integer, minimumDigits = 2) {
    return integer.toLocaleString('en-CA', { minimumIntegerDigits: minimumDigits})
}