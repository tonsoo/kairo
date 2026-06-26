<script setup lang="ts">
import type { DashboardLegendItem } from '@/components/dashboard/dashboardData';

defineProps<{
    items: DashboardLegendItem[];
    compact?: boolean;
}>();
</script>

<template>
    <div
        :class="[
            'flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-400',
            compact ? 'justify-center' : 'justify-start',
        ]"
    >
        <div
            v-for="item in items"
            :key="item.label"
            class="flex items-center gap-2"
        >
            <span :class="['h-2 w-2 rounded-full', item.colorClass]" />
            <span>{{ item.label }}</span>
        </div>
    </div>
</template>
