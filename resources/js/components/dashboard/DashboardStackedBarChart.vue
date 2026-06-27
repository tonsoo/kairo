<script setup lang="ts">
import type { ApexOptions } from 'apexcharts';
import { computed } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import {
    formatDurationMinutes,
    resolveChartMaxMinutes,
} from '@/components/dashboard/dashboardData';
import type { DashboardBarItem } from '@/components/dashboard/dashboardData';
import { i18n } from '@/lib/i18n';


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

const chartMaxMinutes = computed(() =>
    props.maxMinutes ?? resolveChartMaxMinutes(props.items),
);

const chartSeries = computed(() => [
    {
        name: i18n.global.t('dashboard.hours.legend.worked'),
        data: props.items.map((item) => item.workedMinutes),
    },
    {
        name: i18n.global.t('dashboard.hours.legend.extra'),
        data: props.items.map((item) => item.extraMinutes),
    },
    {
        name: i18n.global.t('dashboard.hours.legend.missing'),
        data: props.items.map((item) => item.missingMinutes),
    },
]);

const chartOptions = computed<ApexOptions>(() => ({
    chart: {
        type: 'bar',
        stacked: true,
        animations: {
            enabled: false,
        },
        toolbar: {
            show: false,
        },
        zoom: {
            enabled: false,
        },
        parentHeightOffset: 0,
        foreColor: '#94a3b8',
    },
    colors: ['#0d9488', '#be123c', '#a8a3c5'],
    dataLabels: {
        enabled: false,
    },
    legend: {
        show: false,
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: props.compact ? '52%' : '62%',
            borderRadius: 2,
        },
    },
    grid: {
        borderColor: '#334155',
        strokeDashArray: 4,
        xaxis: {
            lines: {
                show: true,
            },
        },
        yaxis: {
            lines: {
                show: true,
            },
        },
        padding: {
            top: 0,
            right: 8,
            bottom: 0,
            left: 0,
        },
    },
    stroke: {
        show: false,
    },
    tooltip: {
        enabled: true,
        shared: true,
        intersect: false,
        theme: 'dark',
        y: {
            formatter(value: number): string {
                return formatDurationMinutes(Math.round(value));
            },
        },
    },
    xaxis: {
        categories: props.items.map((item) => item.label),
        axisBorder: {
            show: false,
        },
        axisTicks: {
            show: false,
        },
        labels: {
            style: {
                colors: '#94a3b8',
                fontSize: props.compact ? '10px' : '12px',
            },
        },
    },
    yaxis: {
        min: 0,
        max: chartMaxMinutes.value,
        tickAmount: props.stepCount,
        forceNiceScale: false,
        labels: {
            formatter(value: number): string {
                return formatAxisMinutes(value);
            },
            style: {
                colors: '#94a3b8',
                fontSize: '11px',
            },
        },
    },
}));

function formatAxisMinutes(value: number): string {
    const roundedMinutes = Math.max(Math.round(value), 0);
    const hours = Math.floor(roundedMinutes / 60);
    const minutes = roundedMinutes % 60;

    if (minutes === 0) {
        return `${hours}H`;
    }

    return `${hours}H${String(minutes).padStart(2, '0')}`;
}
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
