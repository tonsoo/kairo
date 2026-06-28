<script setup lang="ts">
import type { ApexOptions } from 'apexcharts';
import { computed, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { formatDurationMinutes } from '@/components/dashboard/dashboardData';
import type { DashboardMeterSegment } from '@/components/dashboard/dashboardData';
import { useAppearance } from '@/composables/useAppearance';

const MIN_VISIBLE_FRACTION = 2 / 360;

const props = withDefaults(
    defineProps<{
        segments: DashboardMeterSegment[];
        value: string;
        caption: string;
        size?: string;
    }>(),
    {
        size: 'h-44 w-44',
    },
);

const activeSegmentIndex = ref<number | null>(null);
const { resolvedAppearance } = useAppearance();

const visibleSegments = computed(() =>
    props.segments.filter((segment) => segment.value > 0),
);

const total = computed(() =>
    visibleSegments.value.reduce((sum, segment) => sum + segment.value, 0),
);

const renderedSegments = computed(() => {
    if (total.value <= 0) {
        return [];
    }

    const minimumFraction = visibleSegments.value.length * MIN_VISIBLE_FRACTION;
    const scale = minimumFraction >= 1
        ? 0
        : (1 - minimumFraction);

    return visibleSegments.value.map((segment) => {
        const rawFraction = segment.value / total.value;

        return {
            ...segment,
            chartValue: MIN_VISIBLE_FRACTION + (rawFraction * scale),
        };
    });
});

const activeSegment = computed(() => {
    if (activeSegmentIndex.value === null) {
        return null;
    }

    return renderedSegments.value[activeSegmentIndex.value] ?? null;
});

const chartSeries = computed(() =>
    renderedSegments.value.map((segment) => segment.chartValue),
);

const chartOptions = computed<ApexOptions>(() => ({
    chart: {
        type: 'donut',
        sparkline: {
            enabled: false,
        },
        animations: {
            enabled: false,
        },
        events: {
            dataPointMouseEnter: (_event, _chartContext, config) => {
                const dataPointIndex = config?.dataPointIndex;

                if (dataPointIndex === undefined) {
                    return;
                }

                activeSegmentIndex.value = dataPointIndex;
            },
            dataPointMouseLeave: () => {
                activeSegmentIndex.value = null;
            },
            mouseLeave: () => {
                activeSegmentIndex.value = null;
            },
        },
    },
    colors: renderedSegments.value.map((segment) => resolveColor(segment.colorClass)),
    labels: renderedSegments.value.map((segment) => segment.labelKey),
    legend: {
        show: false,
    },
    dataLabels: {
        enabled: false,
    },
    tooltip: {
        enabled: false,
    },
    stroke: {
        width: 2.5,
        colors: [resolvedAppearance.value === 'dark' ? '#242526' : '#ffffff'],
    },
    states: {
        hover: {
            filter: {
                type: 'lighten',
                value: 0.15,
            },
        },
        active: {
            filter: {
                type: 'none',
            },
        },
    },
    plotOptions: {
        pie: {
            expandOnClick: false,
            donut: {
                size: '80%',
            },
        },
    },
}));

function resolveColor(colorClass: string): string {
    const colorMap: Record<string, string> = {
        'bg-teal-500': '#0d9488',
        'bg-amber-500': '#d97706',
        'bg-rose-500': '#be123c',
        'bg-slate-500/70': '#a8a3c5',
    };

    return colorMap[colorClass] ?? '#64748b';
}
</script>

<template>
    <div
        :class="[
            'relative mx-auto grid place-items-center overflow-visible',
            size,
        ]"
        @mouseleave="activeSegmentIndex = null"
    >
        <VueApexCharts
            type="donut"
            width="100%"
            height="100%"
            :options="chartOptions"
            :series="chartSeries"
            class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2"
        />

        <div
            class="pointer-events-none relative z-10 grid h-[calc(100%-48px)] w-[calc(100%-48px)] place-items-center rounded-full bg-card text-center ring-1 ring-border"
            :class="activeSegment ? 'opacity-30' : 'opacity-100'"
        >
            <div class="space-y-1">
                <p class="text-3xl font-semibold text-foreground">{{ value }}</p>
                <p
                    class="text-[11px] tracking-[0.24em] text-muted-foreground uppercase"
                >
                    {{ caption }}
                </p>
            </div>
        </div>

        <div
            v-if="activeSegment"
            class="pointer-events-none absolute left-[54%] top-[56%] z-20 -translate-x-1/2 -translate-y-1/2 rounded-xl border border-border bg-popover px-4 py-3 text-sm text-popover-foreground shadow-lg"
        >
            {{ formatDurationMinutes(Math.round(activeSegment.value)) }}
        </div>
    </div>
</template>
