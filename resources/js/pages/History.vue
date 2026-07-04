<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import HistoryCalendarView from '@/components/history/HistoryCalendarView.vue';
import HistoryDayEditorDialog from '@/components/history/HistoryDayEditorDialog.vue';
import HistoryListView from '@/components/history/HistoryListView.vue';
import HistoryToolbar from '@/components/history/HistoryToolbar.vue';
import ShiftExportDialog from '@/components/shift-export/ShiftExportDialog.vue';
import { useShiftHistory } from '@/composables/useShiftHistory';
import { getCurrentClientLocalDate } from '@/lib/clientDateTime';
import {
    buildHistoryMonthDays,
    formatHistoryMonthHeading,
} from '@/lib/history';
import type { HistoryView } from '@/lib/history';
import { getDashboardLocale, i18n } from '@/lib/i18n';
import type { ShiftExportFormatOption } from '@/lib/shiftExport';

const props = defineProps<{
    shiftExportFormats: ShiftExportFormatOption[];
}>();

const locale = getDashboardLocale();
const currentView = ref<HistoryView>('list');
const isExportDialogOpen = ref(false);
const {
    monthSummary,
    selectedDate,
    selectedDaySummary,
    dayShifts,
    dayDailyWorkSchedule,
    errorMessageKey,
    dayErrorMessageKey,
    isLoadingMonth,
    isLoadingDay,
    isSavingDay,
    removingBreakKey,
    canGoToNextMonth,
    fetchMonthSummary,
    showPreviousMonth,
    showNextMonth,
    openDay,
    closeDay,
    saveDay,
    removeShiftBreak,
} = useShiftHistory();

const todayDate = getCurrentClientLocalDate();
const monthDays = computed(() =>
    monthSummary.value === null
        ? []
        : buildHistoryMonthDays(monthSummary.value.starts_at, monthSummary.value.items, todayDate),
);
const monthHeading = computed(() =>
    monthSummary.value === null
        ? ''
        : formatHistoryMonthHeading(monthSummary.value.starts_at, locale),
);
const exportRange = computed(() => ({
    from: monthSummary.value?.starts_at ?? '',
    to: monthSummary.value?.ends_at ?? '',
}));

onMounted(() => {
    void fetchMonthSummary();
});

function handleDialogOpenChange(isOpen: boolean): void {
    if (!isOpen) {
        closeDay();
    }
}
</script>

<template>
    <div class="px-8 py-8">

        <div class="space-y-6">
            <HistoryToolbar
                :locale="locale"
                :month-heading="monthHeading"
                :current-view="currentView"
                :can-go-to-next-month="canGoToNextMonth"
                :can-export="monthSummary !== null"
                @previous="void showPreviousMonth()"
                @next="void showNextMonth()"
                @export="isExportDialogOpen = true"
                @update:view="currentView = $event"
            />

            <p
                v-if="errorMessageKey"
                class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-700 dark:text-rose-200"
            >
                {{ i18n.global.t(errorMessageKey) }}
            </p>

            <p
                v-else-if="isLoadingMonth && monthSummary === null"
                class="rounded-2xl border border-border bg-card px-4 py-3 text-sm text-muted-foreground"
            >
                {{ i18n.global.t('history.loading') }}
            </p>

            <HistoryListView
                v-if="currentView === 'list'"
                :days="monthDays"
                :locale="locale"
                @select="void openDay($event)"
            />

            <HistoryCalendarView
                v-else-if="monthSummary !== null"
                :locale="locale"
                :month-start="monthSummary.starts_at"
                :month-items="monthSummary.items"
                :today-date="todayDate"
                @select="void openDay($event)"
            />
        </div>

        <HistoryDayEditorDialog
            :open="selectedDate !== null"
            :locale="locale"
            :selected-date="selectedDate"
            :selected-day-summary="selectedDaySummary"
            :shifts="dayShifts"
            :daily-work-schedule="dayDailyWorkSchedule"
            :is-loading="isLoadingDay"
            :is-saving-day="isSavingDay"
            :removing-break-key="removingBreakKey"
            :error-message-key="dayErrorMessageKey"
            @update:open="handleDialogOpenChange"
            @save-day="void saveDay($event)"
            @remove-break="void removeShiftBreak($event)"
        />

        <ShiftExportDialog
            :open="isExportDialogOpen"
            :locale="locale"
            title-key="exports.dialog.title"
            description-key="exports.dialog.description.history"
            :formats="props.shiftExportFormats"
            :initial-from="exportRange.from"
            :initial-to="exportRange.to"
            editable-range
            @update:open="isExportDialogOpen = $event"
        />
    </div>
</template>
