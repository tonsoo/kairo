<script setup lang="ts">
import { Info } from '@lucide/vue';
import type {
    DashboardLegendItem,
    DashboardMeterSegment,
} from '@/components/dashboard/dashboardData';
import DashboardDonutMeter from '@/components/dashboard/DashboardDonutMeter.vue';
import DashboardLegend from '@/components/dashboard/DashboardLegend.vue';
import DashboardPanel from '@/components/dashboard/DashboardPanel.vue';

withDefaults(
    defineProps<{
        title: string;
        highlight: string;
        meterValue: string;
        meterCaption: string;
        segments: DashboardMeterSegment[];
        legend?: DashboardLegendItem[];
        footerText?: string;
        showInfo?: boolean;
    }>(),
    {
        legend: () => [],
    },
);
</script>

<template>
    <DashboardPanel class="p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-sm font-medium text-muted-foreground">
                    {{ title }}
                </h2>
                <span class="text-sm font-semibold text-foreground">
                    {{ highlight }}
                </span>
            </div>
            <Info v-if="showInfo" class="size-4 text-amber-500" />
        </div>

        <div class="py-4">
            <DashboardDonutMeter
                :segments="segments"
                :value="meterValue"
                :caption="meterCaption"
            />
        </div>

        <DashboardLegend v-if="legend.length > 0" :items="legend" compact />

        <button
            v-if="footerText"
            type="button"
            class="mt-4 text-center text-sm font-medium text-teal-600 transition hover:text-teal-500 dark:text-teal-400 dark:hover:text-teal-300"
        >
            {{ footerText }}
        </button>
    </DashboardPanel>
</template>
