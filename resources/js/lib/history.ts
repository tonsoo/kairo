import type { HoursSummaryItem } from '@/composables/useHoursSummary';
import type { WorkScheduleType } from '@/lib/weeklySchedule';

export type HistoryView = 'list' | 'calendar';

export type HistoryDaySummary = {
    date: string;
    hasSchedule: boolean;
    workedMinutes: number;
    expectedMinutes: number;
    extraMinutes: number;
    missingMinutes: number;
};

export type HistoryMonthDay = {
    date: string;
    dayOfMonth: number;
    isCurrentMonth: boolean;
    isToday: boolean;
    isFuture: boolean;
    summary: HistoryDaySummary | null;
};

export type HistoryCalendarDay = HistoryMonthDay;

export type HistoryDayEditorTab = 'daily-schedule' | 'shifts';

export type DailyWorkScheduleApiData = {
    id: number;
    date: string;
    weekday: number;
    type: WorkScheduleType;
    expected_minutes: number;
    starts_at: string | null;
    ends_at: string | null;
};

export type HistoryDailyScheduleDraft = {
    enabled: boolean;
    type: WorkScheduleType;
    expectedTime: string;
    startsAt: string;
    endsAt: string;
};

export type HistoryShiftDraft = {
    id: number | null;
    key: string;
    started_at: string;
    ended_at: string;
};

export function buildHistoryDaySummaries(
    items: HoursSummaryItem[],
): HistoryDaySummary[] {
    return items
        .filter((item) => item.worked_minutes > 0 || item.missing_minutes > 0)
        .map(buildHistoryDaySummary)
        .sort((left, right) => right.date.localeCompare(left.date));
}

export function buildHistoryMonthDays(
    monthStart: string,
    items: HoursSummaryItem[],
    todayDate: string,
): HistoryMonthDay[] {
    const monthDate = createUtcDate(monthStart);
    const daysInMonth = new Date(
        Date.UTC(monthDate.getUTCFullYear(), monthDate.getUTCMonth() + 1, 0),
    ).getUTCDate();
    const summaryMap = new Map(
        items.map((item) => [item.date, buildHistoryDaySummary(item)]),
    );

    return Array.from({ length: daysInMonth }, (_, index) => {
        const date = new Date(
            Date.UTC(
                monthDate.getUTCFullYear(),
                monthDate.getUTCMonth(),
                index + 1,
            ),
        );
        const year = date.getUTCFullYear();
        const month = date.getUTCMonth() + 1;
        const day = date.getUTCDate();
        const formattedDate = `${year}-${padDateUnit(month)}-${padDateUnit(day)}`;

        return {
            date: formattedDate,
            dayOfMonth: day,
            isCurrentMonth: true,
            isToday: formattedDate === todayDate,
            isFuture: formattedDate > todayDate,
            summary: summaryMap.get(formattedDate) ?? null,
        };
    })
        .filter((day) => !day.isFuture)
        .sort((left, right) => right.date.localeCompare(left.date));
}

export function buildHistoryCalendarDays(
    monthStart: string,
    items: HoursSummaryItem[],
    todayDate: string,
): HistoryCalendarDay[] {
    const monthDate = createUtcDate(monthStart);
    const startOffset = (monthDate.getUTCDay() + 6) % 7;
    const daysInMonth = new Date(
        Date.UTC(monthDate.getUTCFullYear(), monthDate.getUTCMonth() + 1, 0),
    ).getUTCDate();
    const totalCells = Math.ceil((startOffset + daysInMonth) / 7) * 7;
    const gridStart = new Date(
        Date.UTC(
            monthDate.getUTCFullYear(),
            monthDate.getUTCMonth(),
            monthDate.getUTCDate() - startOffset,
        ),
    );
    const summaryMap = new Map(
        items.map((item) => [item.date, buildHistoryDaySummary(item)]),
    );

    return Array.from({ length: totalCells }, (_, index) => {
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
        const isCurrentMonth = month === monthDate.getUTCMonth() + 1;
        const isFuture = formattedDate > todayDate;

        return {
            date: formattedDate,
            dayOfMonth: day,
            isCurrentMonth,
            isToday: formattedDate === todayDate,
            isFuture,
            summary: isFuture ? null : (summaryMap.get(formattedDate) ?? null),
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

function buildHistoryDaySummary(item: HoursSummaryItem): HistoryDaySummary {
    return {
        date: item.date,
        hasSchedule: item.has_schedule,
        workedMinutes: item.worked_minutes,
        expectedMinutes: item.expected_minutes,
        extraMinutes: item.extra_minutes,
        missingMinutes: item.missing_minutes,
    };
}

function createUtcDate(date: string): Date {
    const { day, month, year } = parseLocalDate(date);

    return new Date(Date.UTC(year, month - 1, day));
}

function parseLocalDate(date: string): { day: number; month: number; year: number } {
    const [year, month, day] = date.split('-').map(Number);

    if (!year || !month || !day) {
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
