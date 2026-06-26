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
        highlight?: string;
        highlightClass?: string;
        meterValue: string;
        meterCaption: string;
        segments: DashboardMeterSegment[];
        legend?: DashboardLegendItem[];
        footerText?: string;
        showInfo?: boolean;
    }>(),
    {
        highlightClass: 'text-teal-400',
        legend: () => [],
    },
);
</script>

<template>
    <DashboardPanel class="p-5">
        <div class="flex items-start justify-between gap-4">
            <h2 class="text-sm font-medium text-slate-200">
                {{ title }}
                <span v-if="highlight" :class="['font-bold', highlightClass]">{{
                    highlight
                }}</span>
            </h2>
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
            class="mt-4 text-center text-sm font-medium text-teal-400 transition hover:text-teal-300"
        >
            {{ footerText }}
        </button>
    </DashboardPanel>
</template>
