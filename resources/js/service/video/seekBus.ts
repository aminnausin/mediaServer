const subscribers = new Set<(seconds: number) => void>();

export function onSeek(callback: (seconds: number) => void) {
    subscribers.add(callback);
    return () => subscribers.delete(callback); // unsubscribe
}

export function emitSeek(seconds: number) {
    for (const callback of subscribers) {
        callback(seconds);
    }
}
