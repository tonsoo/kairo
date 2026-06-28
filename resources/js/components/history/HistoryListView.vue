<script setup lang="ts">
import { ArrowUpRight, Clock3 } from '@lucide/vue';
import {
    formatHistoryDayHeading,
    formatHistoryDaySubheading,
} from '@/lib/history';
import type { HistoryDaySummary } from '@/lib/history';
import { i18n } from '@/lib/i18n';
import type { DashboardLocale } from '@/lib/i18n';
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
            class="flex w-full flex-col gap-4 rounded-2xl border border-border bg-card px-5 py-4 text-left transition-colors hover:border-ring/30 hover:bg-accent/30 md:flex-row md:items-center md:justify-between"
            @click="emit('select', day.date)"
        >
            <div class="flex items-center gap-4">
                <div class="flex size-12 items-center justify-center rounded-2xl border border-border bg-muted text-lg font-semibold text-foreground">
                    {{ day.date.slice(-2) }}
                </div>

                <div class="space-y-1">
                    <p class="text-sm font-medium text-foreground">
                        {{ formatHistoryDayHeading(day.date, props.locale) }}
                    </p>
                    <p class="text-xs uppercase tracking-[0.2em] text-muted-foreground">
                        {{ formatHistoryDaySubheading(day.date, props.locale) }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 md:justify-end">
                <div class="inline-flex items-center gap-2 rounded-full border border-teal-500/15 bg-teal-500/10 px-3 py-1.5 text-sm text-teal-700 dark:text-teal-200">
                    <Clock3 class="size-4 text-teal-400" />
                    <span>{{ i18n.global.t('history.day.worked') }}</span>
                    <span class="font-semibold text-foreground">{{ formatDurationMinutes(day.workedMinutes) }}</span>
                </div>

                <div
                    v-if="day.extraMinutes > 0"
                    class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-200"
                >
                    {{ i18n.global.t('history.day.extra') }}
                    {{ formatDurationMinutes(day.extraMinutes) }}
                </div>

                <div
                    v-if="day.missingMinutes > 0"
                    class="rounded-full border border-border bg-muted/50 px-3 py-1 text-xs font-medium text-muted-foreground"
                >
                    {{ i18n.global.t('history.day.missing') }}
                    {{ formatDurationMinutes(day.missingMinutes) }}
                </div>

                <ArrowUpRight class="size-4 text-muted-foreground" />
            </div>
        </button>

        <div
            v-if="props.days.length === 0"
            class="rounded-2xl border border-dashed border-border bg-muted/30 px-6 py-12 text-center"
        >
            <p class="text-base font-medium text-foreground">
                {{ i18n.global.t('history.empty.title') }}
            </p>
            <p class="mt-2 text-sm text-muted-foreground">
                {{ i18n.global.t('history.empty.description') }}
            </p>
        </div>
    </section>
</template>
