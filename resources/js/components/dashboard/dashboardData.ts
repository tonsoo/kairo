import type {
    HoursSummaryApiData,
    HoursSummaryItem,
} from '@/composables/useHoursSummary';
import type { ShiftInRange } from '@/composables/useShiftsInRange';

export type DashboardLegendKey =
    | 'worked'
    | 'extra'
    | 'missing'
    | 'positive'
    | 'negative'
    | 'online'
    | 'paused';

export type DashboardBalanceStatus = 'positive' | 'negative' | 'zero';

export type DashboardLegendItem = {
    labelKey: string;
    colorClass: string;
};

export type DashboardMeterSegment = DashboardLegendItem & {
    value: number;
};

export type DashboardBarItem = {
    label: string;
    workedMinutes: number;
    extraMinutes: number;
    missingMinutes: number;
    scheduledMinutes: number;
    actualMinutes: number;
    totalMinutes: number;
};

export type DashboardJourneySegment = {
    startHour: number;
    endHour: number;
    colorClass: string;
};

export type DashboardJourneyItem = {
    label: string;
    segments: DashboardJourneySegment[];
};

type LocalDateTimeParts = {
    date: string;
    hour: number;
    secondOfDay: number;
};

type DayShiftInterval = {
    startSecond: number;
    endSecond: number;
};

const dashboardLegendConfig = {
    worked: {
        labelKey: 'dashboard.hours.legend.worked',
        colorClass: 'bg-teal-500',
    },
    extra: {
        labelKey: 'dashboard.hours.legend.extra',
        colorClass: 'bg-rose-500',
    },
    missing: {
        labelKey: 'dashboard.hours.legend.missing',
        colorClass: 'bg-slate-500/70',
    },
    positive: {
        labelKey: 'dashboard.hours.legend.positive',
        colorClass: 'bg-teal-500',
    },
    negative: {
        labelKey: 'dashboard.hours.legend.negative',
        colorClass: 'bg-rose-500',
    },
    online: {
        labelKey: 'dashboard.hours.legend.online',
        colorClass: 'bg-teal-500',
    },
    paused: {
        labelKey: 'dashboard.hours.legend.paused',
        colorClass: 'bg-amber-500',
    },
} satisfies Record<DashboardLegendKey, DashboardLegendItem>;

export const chartLegend: DashboardLegendItem[] = [
    dashboardLegendConfig.worked,
    dashboardLegendConfig.extra,
    dashboardLegendConfig.missing,
];

export function buildBalanceSegments(
    balance: HoursSummaryApiData['balance'],
): DashboardMeterSegment[] {
    return [
        {
            ...dashboardLegendConfig.positive,
            value: balance.positive_minutes,
        },
        {
            ...dashboardLegendConfig.negative,
            value: balance.negative_minutes,
        },
    ];
}

export function buildTodaySegments(
    today: HoursSummaryApiData['today'],
    referenceDateTime: string,
    shifts: ShiftInRange[],
): DashboardMeterSegment[] {
    const reference = parseLocalIsoDateTime(referenceDateTime);
    const intervals = mergeDayShiftIntervals(
        shifts
            .map((shift) => toDayShiftInterval(today.date, shift, reference))
            .filter((interval): interval is DayShiftInterval => interval !== null),
    );
    const segments: DashboardMeterSegment[] = [];
    let previousEndSecond: number | null = null;

    for (const interval of intervals) {
        if (previousEndSecond !== null && interval.startSecond > previousEndSecond) {
            segments.push({
                ...dashboardLegendConfig.paused,
                value: (interval.startSecond - previousEndSecond) / 60,
            });
        }

        segments.push({
            ...dashboardLegendConfig.online,
            value: (interval.endSecond - interval.startSecond) / 60,
        });

        previousEndSecond = interval.endSecond;
    }

    const consumedMinutes = segments.reduce(
        (total, segment) => total + segment.value,
        0,
    );

    if (today.expected_minutes > 0) {
        const targetMinutes = Math.max(today.expected_minutes, consumedMinutes);
        const missingMinutes = targetMinutes - consumedMinutes;

        if (missingMinutes > 0) {
            segments.push({
                ...dashboardLegendConfig.missing,
                value: missingMinutes,
            });
        }

        if (segments.length === 0) {
            return [
                {
                    ...dashboardLegendConfig.missing,
                    value: targetMinutes,
                },
            ];
        }
    }

    return segments.filter((segment) => segment.value > 0);
}

export function buildLegendFromSegments(
    segments: DashboardMeterSegment[],
): DashboardLegendItem[] {
    const legendItems = new Map<string, DashboardLegendItem>();

    segments.forEach(({ labelKey, colorClass, value }) => {
        if (value <= 0 || legendItems.has(labelKey)) {
            return;
        }

        legendItems.set(labelKey, {
            labelKey,
            colorClass,
        });
    });

    return Array.from(legendItems.values());
}

