<script setup lang="ts">
import type { HistoryDayEditorTab } from '@/lib/history';
import { i18n } from '@/lib/i18n';

const activeTab = defineModel<HistoryDayEditorTab>('activeTab', { required: true });

defineProps<{
    isSavingDay: boolean;
}>();
</script>

<template>
    <div class="inline-flex w-full rounded-xl border border-border bg-muted/30 p-1">
        <button
            type="button"
            class="flex-1 rounded-lg px-3 py-2 text-sm font-medium transition"
            :class="activeTab === 'shifts'
                ? 'bg-background text-foreground shadow-sm'
                : 'text-muted-foreground hover:text-foreground'"
            :disabled="isSavingDay"
            @click="activeTab = 'shifts'"
        >
            {{ i18n.global.t('history.dialog.shifts') }}
        </button>
        <button
            type="button"
            class="flex-1 rounded-lg px-3 py-2 text-sm font-medium transition"
            :class="activeTab === 'daily-schedule'
                ? 'bg-background text-foreground shadow-sm'
                : 'text-muted-foreground hover:text-foreground'"
            :disabled="isSavingDay"
            @click="activeTab = 'daily-schedule'"
        >
            {{ i18n.global.t('history.dialog.daily_schedule') }}
        </button>
    </div>
</template>
