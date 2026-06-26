import { getDashboardLocale } from '@/lib/dashboardTranslations';

export type WorkScheduleType = 'day_off' | 'total_time' | 'time_range';

export type WorkScheduleApiData = {
    id: number;
    weekday: number;
    type: WorkScheduleType;
    expected_minutes: number;
    starts_at: string | null;
    ends_at: string | null;
    effective_from: string;
};

export type WeeklyScheduleFormRow = {
    weekday: number;
    type: WorkScheduleType;
    expected_time: string;
    starts_at: string;
    ends_at: string;
};

const weekdays = [1, 2, 3, 4, 5, 6, 7] as const;
const mondayReference = new Date(Date.UTC(2026, 0, 5));

export function resolveEffectiveFrom(timeZone: string): string {
    const formatter = new Intl.DateTimeFormat('en-CA', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        timeZone,
    });
    const parts = formatter.formatToParts(new Date());

    return `${resolvePart(parts, 'year')}-${resolvePart(parts, 'month')}-${resolvePart(parts, 'day')}`;
}

export function buildWeeklyScheduleRows(
    schedules: WorkScheduleApiData[],
    effectiveFrom: string,
): WeeklyScheduleFormRow[] {
    const activeSchedules = resolveActiveSchedules(schedules, effectiveFrom);

    return weekdays.map((weekday) => {
        const schedule = activeSchedules.get(weekday);

        if (schedule === undefined) {
            return {
                weekday,
                type: isWeekendWeekday(weekday) ? 'day_off' : 'total_time',
                expected_time: '',
                starts_at: '',
                ends_at: '',
            };
        }

        return {
            weekday,
            type: schedule.type,
            expected_time: schedule.type === 'total_time'
                ? minutesToDuration(schedule.expected_minutes)
                : '',
            starts_at: schedule.starts_at ?? '',
            ends_at: schedule.ends_at ?? '',
        };
    });
}

export function normalizeWeeklyScheduleRows(rows: WeeklyScheduleFormRow[]): Array<{
    weekday: number;
    type: WorkScheduleType;
    expected_minutes?: number | null;
    starts_at?: string | null;
    ends_at?: string | null;
}> {
    return rows.map((row) => {
        if (row.type === 'day_off') {
            return {
                weekday: row.weekday,
                type: row.type,
            };
        }

        if (row.type === 'time_range') {
            return {
                weekday: row.weekday,
                type: row.type,
                starts_at: row.starts_at,
                ends_at: row.ends_at,
            };
        }

        return {
            weekday: row.weekday,
            type: row.type,
            expected_minutes: durationToMinutes(row.expected_time),
        };
    });
}

export function isWeekendWeekday(weekday: number): boolean {
    return weekday === 6 || weekday === 7;
}

export function weekdayLabel(
    weekday: number,
    locale: string = getDashboardLocale(),
): string {
    const referenceDate = new Date(mondayReference);

    referenceDate.setUTCDate(mondayReference.getUTCDate() + (weekday - 1));

    const formatted = new Intl.DateTimeFormat(locale, {
        weekday: 'long',
        timeZone: 'UTC',
    }).format(referenceDate);

    return formatted.charAt(0).toLocaleUpperCase(locale) + formatted.slice(1);
}

function resolveActiveSchedules(
    schedules: WorkScheduleApiData[],
    effectiveFrom: string,
): Map<number, WorkScheduleApiData> {
    const activeSchedules = new Map<number, WorkScheduleApiData>();

    schedules
        .filter((schedule) => weekdays.includes(schedule.weekday as (typeof weekdays)[number]))
        .sort((left, right) => right.effective_from.localeCompare(left.effective_from))
        .forEach((schedule) => {
            if (schedule.effective_from > effectiveFrom || activeSchedules.has(schedule.weekday)) {
                return;
            }

            activeSchedules.set(schedule.weekday, schedule);
        });

    return activeSchedules;
}

function resolvePart(
    parts: Intl.DateTimeFormatPart[],
    type: Intl.DateTimeFormatPartTypes,
): string {
    const part = parts.find((item) => item.type === type);

    if (part === undefined) {
        throw new Error(`Missing ${type} part for date formatting.`);
    }

    return part.value;
}

function minutesToDuration(minutes: number): string {
    const hours = Math.floor(minutes / 60);
    const remainder = minutes % 60;

    return `${String(hours).padStart(2, '0')}:${String(remainder).padStart(2, '0')}`;
}

function durationToMinutes(value: string): number | null {
    const match = value.match(/^(\d{2}):(\d{2})$/);

    if (match === null) {
        return null;
    }

    const hours = Number(match[1]);
    const minutes = Number(match[2]);

    if (! Number.isInteger(hours) || ! Number.isInteger(minutes)) {
        return null;
    }

    return (hours * 60) + minutes;
}
