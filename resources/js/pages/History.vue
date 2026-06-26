<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import HistoryCalendarView from '@/components/history/HistoryCalendarView.vue';
import HistoryDayEditorDialog from '@/components/history/HistoryDayEditorDialog.vue';
import HistoryListView from '@/components/history/HistoryListView.vue';
import HistoryToolbar from '@/components/history/HistoryToolbar.vue';
import ShiftExportDialog from '@/components/shift-export/ShiftExportDialog.vue';
import { useShiftHistory } from '@/composables/useShiftHistory';
import { getCurrentClientLocalDate } from '@/lib/clientDateTime';
import {
    getDashboardLocale,
    translateDashboard,
} from '@/lib/dashboardTranslations';
import {
    buildHistoryDaySummaries,
    formatHistoryMonthHeading,
} from '@/lib/history';
import type { HistoryView } from '@/lib/history';
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
    errorMessageKey,
    dayErrorMessageKey,
    isLoadingMonth,
    isLoadingDay,
    savingShiftId,
    deletingShiftId,
    removingBreakKey,
    canGoToNextMonth,
    fetchMonthSummary,
    showPreviousMonth,
    showNextMonth,
    openDay,
    closeDay,
    saveShift,
    deleteShift,
    removeShiftBreak,
} = useShiftHistory();

const daySummaries = computed(() =>
    monthSummary.value === null ? [] : buildHistoryDaySummaries(monthSummary.value.items),
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
const todayDate = getCurrentClientLocalDate();

onMounted(() => {
    void fetchMonthSummary();
});

function handleDialogOpenChange(isOpen: boolean): void {
    if (! isOpen) {
        closeDay();
    }
}
</script>

<template>
    <div class="px-8 py-8">
        <Head :title="translateDashboard('history.page.title', locale)" />

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
                class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"
            >
                {{ translateDashboard(errorMessageKey, locale) }}
            </p>

            <p
                v-else-if="isLoadingMonth && monthSummary === null"
                class="rounded-2xl border border-[#2f3033] bg-[#18191a] px-4 py-3 text-sm text-slate-400"
            >
                {{ translateDashboard('history.loading', locale) }}
            </p>

            <HistoryListView
                v-if="currentView === 'list'"
                :days="daySummaries"
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
            :is-loading="isLoadingDay"
            :saving-shift-id="savingShiftId"
            :deleting-shift-id="deletingShiftId"
            :removing-break-key="removingBreakKey"
            :error-message-key="dayErrorMessageKey"
            @update:open="handleDialogOpenChange"
            @save-shift="void saveShift($event)"
            @delete-shift="void deleteShift($event)"
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
