import { useHttp } from '@inertiajs/vue3';
import type { ComputedRef } from 'vue';
import { computed, ref } from 'vue';
import { destroy as removeBreak } from '@/actions/App/Http/Controllers/Api/ShiftBreakController';
import {
    destroy,
    index as listShifts,
    update,
} from '@/actions/App/Http/Controllers/Api/ShiftController';
import type { HoursSummaryApiData } from '@/composables/useHoursSummary';
import type { ShiftInRange } from '@/composables/useShiftsInRange';
import {
    getCurrentClientDateTimeAtom,
    getCurrentClientTimezone,
} from '@/lib/clientDateTime';
import {
    buildHistoryDaySummaries,
    getCurrentMonthStart,
    shiftMonthStart,
} from '@/lib/history';
import type { HistoryDaySummary } from '@/lib/history';
import { hoursSummary } from '@/routes/api/me';

type HoursSummaryResponse = {
    data: HoursSummaryApiData;
};

type ShiftsResponse = {
    data: ShiftInRange[];
};

type UpdateShiftPayload = {
    shiftId: number;
    startedAt: string;
    endedAt: string | null;
};

type RemoveBreakPayload = {
    previousShiftId: number;
    nextShiftId: number;
};

export type UseShiftHistoryReturn = {
    monthSummary: typeof monthSummary;
    selectedMonthStart: typeof selectedMonthStart;
    selectedDate: typeof selectedDate;
    selectedDaySummary: ComputedRef<HistoryDaySummary | null>;
    dayShifts: typeof dayShifts;
    errorMessageKey: typeof errorMessageKey;
    dayErrorMessageKey: typeof dayErrorMessageKey;
    isLoadingMonth: typeof isLoadingMonth;
    isLoadingDay: typeof isLoadingDay;
    savingShiftId: typeof savingShiftId;
    deletingShiftId: typeof deletingShiftId;
    removingBreakKey: typeof removingBreakKey;
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

const monthSummary = ref<HoursSummaryApiData['month'] | null>(null);
const selectedMonthStart = ref(getCurrentMonthStart());
const selectedDate = ref<string | null>(null);
const dayShifts = ref<ShiftInRange[]>([]);
const errorMessageKey = ref<string | null>(null);
const dayErrorMessageKey = ref<string | null>(null);
const isLoadingMonth = ref(false);
const isLoadingDay = ref(false);
const savingShiftId = ref<number | null>(null);
const deletingShiftId = ref<number | null>(null);
const removingBreakKey = ref<string | null>(null);

export const useShiftHistory = (): UseShiftHistoryReturn => {
    const readHttp = useHttp();
    const updateShiftForm = useHttp<{
        started_at: string;
        ended_at: string | null;
        timezone: string;
    }>({
        started_at: '',
        ended_at: null,
        timezone: '',
    });
    const removeBreakForm = useHttp<{
        previous_shift_id: number;
        next_shift_id: number;
    }>({
        previous_shift_id: 0,
        next_shift_id: 0,
    });
    const deleteShiftForm = useHttp<Record<string, never>>({});
    const currentMonthStart = getCurrentMonthStart();

    const selectedDaySummary = computed<HistoryDaySummary | null>(() => {
        if (selectedDate.value === null || monthSummary.value === null) {
            return null;
        }

        return buildHistoryDaySummaries(monthSummary.value.items)
            .find((item) => item.date === selectedDate.value) ?? null;
    });

    const canGoToNextMonth = computed<boolean>(() =>
        selectedMonthStart.value < currentMonthStart,
    );

    const fetchMonthSummary = async (): Promise<void> => {
        isLoadingMonth.value = true;
        errorMessageKey.value = null;

        try {
            const response = (await readHttp.submit(
                hoursSummary({
                    query: {
                        at: getCurrentClientDateTimeAtom(),
                        timezone: getCurrentClientTimezone(),
                        month: selectedMonthStart.value,
                    },
                }),
            )) as HoursSummaryResponse;

            monthSummary.value = response.data.month;

            if (
                selectedDate.value !== null
                && ! buildHistoryDaySummaries(response.data.month.items)
                    .some((item) => item.date === selectedDate.value)
            ) {
                closeDay();
            }
        } catch {
            errorMessageKey.value = 'history.load_error';
            monthSummary.value = null;
        } finally {
            isLoadingMonth.value = false;
        }
    };

    const showPreviousMonth = async (): Promise<void> => {
        selectedMonthStart.value = shiftMonthStart(selectedMonthStart.value, -1);
        closeDay();

        await fetchMonthSummary();
    };

    const showNextMonth = async (): Promise<void> => {
        if (! canGoToNextMonth.value) {
            return;
        }

        selectedMonthStart.value = shiftMonthStart(selectedMonthStart.value, 1);
        closeDay();

        await fetchMonthSummary();
    };

    const openDay = async (date: string): Promise<void> => {
        selectedDate.value = date;
        dayErrorMessageKey.value = null;

        await fetchDayShifts(date);
    };

    const closeDay = (): void => {
        selectedDate.value = null;
        dayShifts.value = [];
        dayErrorMessageKey.value = null;
    };

    const saveShift = async (payload: UpdateShiftPayload): Promise<void> => {
        savingShiftId.value = payload.shiftId;
        dayErrorMessageKey.value = null;
        updateShiftForm.started_at = payload.startedAt;
        updateShiftForm.ended_at = payload.endedAt;
        updateShiftForm.timezone = getCurrentClientTimezone();

        try {
            await updateShiftForm.submit(update({ shift: payload.shiftId }));
        } catch {
            dayErrorMessageKey.value = 'history.dialog.action_error';
        } finally {
            await refreshSelectedDay();
            savingShiftId.value = null;
        }
    };

    const deleteShift = async (shiftId: number): Promise<void> => {
        deletingShiftId.value = shiftId;
        dayErrorMessageKey.value = null;

        try {
            await deleteShiftForm.submit(destroy({ shift: shiftId }));
        } catch {
            dayErrorMessageKey.value = 'history.dialog.action_error';
        } finally {
            await refreshSelectedDay();
            deletingShiftId.value = null;
        }
    };

    const removeShiftBreak = async (payload: RemoveBreakPayload): Promise<void> => {
        removingBreakKey.value = `${payload.previousShiftId}:${payload.nextShiftId}`;
        dayErrorMessageKey.value = null;
        removeBreakForm.previous_shift_id = payload.previousShiftId;
        removeBreakForm.next_shift_id = payload.nextShiftId;

        try {
            await removeBreakForm.submit(removeBreak());
        } catch {
            dayErrorMessageKey.value = 'history.dialog.action_error';
        } finally {
            await refreshSelectedDay();
            removingBreakKey.value = null;
        }
    };

    const fetchDayShifts = async (date: string): Promise<void> => {
        isLoadingDay.value = true;

        try {
            const response = (await readHttp.submit(
                listShifts({
                    query: {
                        from: date,
                        to: date,
                        timezone: getCurrentClientTimezone(),
                    },
                }),
            )) as ShiftsResponse;

            dayShifts.value = response.data.sort((left, right) =>
                left.started_at.localeCompare(right.started_at),
            );

            if (response.data.length === 0) {
                closeDay();
            }
        } catch {
            dayErrorMessageKey.value = 'history.dialog.action_error';
            dayShifts.value = [];
        } finally {
            isLoadingDay.value = false;
        }
    };

    const refreshSelectedDay = async (): Promise<void> => {
        const promises = [fetchMonthSummary()];

        if (selectedDate.value !== null) {
            promises.push(fetchDayShifts(selectedDate.value));
        }

        await Promise.allSettled(promises);
    };

    return {
        monthSummary,
        selectedMonthStart,
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
    };
};
