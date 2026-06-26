<script setup lang="ts">
import { ChevronLeft, ChevronRight, Download } from '@lucide/vue';
import { computed, ref } from 'vue';
import type {
    DashboardBarItem,
    DashboardJourneyItem,
    DashboardLegendItem,
} from '@/components/dashboard/dashboardData';
import DashboardJourneyChart from '@/components/dashboard/DashboardJourneyChart.vue';
import DashboardLegend from '@/components/dashboard/DashboardLegend.vue';
import DashboardPanel from '@/components/dashboard/DashboardPanel.vue';
import DashboardStackedBarChart from '@/components/dashboard/DashboardStackedBarChart.vue';
import { Button } from '@/components/ui/button';
import {
    getDashboardLocale,
    translateDashboard,
} from '@/lib/dashboardTranslations';

const locale = getDashboardLocale();
const emit = defineEmits<{
    previous: [];
    next: [];
    export: [];
}>();

defineProps<{
    title: string;
    items: DashboardBarItem[];
    journeyItems: DashboardJourneyItem[];
    legend: DashboardLegendItem[];
    maxMinutes: number;
    canGoPrevious: boolean;
    canGoNext: boolean;
    canExport: boolean;
}>();

const mode = ref<'summary' | 'journey'>('summary');

const views = computed(() => [
    {
        value: 'summary' as const,
        label: translateDashboard('dashboard.hours.month.view.summary', locale),
    },
    {
        value: 'journey' as const,
        label: translateDashboard('dashboard.hours.month.view.journey', locale),
    },
]);
</script>

<template>
    <DashboardPanel class="p-6">
        <div class="mb-6 flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-1">
                <button
                    type="button"
                    class="flex size-6 items-center justify-center rounded-full border border-[#3a3b3c] bg-[#18191a] text-slate-400 transition hover:text-white disabled:cursor-not-allowed disabled:opacity-35"
                    :disabled="!canGoPrevious"
                    @click="emit('previous')"
                >
                    <ChevronLeft class="size-4" />
                </button>
                <button
                    type="button"
                    class="flex size-6 items-center justify-center rounded-full border border-[#3a3b3c] bg-[#18191a] text-slate-400 transition hover:text-white disabled:cursor-not-allowed disabled:opacity-35"
                    :disabled="!canGoNext"
                    @click="emit('next')"
                >
                    <ChevronRight class="size-4" />
                </button>
            </div>
            <h2 class="text-lg font-medium text-slate-200">
                {{ title }}
            </h2>
            <div class="ml-auto flex flex-wrap items-center gap-4 text-sm text-slate-400">
                <Button
                    type="button"
                    variant="ghost"
                    class="rounded-full border border-[#313234] bg-[#18191a] px-4 text-slate-300 hover:bg-[#242526] hover:text-slate-100"
                    :disabled="!canExport"
                    @click="emit('export')"
                >
                    <Download class="size-4" />
                    {{ translateDashboard('exports.button', locale) }}
                </Button>
                <button
                    v-for="view in views"
                    :key="view.value"
                    type="button"
                    class="flex items-center gap-2"
                    @click="mode = view.value"
                >
                    <span
                        :class="[
                            'size-3 rounded-full border',
                            mode === view.value
                                ? 'border-teal-500 bg-teal-500'
                                : 'border-slate-500',
                        ]"
                    />
                    <span>{{ view.label }}</span>
                </button>
            </div>
        </div>

        <div :class="mode === 'journey' ? 'h-[52rem]' : 'h-64'">
            <DashboardStackedBarChart
                v-if="mode === 'summary'"
                :items="items"
                :max-minutes="maxMinutes"
                compact
            />
            <DashboardJourneyChart v-else :items="journeyItems" />
        </div>

        <div v-if="mode === 'summary'" class="mt-4">
            <DashboardLegend :items="legend" />
        </div>
    </DashboardPanel>
</template>
