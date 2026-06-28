<script setup lang="ts">
import { ChevronLeft, ChevronRight, Download } from '@lucide/vue';
import type {
    DashboardBarItem,
    DashboardLegendItem,
} from '@/components/dashboard/dashboardData';
import DashboardLegend from '@/components/dashboard/DashboardLegend.vue';
import DashboardPanel from '@/components/dashboard/DashboardPanel.vue';
import DashboardStackedBarChart from '@/components/dashboard/DashboardStackedBarChart.vue';
import { Button } from '@/components/ui/button';
import { i18n } from '@/lib/i18n';


defineEmits<{
    previous: [];
    next: [];
    export: [];
}>();

defineProps<{
    title: string;
    items: DashboardBarItem[];
    legend: DashboardLegendItem[];
    canGoPrevious: boolean;
    canGoNext: boolean;
    canExport: boolean;
}>();
</script>

<template>
    <DashboardPanel class="p-6">
        <div class="mb-6 flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-1">
                <button
                    type="button"
                    class="flex size-6 items-center justify-center rounded-full border border-border bg-background text-muted-foreground transition hover:bg-accent hover:text-foreground disabled:cursor-not-allowed disabled:opacity-35"
                    :disabled="!canGoPrevious"
                    @click="$emit('previous')"
                >
                    <ChevronLeft class="size-4" />
                </button>
                <button
                    type="button"
                    class="flex size-6 items-center justify-center rounded-full border border-border bg-background text-muted-foreground transition hover:bg-accent hover:text-foreground disabled:cursor-not-allowed disabled:opacity-35"
                    :disabled="!canGoNext"
                    @click="$emit('next')"
                >
                    <ChevronRight class="size-4" />
                </button>
            </div>
            <h2 class="text-lg font-medium text-foreground">
                {{ title }}
            </h2>
            <div class="ml-auto flex flex-wrap items-center gap-4">
                <Button
                    type="button"
                    variant="ghost"
                    class="rounded-full border border-border bg-background px-4 text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                    :disabled="!canExport"
                    @click="$emit('export')"
                >
                    <Download class="size-4" />
                    {{ i18n.global.t('exports.button') }}
                </Button>
                <DashboardLegend :items="legend" />
            </div>
        </div>

        <div class="h-[19rem] grow">
            <DashboardStackedBarChart :items="items" />
        </div>
    </DashboardPanel>
</template>
