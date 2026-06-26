import type { HoursSummaryItem } from '@/composables/useHoursSummary';

export type HistoryView = 'list' | 'calendar';

export type HistoryDaySummary = {
    date: string;
    workedMinutes: number;
    extraMinutes: number;
    missingMinutes: number;
};

export type HistoryCalendarDay = {
    date: string;
    dayOfMonth: number;
    isCurrentMonth: boolean;
    isToday: boolean;
    summary: HistoryDaySummary | null;
};

export function buildHistoryDaySummaries(
    items: HoursSummaryItem[],
): HistoryDaySummary[] {
    return items
        .filter((item) => item.worked_minutes > 0)
        .map((item) => ({
            date: item.date,
            workedMinutes: item.worked_minutes,
            extraMinutes: item.extra_minutes,
            missingMinutes: item.missing_minutes,
        }))
        .sort((left, right) => right.date.localeCompare(left.date));
}

export function buildHistoryCalendarDays(
    monthStart: string,
    items: HoursSummaryItem[],
    todayDate: string,
): HistoryCalendarDay[] {
    const monthDate = createUtcDate(monthStart);
    const startOffset = (monthDate.getUTCDay() + 6) % 7;
    const gridStart = new Date(
        Date.UTC(
            monthDate.getUTCFullYear(),
            monthDate.getUTCMonth(),
            monthDate.getUTCDate() - startOffset,
        ),
    );
    const summaryMap = new Map(
        buildHistoryDaySummaries(items).map((item) => [item.date, item]),
    );

    return Array.from({ length: 42 }, (_, index) => {
        const date = new Date(
            Date.UTC(
                gridStart.getUTCFullYear(),
                gridStart.getUTCMonth(),
                gridStart.getUTCDate() + index,
            ),
        );
        const year = date.getUTCFullYear();
        const month = date.getUTCMonth() + 1;
        const day = date.getUTCDate();
        const formattedDate = `${year}-${padDateUnit(month)}-${padDateUnit(day)}`;

        return {
            date: formattedDate,
            dayOfMonth: day,
            isCurrentMonth: month === monthDate.getUTCMonth() + 1,
            isToday: formattedDate === todayDate,
            summary: summaryMap.get(formattedDate) ?? null,
        };
    });
}

export function buildWeekdayLabels(locale: string): string[] {
    const monday = new Date(Date.UTC(2026, 5, 1));

    return Array.from({ length: 7 }, (_, index) => new Intl.DateTimeFormat(locale, {
        weekday: 'short',
        timeZone: 'UTC',
    }).format(new Date(Date.UTC(
        monday.getUTCFullYear(),
        monday.getUTCMonth(),
        monday.getUTCDate() + index,
    ))));
}

export function formatHistoryMonthHeading(date: string, locale: string): string {
    const { month, year } = parseLocalDate(date);

    const label = new Intl.DateTimeFormat(locale, {
        month: 'long',
        year: 'numeric',
        timeZone: 'UTC',
    }).format(new Date(Date.UTC(year, month - 1, 1)));

    return capitalize(label);
}

export function formatHistoryDayHeading(date: string, locale: string): string {
    return new Intl.DateTimeFormat(locale, {
        weekday: 'long',
        day: 'numeric',
        timeZone: 'UTC',
    }).format(createUtcDate(date));
}

export function formatHistoryDaySubheading(date: string, locale: string): string {
    return capitalize(new Intl.DateTimeFormat(locale, {
        month: 'long',
        year: 'numeric',
        timeZone: 'UTC',
    }).format(createUtcDate(date)));
}

export function shiftMonthStart(date: string, amount: number): string {
    const { month, year } = parseLocalDate(date);
    const shiftedDate = new Date(Date.UTC(year, month - 1 + amount, 1));

    return `${shiftedDate.getUTCFullYear()}-${padDateUnit(shiftedDate.getUTCMonth() + 1)}-01`;
}

export function getCurrentMonthStart(): string {
    const now = new Date();

    return `${now.getFullYear()}-${padDateUnit(now.getMonth() + 1)}-01`;
}

function createUtcDate(date: string): Date {
    const { day, month, year } = parseLocalDate(date);

    return new Date(Date.UTC(year, month - 1, day));
}

function parseLocalDate(date: string): { day: number; month: number; year: number } {
    const [year, month, day] = date.split('-').map(Number);

    if (! year || ! month || ! day) {
        throw new Error(`Invalid local date: ${date}`);
    }

    return { day, month, year };
}

function padDateUnit(value: number): string {
    return String(value).padStart(2, '0');
}

function capitalize(value: string): string {
    return value.charAt(0).toLocaleUpperCase() + value.slice(1);
}
