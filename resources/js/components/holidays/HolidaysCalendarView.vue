<script setup lang="ts">
import { computed } from 'vue';
import type { HolidayApiData } from '@/composables/useHolidays';
import {
    buildWeekdayLabels,
    formatHistoryMonthHeading,
} from '@/lib/history';
import type { DashboardLocale } from '@/lib/i18n';
import { i18n } from '@/lib/i18n';

type HolidayCalendarDay = {
    date: string;
    dayOfMonth: number;
    isCurrentMonth: boolean;
    holidays: HolidayApiData[];
};

const props = defineProps<{
    locale: DashboardLocale;
    monthStart: string;
    holidays: HolidayApiData[];
    savingHolidayId: number | null;
}>();

const emit = defineEmits<{
    toggle: [holiday: HolidayApiData];
}>();

const weekdayLabels = computed(() => buildWeekdayLabels(props.locale));
const monthHeading = computed(() => formatHistoryMonthHeading(props.monthStart, props.locale));
const days = computed<HolidayCalendarDay[]>(() => {
    const [year, month] = props.monthStart.split('-').map(Number);
    const monthDate = new Date(Date.UTC(year, (month ?? 1) - 1, 1));
    const startOffset = (monthDate.getUTCDay() + 6) % 7;
    const daysInMonth = new Date(Date.UTC(year, monthDate.getUTCMonth() + 1, 0)).getUTCDate();
    const totalCells = Math.ceil((startOffset + daysInMonth) / 7) * 7;
    const gridStart = new Date(Date.UTC(year, monthDate.getUTCMonth(), 1 - startOffset));
    const holidaysByDate = new Map<string, HolidayApiData[]>();

    props.holidays.forEach((holiday) => {
        const currentHolidays = holidaysByDate.get(holiday.date) ?? [];
        currentHolidays.push(holiday);
        holidaysByDate.set(holiday.date, currentHolidays);
    });

    return Array.from({ length: totalCells }, (_, index) => {
        const date = new Date(Date.UTC(
            gridStart.getUTCFullYear(),
            gridStart.getUTCMonth(),
            gridStart.getUTCDate() + index,
        ));
        const formattedDate = `${date.getUTCFullYear()}-${String(date.getUTCMonth() + 1).padStart(2, '0')}-${String(date.getUTCDate()).padStart(2, '0')}`;

        return {
            date: formattedDate,
            dayOfMonth: date.getUTCDate(),
            isCurrentMonth: formattedDate.startsWith(props.monthStart.slice(0, 7)),
            holidays: holidaysByDate.get(formattedDate) ?? [],
        };
    });
});
</script>

<template>
    <section class="rounded-3xl border border-border bg-card p-4 md:p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-semibold text-foreground">{{ monthHeading }}</h2>
        </div>

        <div class="grid grid-cols-7 gap-2 text-center text-[11px] font-medium uppercase tracking-[0.22em] text-muted-foreground">
            <span v-for="label in weekdayLabels" :key="label" class="py-2">
                {{ label }}
            </span>
        </div>

        <div class="mt-3 grid grid-cols-7 gap-2">
            <article
                v-for="day in days"
                :key="day.date"
                class="flex min-h-32 flex-col rounded-2xl border px-3 py-3"
                :class="day.isCurrentMonth
                    ? 'border-teal-500/18 bg-background shadow-[inset_0_1px_0_rgba(20,184,166,0.08)]'
                    : 'border-zinc-900/35 bg-zinc-950/35 text-zinc-500 dark:border-zinc-50/8 dark:bg-zinc-950/55 dark:text-zinc-500'"
            >
                <div class="flex items-center justify-between gap-2">
                    <span class="text-sm font-semibold" :class="day.isCurrentMonth ? 'text-foreground' : 'text-zinc-500 dark:text-zinc-500'">
                        {{ day.dayOfMonth }}
                    </span>
                    <span
                        v-if="day.holidays.length > 0"
                        class="rounded-full bg-teal-500/10 px-2 py-0.5 text-[10px] font-medium text-teal-700 dark:text-teal-200"
                    >
                        {{ day.holidays.length }}
                    </span>
                </div>

                <div class="mt-4 space-y-2">
                    <template v-if="day.holidays.length > 0">
                        <button
                            v-for="holiday in day.holidays"
                            :key="holiday.id"
                            type="button"
                            class="w-full rounded-2xl border px-3 py-2 text-left transition-colors"
                            :class="holiday.is_followed
                                ? 'border-emerald-500/25 bg-emerald-500/10 text-emerald-800 dark:text-emerald-200'
                                : 'border-border bg-card text-foreground hover:border-teal-500/35 hover:bg-teal-500/[0.04]'"
                            :disabled="props.savingHolidayId === holiday.id"
                            @click="emit('toggle', holiday)"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                                        {{ holiday.country_code }}
                                    </p>
                                    <p class="mt-1 text-sm font-medium">{{ holiday.name }}</p>
                                </div>
                                <span
                                    class="rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                    :class="holiday.is_followed
                                        ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-200'
                                        : 'bg-muted text-muted-foreground'"
                                >
                                    {{ holiday.is_followed
                                        ? i18n.global.t('holidays.badge.following')
                                        : i18n.global.t('holidays.action.follow') }}
                                </span>
                            </div>
                            <p v-if="holiday.is_national" class="mt-2 text-xs text-muted-foreground">
                                {{ i18n.global.t('holidays.badge.national') }}
                            </p>
                        </button>
                    </template>

                    <p
                        v-else-if="day.isCurrentMonth"
                        class="rounded-2xl border border-dashed border-border px-3 py-4 text-xs text-muted-foreground"
                    >
                        {{ i18n.global.t('holidays.empty_day') }}
                    </p>
                </div>
            </article>
        </div>
    </section>
</template>
