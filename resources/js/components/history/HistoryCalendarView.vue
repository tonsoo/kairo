<script setup lang="ts">
import { computed } from 'vue';
import type { HoursSummaryItem } from '@/composables/useHoursSummary';
import {
    buildHistoryCalendarDays,
    buildWeekdayLabels,
} from '@/lib/history';
import type { DashboardLocale } from '@/lib/i18n';
import { i18n } from '@/lib/i18n';
import { formatDurationMinutes } from '@/lib/time';

const props = defineProps<{
    locale: DashboardLocale;
    monthStart: string;
    monthItems: HoursSummaryItem[];
    todayDate: string;
}>();

const emit = defineEmits<{
    select: [date: string];
}>();

const weekdayLabels = computed(() => buildWeekdayLabels(props.locale));
const days = computed(() =>
    buildHistoryCalendarDays(props.monthStart, props.monthItems, props.todayDate),
);
</script>

<template>
    <section class="rounded-3xl border border-border bg-card p-4 md:p-6">
        <div class="grid grid-cols-7 gap-2 text-center text-[11px] font-medium uppercase tracking-[0.22em] text-muted-foreground">
            <span v-for="label in weekdayLabels" :key="label" class="py-2">
                {{ label }}
            </span>
        </div>

        <div class="mt-3 grid grid-cols-7 gap-2">
            <button
                v-for="day in days"
                :key="day.date"
                type="button"
                class="flex min-h-28 flex-col rounded-2xl border px-3 py-3 text-left transition-colors"
                :class="[
                    day.isCurrentMonth && !day.isFuture
                        ? 'border-teal-500/18 bg-background shadow-[inset_0_1px_0_rgba(20,184,166,0.08)] hover:border-teal-500/35 hover:bg-teal-500/[0.04]'
                        : day.isCurrentMonth
                            ? 'border-border/60 bg-muted/30 text-muted-foreground/70'
                            : 'border-zinc-900/35 bg-zinc-950/35 text-zinc-500 dark:border-zinc-50/8 dark:bg-zinc-950/55 dark:text-zinc-500',
                    day.isToday
                        ? 'ring-1 ring-primary/25'
                        : '',
                ]"
                :disabled="!day.isCurrentMonth || day.isFuture"
                @click="emit('select', day.date)"
            >
                <div class="flex items-center justify-between gap-2">
                    <span
                        class="text-sm font-semibold"
                        :class="day.isCurrentMonth
                            ? day.isFuture
                                ? 'text-muted-foreground/80'
                                : 'text-foreground'
                            : 'text-zinc-500 dark:text-zinc-500'"
                    >
                        {{ day.dayOfMonth }}
                    </span>
                    <span
                        v-if="day.summary !== null && day.summary.workedMinutes > 0"
                        class="rounded-full bg-teal-500/10 px-2 py-0.5 text-[10px] font-medium text-teal-700 dark:text-teal-200"
                    >
                        {{ formatDurationMinutes(day.summary.workedMinutes) }}
                    </span>
                    <span
                        v-else-if="day.isCurrentMonth && !day.isFuture"
                        class="rounded-full border border-dashed border-teal-500/20 bg-teal-500/[0.04] px-2 py-0.5 text-[10px] font-medium text-teal-700/80 dark:text-teal-200/80"
                    >
                        {{ i18n.global.t('history.dialog.add_daily_schedule') }}
                    </span>
                </div>

                <div v-if="day.summary !== null" class="mt-auto space-y-1 pt-6 text-xs">
                    <p v-if="day.summary.workedMinutes > 0" class="font-medium text-foreground">
                        {{ formatDurationMinutes(day.summary.workedMinutes) }}
                    </p>
                    <p v-if="day.summary.extraMinutes > 0" class="text-emerald-700 dark:text-emerald-300">
                        +{{ formatDurationMinutes(day.summary.extraMinutes) }}
                    </p>
                    <p v-if="day.summary.missingMinutes > 0" class="text-muted-foreground">
                        -{{ formatDurationMinutes(day.summary.missingMinutes) }}
                    </p>
                    <p v-if="!day.summary.hasSchedule" class="text-muted-foreground">
                        {{ i18n.global.t('history.dialog.daily_schedule_empty') }}
                    </p>
                    <p v-else-if="day.summary.expectedMinutes === 0" class="text-muted-foreground">
                        {{ i18n.global.t('weekly_schedule.type.day_off') }}
                    </p>
                </div>
            </button>
        </div>
    </section>
</template>