export function buildSemesterChartItems(
    items: HoursSummaryItem[],
    locale: string,
): DashboardBarItem[] {
    return items.map((item) => ({
        label: formatShortMonthLabel(item.date, locale),
        workedMinutes: item.regular_minutes,
        extraMinutes: item.extra_minutes,
        missingMinutes: item.missing_minutes,
        scheduledMinutes: item.regular_minutes + item.missing_minutes,
        actualMinutes: item.regular_minutes + item.extra_minutes,
        totalMinutes: item.regular_minutes + item.extra_minutes + item.missing_minutes,
    }));
}

export function buildMonthChartItems(
    monthStartsAt: string,
    items: HoursSummaryItem[],
): DashboardBarItem[] {
    const { month, year } = parseLocalDate(monthStartsAt);
    const daysInMonth = new Date(Date.UTC(year, month, 0)).getUTCDate();
    const itemsByDay = new Map(
        items.map((item) => [parseLocalDate(item.date).day, item]),
    );

    return Array.from({ length: daysInMonth }, (_, index) => {
        const day = index + 1;
        const item = itemsByDay.get(day);
        const workedMinutes = item?.regular_minutes ?? 0;
        const extraMinutes = item?.extra_minutes ?? 0;
        const missingMinutes = item?.missing_minutes ?? 0;

        return {
            label: String(day),
            workedMinutes,
            extraMinutes,
            missingMinutes,
            scheduledMinutes: workedMinutes + missingMinutes,
            actualMinutes: workedMinutes + extraMinutes,
            totalMinutes: workedMinutes + extraMinutes + missingMinutes,
        };
    });
}

export function resolveChartMaxMinutes(
    items: DashboardBarItem[],
    fallbackMinutes: number = 0,
): number {
    return Math.max(
        fallbackMinutes,
        ...items.flatMap((item) => [
            item.scheduledMinutes,
            item.actualMinutes,
            item.totalMinutes,
        ]),
        60,
    );
}

export function buildMonthJourneyItems(
    monthStartsAt: string,
    referenceDateTime: string,
    shifts: ShiftInRange[],
): DashboardJourneyItem[] {
    const { month, year } = parseLocalDate(monthStartsAt);
    const daysInMonth = new Date(Date.UTC(year, month, 0)).getUTCDate();
    const items = Array.from({ length: daysInMonth }, (_, index) => ({
        label: String(index + 1),
        segments: [] as DashboardJourneySegment[],
    }));
    const reference = parseLocalIsoDateTime(referenceDateTime);

    shifts.forEach((shift) => {
        const startedAt = parseLocalIsoDateTime(shift.started_at);
        const endedAt = shift.ended_at === null
            ? reference
            : parseLocalIsoDateTime(shift.ended_at);

        for (
            let current = startedAt.date;
            current <= endedAt.date;
            current = addOneDay(current)
        ) {
            const currentDate = parseLocalDate(current);

            if (currentDate.year !== year || currentDate.month !== month) {
                continue;
            }

            const dayIndex = currentDate.day - 1;
            const startHour = current === startedAt.date ? startedAt.hour : 0;
            const endHour = current === endedAt.date ? endedAt.hour : 24;

            if (endHour <= startHour) {
                continue;
            }

            items[dayIndex]?.segments.push({
                startHour,
                endHour,
                colorClass: 'bg-teal-500/45',
            });
        }
    });

    items.forEach((item) => {
        item.segments.sort((left, right) => left.startHour - right.startHour);
    });

    return items;
}

export function formatDurationMinutes(
    minutes: number,
    options: { signed?: boolean } = {},
): string {
    const { signed = false } = options;
    const sign = signed && minutes < 0 ? '-' : '';
    const absoluteMinutes = Math.abs(minutes);
    const hours = Math.floor(absoluteMinutes / 60);
    const remainder = absoluteMinutes % 60;

    return `${sign}${padTimeUnit(hours)}:${padTimeUnit(remainder)}`;
}

export function formatPercentage(value: number, total: number): string {
    if (total <= 0) {
        return '0%';
    }

    return `${Math.round((value / total) * 100)}%`;
}

export function resolveBalanceStatus(
    balanceMinutes: number,
): DashboardBalanceStatus {
    if (balanceMinutes > 0) {
        return 'positive';
    }

    if (balanceMinutes < 0) {
        return 'negative';
    }

    return 'zero';
}

export function getBalanceStatusLabelKey(
    status: DashboardBalanceStatus,
): string {
    return `dashboard.hours.balance.status.${status}`;
}

export function formatMonthHeading(date: string, locale: string): string {
    const { month, year } = parseLocalDate(date);
    const monthName = new Intl.DateTimeFormat(locale, {
        month: 'long',
        timeZone: 'UTC',
    }).format(new Date(Date.UTC(year, month - 1, 1)));

    return `${capitalize(monthName, locale)}, ${year}`;
}

