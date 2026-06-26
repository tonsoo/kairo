<script setup lang="ts">
import { computed } from 'vue';
import type { HoursSummaryItem } from '@/composables/useHoursSummary';
import type { DashboardLocale } from '@/lib/dashboardTranslations';
import {
    buildHistoryCalendarDays,
    buildWeekdayLabels,
} from '@/lib/history';
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
    <section class="rounded-3xl border border-[#2e2f30] bg-[#18191a] p-4 md:p-6">
        <div class="grid grid-cols-7 gap-2 text-center text-[11px] font-medium uppercase tracking-[0.22em] text-slate-500">
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
                    day.isCurrentMonth
                        ? 'border-[#2e2f30] bg-[#1d1e20]'
                        : 'border-[#252628] bg-[#18191a] text-slate-600',
                    day.summary !== null
                        ? 'hover:border-[#3c3d40] hover:bg-[#202123]'
                        : '',
                    day.isToday
                        ? 'ring-1 ring-[#d0ebba]/40'
                        : '',
                ]"
                :disabled="day.summary === null"
                @click="emit('select', day.date)"
            >
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold" :class="day.isCurrentMonth ? 'text-slate-100' : 'text-slate-600'">
                        {{ day.dayOfMonth }}
                    </span>
                    <span
                        v-if="day.summary !== null"
                        class="rounded-full bg-teal-500/10 px-2 py-0.5 text-[10px] font-medium text-teal-200"
                    >
                        {{ formatDurationMinutes(day.summary.workedMinutes) }}
                    </span>
                </div>

                <div v-if="day.summary !== null" class="mt-auto space-y-1 pt-6 text-xs">
                    <p class="text-slate-200">
                        {{ formatDurationMinutes(day.summary.workedMinutes) }}
                    </p>
                    <p v-if="day.summary.extraMinutes > 0" class="text-emerald-300">
                        +{{ formatDurationMinutes(day.summary.extraMinutes) }}
                    </p>
                    <p v-if="day.summary.missingMinutes > 0" class="text-slate-400">
                        -{{ formatDurationMinutes(day.summary.missingMinutes) }}
                    </p>
                </div>
            </button>
        </div>
    </section>
</template>
