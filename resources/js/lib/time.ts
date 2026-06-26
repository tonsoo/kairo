function padTimeUnit(value: number): string {
    return String(value).padStart(2, '0');
}

export function formatDurationMinutes(
    minutes: number,
    options: { signed?: boolean; suffix?: boolean } = {},
): string {
    const { signed = false, suffix = false } = options;
    const normalizedMinutes = Math.max(0, Math.round(Math.abs(minutes)));
    const sign = signed && minutes < 0 ? '-' : '';
    const hours = Math.floor(normalizedMinutes / 60);
    const remainder = normalizedMinutes % 60;
    const formatted = `${sign}${padTimeUnit(hours)}:${padTimeUnit(remainder)}`;

    return suffix ? `${formatted}h` : formatted;
}
