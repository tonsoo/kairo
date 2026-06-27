import type { ComputedRef } from 'vue';
import { computed } from 'vue';
import { useShiftHistoryDay } from '@/composables/useShiftHistoryDay';
import type {
    RemoveBreakPayload,
    UpdateShiftPayload,
    UseShiftHistoryDayReturn,
} from '@/composables/useShiftHistoryDay';
import { useShiftHistoryMonth } from '@/composables/useShiftHistoryMonth';
import { buildHistoryDaySummaries } from '@/lib/history';
import type { HistoryDaySummary } from '@/lib/history';

export type UseShiftHistoryReturn = {
    monthSummary: ReturnType<typeof useShiftHistoryMonth>['monthSummary'];
    selectedMonthStart: ReturnType<typeof useShiftHistoryMonth>['selectedMonthStart'];
    selectedDate: UseShiftHistoryDayReturn['selectedDate'];
    selectedDaySummary: ComputedRef<HistoryDaySummary | null>;
    dayShifts: UseShiftHistoryDayReturn['dayShifts'];
    errorMessageKey: ReturnType<typeof useShiftHistoryMonth>['errorMessageKey'];
    dayErrorMessageKey: UseShiftHistoryDayReturn['dayErrorMessageKey'];
    isLoadingMonth: ReturnType<typeof useShiftHistoryMonth>['isLoadingMonth'];
    isLoadingDay: UseShiftHistoryDayReturn['isLoadingDay'];
    savingShiftId: UseShiftHistoryDayReturn['savingShiftId'];
    deletingShiftId: UseShiftHistoryDayReturn['deletingShiftId'];
    removingBreakKey: UseShiftHistoryDayReturn['removingBreakKey'];
    canGoToNextMonth: ComputedRef<boolean>;
    fetchMonthSummary: () => Promise<void>;
    showPreviousMonth: () => Promise<void>;
    showNextMonth: () => Promise<void>;
    openDay: (date: string) => Promise<void>;
    closeDay: () => void;
    saveShift: (payload: UpdateShiftPayload) => Promise<void>;
    deleteShift: (shiftId: number) => Promise<void>;
    removeShiftBreak: (payload: RemoveBreakPayload) => Promise<void>;
};

export const useShiftHistory = (): UseShiftHistoryReturn => {
    let refreshMonthSummary = async (): Promise<void> => {};

    const dayHistory = useShiftHistoryDay(async () => {
        await refreshMonthSummary();
    });
    const monthHistory = useShiftHistoryMonth(
        dayHistory.selectedDate,
        dayHistory.closeDay,
    );

    refreshMonthSummary = monthHistory.fetchMonthSummary;

    const selectedDaySummary = computed<HistoryDaySummary | null>(() => {
        if (
            dayHistory.selectedDate.value === null
            || monthHistory.monthSummary.value === null
        ) {
            return null;
        }

        return (
            buildHistoryDaySummaries(monthHistory.monthSummary.value.items).find(
                (item) => item.date === dayHistory.selectedDate.value,
            ) ?? null
        );
    });

    return {
        monthSummary: monthHistory.monthSummary,
        selectedMonthStart: monthHistory.selectedMonthStart,
        selectedDate: dayHistory.selectedDate,
        selectedDaySummary,
        dayShifts: dayHistory.dayShifts,
        errorMessageKey: monthHistory.errorMessageKey,
        dayErrorMessageKey: dayHistory.dayErrorMessageKey,
        isLoadingMonth: monthHistory.isLoadingMonth,
        isLoadingDay: dayHistory.isLoadingDay,
        savingShiftId: dayHistory.savingShiftId,
        deletingShiftId: dayHistory.deletingShiftId,
        removingBreakKey: dayHistory.removingBreakKey,
        canGoToNextMonth: monthHistory.canGoToNextMonth,
        fetchMonthSummary: monthHistory.fetchMonthSummary,
        showPreviousMonth: monthHistory.showPreviousMonth,
        showNextMonth: monthHistory.showNextMonth,
        openDay: dayHistory.openDay,
        closeDay: dayHistory.closeDay,
        saveShift: dayHistory.saveShift,
        deleteShift: dayHistory.deleteShift,
        removeShiftBreak: dayHistory.removeShiftBreak,
    };
};
