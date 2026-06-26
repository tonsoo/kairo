<script setup lang="ts">
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import type {
    DashboardBarItem,
    DashboardLegendItem,
} from '@/components/dashboard/dashboardData';
import DashboardLegend from '@/components/dashboard/DashboardLegend.vue';
import DashboardPanel from '@/components/dashboard/DashboardPanel.vue';
import DashboardStackedBarChart from '@/components/dashboard/DashboardStackedBarChart.vue';
import {
    getDashboardLocale,
    translateDashboard,
} from '@/lib/dashboardTranslations';

const locale = getDashboardLocale();

defineProps<{
    items: DashboardBarItem[];
    legend: DashboardLegendItem[];
}>();
</script>

<template>
    <DashboardPanel class="p-6">
        <div class="mb-6 flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-1">
                <button
                    type="button"
                    class="flex size-6 items-center justify-center rounded-full border border-[#3a3b3c] bg-[#18191a] text-slate-400 transition hover:text-white"
                >
                    <ChevronLeft class="size-4" />
                </button>
                <button
                    type="button"
                    class="flex size-6 items-center justify-center rounded-full border border-[#3a3b3c] bg-[#18191a] text-slate-400 transition hover:text-white"
                >
                    <ChevronRight class="size-4" />
                </button>
            </div>
            <h2 class="text-lg font-medium text-slate-200">
                {{ translateDashboard('dashboard.hours.semester.title', locale) }}
            </h2>
            <div class="ml-auto"><DashboardLegend :items="legend" /></div>
        </div>

        <div class="h-[19rem] grow">
            <DashboardStackedBarChart :items="items" />
        </div>
    </DashboardPanel>
</template>
