<script setup lang="ts">
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { ref } from 'vue';
import type {
    DashboardBarItem,
    DashboardLegendItem,
} from '@/components/dashboard/dashboardData';
import DashboardLegend from '@/components/dashboard/DashboardLegend.vue';
import DashboardPanel from '@/components/dashboard/DashboardPanel.vue';
import DashboardStackedBarChart from '@/components/dashboard/DashboardStackedBarChart.vue';

defineProps<{
    items: DashboardBarItem[];
    legend: DashboardLegendItem[];
}>();

const mode = ref<'Resumo' | 'Jornada'>('Resumo');
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
                Junho, 2026 • Banco de horas: 00:00h
            </h2>
            <div class="ml-auto flex items-center gap-4 text-sm text-slate-400">
                <button
                    v-for="view in ['Resumo', 'Jornada'] as const"
                    :key="view"
                    type="button"
                    class="flex items-center gap-2"
                    @click="mode = view"
                >
                    <span
                        :class="[
                            'size-3 rounded-full border',
                            mode === view
                                ? 'border-teal-500 bg-teal-500'
                                : 'border-slate-500',
                        ]"
                    />
                    <span>{{ view }}</span>
                </button>
            </div>
        </div>

        <div class="h-64">
            <DashboardStackedBarChart :items="items" compact />
        </div>

        <div class="mt-4"><DashboardLegend :items="legend" /></div>
    </DashboardPanel>
</template>
