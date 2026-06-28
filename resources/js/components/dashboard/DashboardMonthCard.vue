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
import { i18n } from '@/lib/i18n';

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
        label: i18n.global.t('dashboard.hours.month.view.summary'),
    },
    {
        value: 'journey' as const,
        label: i18n.global.t('dashboard.hours.month.view.journey'),
    },
]);
</script>

<template>
    <DashboardPanel class="p-6">
        <div class="mb-6 flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-1">
                <button
                    type="button"
                    class="flex size-6 items-center justify-center rounded-full border border-border bg-background text-muted-foreground transition hover:bg-accent hover:text-foreground disabled:cursor-not-allowed disabled:opacity-35"
                    :disabled="!canGoPrevious"
                    @click="emit('previous')"
                >
                    <ChevronLeft class="size-4" />
                </button>
                <button
                    type="button"
                    class="flex size-6 items-center justify-center rounded-full border border-border bg-background text-muted-foreground transition hover:bg-accent hover:text-foreground disabled:cursor-not-allowed disabled:opacity-35"
                    :disabled="!canGoNext"
                    @click="emit('next')"
                >
                    <ChevronRight class="size-4" />
                </button>
            </div>
            <h2 class="text-lg font-medium text-foreground">
                {{ title }}
            </h2>
            <div class="ml-auto flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                <Button
                    type="button"
                    variant="ghost"
                    class="rounded-full border border-border bg-background px-4 text-muted-foreground hover:bg-accent hover:text-accent-foreground"
                    :disabled="!canExport"
                    @click="emit('export')"
                >
                    <Download class="size-4" />
                    {{ i18n.global.t('exports.button') }}
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
                                : 'border-border',
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