export function formatSemesterHeading(
    startsAt: string,
    endsAt: string,
    locale: string,
): string {
    const start = parseLocalDate(startsAt);
    const end = parseLocalDate(endsAt);
    const startLabel = new Intl.DateTimeFormat(locale, {
        month: 'short',
        timeZone: 'UTC',
    }).format(new Date(Date.UTC(start.year, start.month - 1, 1)));
    const endLabel = new Intl.DateTimeFormat(locale, {
        month: 'short',
        timeZone: 'UTC',
    }).format(new Date(Date.UTC(end.year, end.month - 1, 1)));

    if (start.year === end.year) {
        return `${capitalize(startLabel, locale)} - ${capitalize(endLabel, locale)} ${end.year}`;
    }

    return `${capitalize(startLabel, locale)} ${start.year} - ${capitalize(endLabel, locale)} ${end.year}`;
}

export function getMonthStartFromDateTime(value: string): string {
    const { date } = parseLocalIsoDateTime(value);
    const { month, year } = parseLocalDate(date);

    return `${year}-${padTimeUnit(month)}-01`;
}

export function getCurrentSemesterStart(value: string): string {
    return shiftMonthStart(getMonthStartFromDateTime(value), -5);
}

export function shiftMonthStart(date: string, offsetMonths: number): string {
    const { month, year } = parseLocalDate(date);
    const shifted = new Date(Date.UTC(year, month - 1 + offsetMonths, 1));

    return shifted.toISOString().slice(0, 10);
}

function formatShortMonthLabel(date: string, locale: string): string {
    const { month, year } = parseLocalDate(date);
    const monthName = new Intl.DateTimeFormat(locale, {
        month: 'short',
        timeZone: 'UTC',
    }).format(new Date(Date.UTC(year, month - 1, 1)));

    return `${monthName}/${String(year).slice(-2)}`;
}

function mergeDayShiftIntervals(intervals: DayShiftInterval[]): DayShiftInterval[] {
    if (intervals.length === 0) {
        return [];
    }

    const sortedIntervals = [...intervals].sort(
        (left, right) => left.startSecond - right.startSecond,
    );
    const mergedIntervals: DayShiftInterval[] = [sortedIntervals[0] as DayShiftInterval];

    for (const interval of sortedIntervals.slice(1)) {
        const lastInterval = mergedIntervals.at(-1);

        if (lastInterval === undefined) {
            mergedIntervals.push(interval);
            continue;
        }

        if (interval.startSecond <= lastInterval.endSecond) {
            lastInterval.endSecond = Math.max(lastInterval.endSecond, interval.endSecond);
            continue;
        }

        mergedIntervals.push({
            startSecond: interval.startSecond,
            endSecond: interval.endSecond,
        });
    }

    return mergedIntervals;
}

function toDayShiftInterval(
    todayDate: string,
    shift: ShiftInRange,
    reference: LocalDateTimeParts,
): DayShiftInterval | null {
    const startedAt = parseLocalIsoDateTime(shift.started_at);
    const endedAt = shift.ended_at === null
        ? reference
        : parseLocalIsoDateTime(shift.ended_at);

    if (startedAt.date > todayDate) {
        return null;
    }

    if (endedAt.date < todayDate) {
        return null;
    }

    const startSecond = startedAt.date < todayDate ? 0 : startedAt.secondOfDay;
    const endSecond = endedAt.date > todayDate
        ? 24 * 60 * 60
        : endedAt.secondOfDay;

    if (endSecond <= startSecond) {
        return null;
    }

    return {
        startSecond,
        endSecond,
    };
}

function parseLocalDate(date: string): { day: number; month: number; year: number } {
    const [year, month, day] = date.split('-').map(Number);

    if (!year || !month || !day) {
        throw new Error(`Invalid local date: ${date}`);
    }

    return {
        day,
        month,
        year,
    };
}

function parseLocalIsoDateTime(value: string): LocalDateTimeParts {
    const match = value.match(/^(\d{4}-\d{2}-\d{2})T(\d{2}):(\d{2})(?::(\d{2})(?:\.\d+)?)?/);

    if (match === null) {
        throw new Error(`Invalid local datetime: ${value}`);
    }

    const [, date, hourString, minuteString, secondString = '0'] = match;
    const hour = Number(hourString);
    const minute = Number(minuteString);
    const second = Number(secondString);

    if ([hour, minute, second].some(Number.isNaN)) {
        throw new Error(`Invalid local datetime: ${value}`);
    }

    return {
        date,
        hour: hour + (minute / 60) + (second / 3600),
        secondOfDay: (hour * 3600) + (minute * 60) + second,
    };
}

function addOneDay(date: string): string {
    const { day, month, year } = parseLocalDate(date);
    const nextDate = new Date(Date.UTC(year, month - 1, day + 1));

    return nextDate.toISOString().slice(0, 10);
}

function padTimeUnit(value: number): string {
    return String(value).padStart(2, '0');
}

function capitalize(value: string, locale: string): string {
    return value.charAt(0).toLocaleUpperCase(locale) + value.slice(1);
}
