export type GoalHoursParts = {
    hours: string;
    minutes: string;
};

export const goalHoursOptions = Array.from({ length: 25 }, (_, index) =>
    padGoalHoursPart(index),
);

export function buildGoalHoursMinutesOptions(selectedHours: string): string[] {
    if (selectedHours === '24') {
        return ['00'];
    }

    const start = selectedHours === '00' ? 1 : 0;

    return Array.from({ length: 60 - start }, (_, index) =>
        padGoalHoursPart(index + start),
    );
}

export function parseGoalHoursDuration(
    rawValue: string | number | null,
): GoalHoursParts | null {
    if (typeof rawValue === 'number') {
        return {
            hours: padGoalHoursPart(rawValue),
            minutes: '00',
        };
    }

    if (typeof rawValue !== 'string') {
        return null;
    }

    const durationMatch = rawValue.match(/^(\d{2}):(\d{2})$/);

    if (durationMatch !== null) {
        return {
            hours: durationMatch[1],
            minutes: durationMatch[2],
        };
    }

    const hoursOnlyMatch = rawValue.match(/^\d+$/);

    if (hoursOnlyMatch === null) {
        return null;
    }

    const normalizedHours = normalizeGoalHoursPart(rawValue);

    if (normalizedHours === null) {
        return null;
    }

    return {
        hours: normalizedHours,
        minutes: '00',
    };
}

export function buildGoalHoursDuration(
    hours: string | number,
    minutes: string | number,
): string {
    const normalizedHours = normalizeGoalHoursPart(hours);

    if (normalizedHours === null) {
        return '';
    }

    const normalizedMinutes =
        normalizedHours === '24'
            ? '00'
            : normalizeGoalHoursPart(minutes) ?? '00';

    return `${normalizedHours}:${normalizedMinutes}`;
}

function normalizeGoalHoursPart(
    value: string | number | null | undefined,
): string | null {
    if (typeof value === 'number') {
        if (!Number.isInteger(value) || value < 0) {
            return null;
        }

        return padGoalHoursPart(value);
    }

    if (typeof value !== 'string') {
        return null;
    }

    if (value.trim() === '') {
        return null;
    }

    const numericValue = Number(value);

    if (!Number.isInteger(numericValue) || numericValue < 0) {
        return null;
    }

    return padGoalHoursPart(numericValue);
}

function padGoalHoursPart(value: number): string {
    return String(value).padStart(2, '0');
}
