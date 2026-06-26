<script setup lang="ts">
import { computed } from 'vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    getDashboardLocale,
    translateDashboard,
} from '@/lib/dashboardTranslations';

const locale = getDashboardLocale();
const value = defineModel<string>({ required: true });

const hoursOptions = Array.from({ length: 25 }, (_, index) => pad(index));
const minutesOptions = computed(() => {
    if (selectedHours.value === '24') {
        return ['00'];
    }

    const start = selectedHours.value === '00' ? 1 : 0;

    return Array.from(
        { length: 60 - start },
        (_, index) => pad(index + start),
    );
});

const selectedHours = computed({
    get: () => parseDuration(value.value)?.hours ?? '',
    set: (nextHours: string) => {
        const current = parseDuration(value.value);

        value.value = buildDuration(nextHours, current?.minutes ?? '00');
    },
});
const selectedMinutes = computed({
    get: () => parseDuration(value.value)?.minutes ?? '',
    set: (nextMinutes: string) => {
        const current = parseDuration(value.value);

        value.value = buildDuration(current?.hours ?? '00', nextMinutes);
    },
});

function parseDuration(rawValue: string): { hours: string; minutes: string } | null {
    const match = rawValue.match(/^(\d{2}):(\d{2})$/);

    if (match === null) {
        return null;
    }

    return {
        hours: match[1],
        minutes: match[2],
    };
}

function buildDuration(hours: string, minutes: string): string {
    const normalizedHours = normalizePart(hours);

    if (normalizedHours === null) {
        return '';
    }

    const normalizedMinutes = normalizedHours === '24'
        ? '00'
        : normalizePart(minutes) ?? '00';

    return `${normalizedHours}:${normalizedMinutes}`;
}

function normalizePart(value: string): string | null {
    if (value.trim() === '') {
        return null;
    }

    const numericValue = Number(value);

    if (! Number.isInteger(numericValue) || numericValue < 0) {
        return null;
    }

    return pad(numericValue);
}

function pad(value: number): string {
    return String(value).padStart(2, '0');
}
</script>

<template>
    <div class="space-y-2">
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="space-y-2">
                <span class="text-xs font-medium tracking-[0.18em] text-slate-500 uppercase">
                    {{ translateDashboard('weekly_schedule.field.goal_hours_hours', locale) }}
                </span>
                <Select v-model="selectedHours">
                    <SelectTrigger class="h-11 w-full rounded-xl border-[#3a3b3c] bg-[#18191a] px-3 text-sm text-slate-100">
                        <SelectValue :placeholder="translateDashboard('weekly_schedule.field.goal_hours_hours', locale)" />
                    </SelectTrigger>
                    <SelectContent class="border-[#2e2f30] bg-[#18191a] text-slate-100">
                        <SelectItem
                            v-for="hoursOption in hoursOptions"
                            :key="hoursOption"
                            :value="hoursOption"
                            class="focus:bg-white/8 focus:text-slate-100"
                        >
                            {{ hoursOption }}h
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="space-y-2">
                <span class="text-xs font-medium tracking-[0.18em] text-slate-500 uppercase">
                    {{ translateDashboard('weekly_schedule.field.goal_hours_minutes', locale) }}
                </span>
                <Select v-model="selectedMinutes">
                    <SelectTrigger class="h-11 w-full rounded-xl border-[#3a3b3c] bg-[#18191a] px-3 text-sm text-slate-100">
                        <SelectValue :placeholder="translateDashboard('weekly_schedule.field.goal_hours_minutes', locale)" />
                    </SelectTrigger>
                    <SelectContent class="border-[#2e2f30] bg-[#18191a] text-slate-100">
                        <SelectItem
                            v-for="minutesOption in minutesOptions"
                            :key="minutesOption"
                            :value="minutesOption"
                            class="focus:bg-white/8 focus:text-slate-100"
                        >
                            {{ minutesOption }}m
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>
    </div>
</template>
