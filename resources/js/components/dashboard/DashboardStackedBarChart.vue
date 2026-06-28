<script setup lang="ts">
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import type { DashboardBarItem } from '@/components/dashboard/dashboardData';
import {
    buildDashboardStackedBarChartOptions,
    buildDashboardStackedBarChartSeries,
} from '@/components/dashboard/dashboardStackedBarChart';
import { useAppearance } from '@/composables/useAppearance';

const props = withDefaults(
    defineProps<{
        items: DashboardBarItem[];
        compact?: boolean;
        maxMinutes?: number;
        stepCount?: number;
    }>(),
    {
        compact: false,
        maxMinutes: undefined,
        stepCount: 6,
    },
);

const { resolvedAppearance } = useAppearance();

const chartSeries = computed(() =>
    buildDashboardStackedBarChartSeries(props.items),
);

const chartOptions = computed(() =>
    buildDashboardStackedBarChartOptions({
        items: props.items,
        appearance: resolvedAppearance.value,
        compact: props.compact,
        maxMinutes: props.maxMinutes,
        stepCount: props.stepCount,
    }),
);
</script>

<template>
    <div class="h-full min-h-0">
        <VueApexCharts
            type="bar"
            width="100%"
            height="100%"
            :options="chartOptions"
            :series="chartSeries"
        />
    </div>
</template>
