import { useHttp } from '@inertiajs/vue3';
import type { Ref } from 'vue';
import { ref } from 'vue';
import { destroy as removeBreak } from '@/actions/App/Http/Controllers/Api/ShiftBreakController';
import {
    destroy,
    index as listShifts,
    update,
} from '@/actions/App/Http/Controllers/Api/ShiftController';
import type { ShiftInRange } from '@/composables/useShiftsInRange';
import { getCurrentClientTimezone } from '@/lib/clientDateTime';

type ShiftsResponse = {
    data: ShiftInRange[];
};

export type UpdateShiftPayload = {
    shiftId: number;
    startedAt: string;
    endedAt: string | null;
};

export type RemoveBreakPayload = {
    previousShiftId: number;
    nextShiftId: number;
};

export type UseShiftHistoryDayReturn = {
    selectedDate: Ref<string | null>;
    dayShifts: Ref<ShiftInRange[]>;
    dayErrorMessageKey: Ref<string | null>;
    isLoadingDay: Ref<boolean>;
    savingShiftId: Ref<number | null>;
    deletingShiftId: Ref<number | null>;
    removingBreakKey: Ref<string | null>;
    openDay: (date: string) => Promise<void>;
    closeDay: () => void;
    saveShift: (payload: UpdateShiftPayload) => Promise<void>;
    deleteShift: (shiftId: number) => Promise<void>;
    removeShiftBreak: (payload: RemoveBreakPayload) => Promise<void>;
};

export const useShiftHistoryDay = (
    refreshMonthSummary: () => Promise<void>,
): UseShiftHistoryDayReturn => {
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
    const selectedDate = ref<string | null>(null);
    const dayShifts = ref<ShiftInRange[]>([]);
    const dayErrorMessageKey = ref<string | null>(null);
    const isLoadingDay = ref(false);
    const savingShiftId = ref<number | null>(null);
    const deletingShiftId = ref<number | null>(null);
    const removingBreakKey = ref<string | null>(null);

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

    const removeShiftBreak = async (
        payload: RemoveBreakPayload,
    ): Promise<void> => {
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
        const refreshOperations: Promise<unknown>[] = [refreshMonthSummary()];

        if (selectedDate.value !== null) {
            refreshOperations.push(fetchDayShifts(selectedDate.value));
        }

        await Promise.allSettled(refreshOperations);
    };

    return {
        selectedDate,
        dayShifts,
        dayErrorMessageKey,
        isLoadingDay,
        savingShiftId,
        deletingShiftId,
        removingBreakKey,
        openDay,
        closeDay,
        saveShift,
        deleteShift,
        removeShiftBreak,
    };
};
