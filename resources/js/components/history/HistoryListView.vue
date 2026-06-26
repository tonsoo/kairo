<script setup lang="ts">
import { ArrowUpRight, Clock3 } from '@lucide/vue';
import { translateDashboard } from '@/lib/dashboardTranslations';
import type { DashboardLocale } from '@/lib/dashboardTranslations';
import {
    formatHistoryDayHeading,
    formatHistoryDaySubheading,
} from '@/lib/history';
import type { HistoryDaySummary } from '@/lib/history';
import { formatDurationMinutes } from '@/lib/time';

const props = defineProps<{
    days: HistoryDaySummary[];
    locale: DashboardLocale;
}>();

const emit = defineEmits<{
    select: [date: string];
}>();
</script>

<template>
    <section class="space-y-3">
        <button
            v-for="day in props.days"
            :key="day.date"
            type="button"
            class="flex w-full flex-col gap-4 rounded-2xl border border-[#2e2f30] bg-[#18191a] px-5 py-4 text-left transition-colors hover:border-[#3c3d40] hover:bg-[#1c1d1f] md:flex-row md:items-center md:justify-between"
            @click="emit('select', day.date)"
        >
            <div class="flex items-center gap-4">
                <div class="flex size-12 items-center justify-center rounded-2xl border border-[#313234] bg-[#222325] text-lg font-semibold text-slate-100">
                    {{ day.date.slice(-2) }}
                </div>

                <div class="space-y-1">
                    <p class="text-sm font-medium text-slate-100">
                        {{ formatHistoryDayHeading(day.date, props.locale) }}
                    </p>
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">
                        {{ formatHistoryDaySubheading(day.date, props.locale) }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 md:justify-end">
                <div class="inline-flex items-center gap-2 rounded-full border border-teal-500/15 bg-teal-500/10 px-3 py-1.5 text-sm text-teal-200">
                    <Clock3 class="size-4 text-teal-400" />
                    <span>{{ translateDashboard('history.day.worked', props.locale) }}</span>
                    <span class="font-semibold text-slate-100">{{ formatDurationMinutes(day.workedMinutes) }}</span>
                </div>

                <div
                    v-if="day.extraMinutes > 0"
                    class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-200"
                >
                    {{ translateDashboard('history.day.extra', props.locale) }}
                    {{ formatDurationMinutes(day.extraMinutes) }}
                </div>

                <div
                    v-if="day.missingMinutes > 0"
                    class="rounded-full border border-slate-500/20 bg-slate-500/10 px-3 py-1 text-xs font-medium text-slate-300"
                >
                    {{ translateDashboard('history.day.missing', props.locale) }}
                    {{ formatDurationMinutes(day.missingMinutes) }}
                </div>

                <ArrowUpRight class="size-4 text-slate-500" />
            </div>
        </button>

        <div
            v-if="props.days.length === 0"
            class="rounded-2xl border border-dashed border-[#343538] bg-[#18191a] px-6 py-12 text-center"
        >
            <p class="text-base font-medium text-slate-100">
                {{ translateDashboard('history.empty.title', props.locale) }}
            </p>
            <p class="mt-2 text-sm text-slate-400">
                {{ translateDashboard('history.empty.description', props.locale) }}
            </p>
        </div>
    </section>
</template>
