function padDateTimeUnit(value: number): string {
    return String(value).padStart(2, '0');
}

export function getCurrentClientTimezone(): string {
    const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

    return typeof timezone === 'string' && timezone.length > 0
        ? timezone
        : 'UTC';
}

export function getCurrentClientDateTimeAtom(now: Date = new Date()): string {
    const year = now.getFullYear();
    const month = padDateTimeUnit(now.getMonth() + 1);
    const day = padDateTimeUnit(now.getDate());
    const hours = padDateTimeUnit(now.getHours());
    const minutes = padDateTimeUnit(now.getMinutes());
    const seconds = padDateTimeUnit(now.getSeconds());
    const offsetMinutes = -now.getTimezoneOffset();
    const sign = offsetMinutes >= 0 ? '+' : '-';
    const absoluteOffsetMinutes = Math.abs(offsetMinutes);
    const offsetHours = padDateTimeUnit(Math.floor(absoluteOffsetMinutes / 60));
    const offsetRemainderMinutes = padDateTimeUnit(absoluteOffsetMinutes % 60);

    return `${year}-${month}-${day}T${hours}:${minutes}:${seconds}${sign}${offsetHours}:${offsetRemainderMinutes}`;
}
