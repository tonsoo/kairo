<script setup lang="ts">
import type { DashboardLegendItem } from '@/components/dashboard/dashboardData';
import { i18n } from '@/lib/i18n';


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
            :key="item.labelKey"
            class="flex items-center gap-2"
        >
            <span :class="['h-2 w-2 rounded-full', item.colorClass]" />
            <span>{{ i18n.global.t(item.labelKey) }}</span>
        </div>
    </div>
</template>
