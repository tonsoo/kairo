import { useHttp } from '@inertiajs/vue3';
import type { Ref } from 'vue';
import { ref } from 'vue';
import { show, upsert } from '@/actions/App/Http/Controllers/Api/DailyWorkScheduleController';
import { destroy as removeBreak } from '@/actions/App/Http/Controllers/Api/ShiftBreakController';
import {
    destroy,
    index as listShifts,
    store,
    update,
} from '@/actions/App/Http/Controllers/Api/ShiftController';
import type { ShiftInRange } from '@/composables/useShiftsInRange';
import {
    getCurrentClientTimezone,
} from '@/lib/clientDateTime';
import type { DailyWorkScheduleApiData } from '@/lib/history';
import type { WorkScheduleType } from '@/lib/weeklySchedule';

type ShiftsResponse = {
    data: ShiftInRange[];
};

type DailyWorkScheduleResponse = {
    data: DailyWorkScheduleApiData | null;
};

export type SaveDayShiftPayload = {
    id: number | null;
    startedAt: string;
    endedAt: string | null;
};

export type SaveDayDailyWorkSchedulePayload = {
    type: WorkScheduleType;
    expectedMinutes: number | null;
    startsAt: string | null;
    endsAt: string | null;
};

export type SaveHistoryDayPayload = {
    shifts: SaveDayShiftPayload[];
    dailyWorkSchedule: SaveDayDailyWorkSchedulePayload | null;
};

export type RemoveBreakPayload = {
    previousShiftId: number;
    nextShiftId: number;
};

export type UseShiftHistoryDayReturn = {
    selectedDate: Ref<string | null>;
    dayShifts: Ref<ShiftInRange[]>;
    dayDailyWorkSchedule: Ref<DailyWorkScheduleApiData | null>;
    dayErrorMessageKey: Ref<string | null>;
    isLoadingDay: Ref<boolean>;
    isSavingDay: Ref<boolean>;
    removingBreakKey: Ref<string | null>;
    openDay: (date: string) => Promise<void>;
    closeDay: () => void;
    saveDay: (payload: SaveHistoryDayPayload) => Promise<void>;
    removeShiftBreak: (payload: RemoveBreakPayload) => Promise<void>;
};

export const useShiftHistoryDay = (
    refreshMonthSummary: () => Promise<void>,
): UseShiftHistoryDayReturn => {
    const readHttp = useHttp();
    const shiftMutationHttp = useHttp<{
        started_at: string;
        ended_at: string | null;
        timezone: string;
    }>({
        started_at: '',
        ended_at: null,
        timezone: '',
    });
    const dailyWorkScheduleHttp = useHttp<{
        type: WorkScheduleType;
        expected_minutes: number | null;
        starts_at: string | null;
        ends_at: string | null;
    }>({
        type: 'day_off',
        expected_minutes: null,
        starts_at: null,
        ends_at: null,
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
    const dayDailyWorkSchedule = ref<DailyWorkScheduleApiData | null>(null);
    const dayErrorMessageKey = ref<string | null>(null);
    const isLoadingDay = ref(false);
    const isSavingDay = ref(false);
    const removingBreakKey = ref<string | null>(null);

    const openDay = async (date: string): Promise<void> => {
        selectedDate.value = date;
        dayErrorMessageKey.value = null;

        await fetchDayData(date);
    };

    const closeDay = (): void => {
        selectedDate.value = null;
        dayShifts.value = [];
        dayDailyWorkSchedule.value = null;
        dayErrorMessageKey.value = null;
    };

    const saveDay = async (payload: SaveHistoryDayPayload): Promise<void> => {
        if (selectedDate.value === null) {
            return;
        }

        isSavingDay.value = true;
        dayErrorMessageKey.value = null;

        const originalShiftsById = new Map(dayShifts.value.map((shift) => [shift.id, shift]));
        const submittedShiftIds = new Set<number>();
        let hadError = false;

        try {
            if (payload.dailyWorkSchedule !== null) {
                dailyWorkScheduleHttp.type = payload.dailyWorkSchedule.type;
                dailyWorkScheduleHttp.expected_minutes = payload.dailyWorkSchedule.expectedMinutes;
                dailyWorkScheduleHttp.starts_at = payload.dailyWorkSchedule.startsAt;
                dailyWorkScheduleHttp.ends_at = payload.dailyWorkSchedule.endsAt;

                await dailyWorkScheduleHttp.submit(
                    upsert({ date: selectedDate.value }),
                );
            }

            for (const shift of payload.shifts) {
                shiftMutationHttp.started_at = shift.startedAt;
                shiftMutationHttp.ended_at = shift.endedAt;
                shiftMutationHttp.timezone = getCurrentClientTimezone();

                if (shift.id === null) {
                    await shiftMutationHttp.submit(store());
                    continue;
                }

                submittedShiftIds.add(shift.id);

                const originalShift = originalShiftsById.get(shift.id);

                if (
                    originalShift !== undefined
                    && originalShift.started_at === shift.startedAt
                    && originalShift.ended_at === shift.endedAt
                ) {
                    continue;
                }

                await shiftMutationHttp.submit(update({ shift: shift.id }));
            }

            for (const shift of dayShifts.value) {
                if (submittedShiftIds.has(shift.id)) {
                    continue;
                }

                await deleteShiftForm.submit(destroy({ shift: shift.id }));
            }
        } catch {
            hadError = true;
            dayErrorMessageKey.value = 'history.dialog.action_error';
        } finally {
            await refreshSelectedDay();
            isSavingDay.value = false;

            if (hadError) {
                dayErrorMessageKey.value = 'history.dialog.action_error';
            }
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

    const fetchDayData = async (date: string): Promise<void> => {
        isLoadingDay.value = true;

        try {
            const [shiftsResponse, dailyWorkScheduleResponse] = await Promise.all([
                readHttp.submit(
                    listShifts({
                        query: {
                            from: date,
                            to: date,
                            timezone: getCurrentClientTimezone(),
                        },
                    }),
                ) as Promise<ShiftsResponse>,
                readHttp.submit(show({ date })) as Promise<DailyWorkScheduleResponse>,
            ]);

            dayShifts.value = shiftsResponse.data.sort((left, right) =>
                left.started_at.localeCompare(right.started_at),
            );
            dayDailyWorkSchedule.value = dailyWorkScheduleResponse.data;
        } catch {
            dayErrorMessageKey.value = 'history.dialog.action_error';
            dayShifts.value = [];
            dayDailyWorkSchedule.value = null;
        } finally {
            isLoadingDay.value = false;
        }
    };

    const refreshSelectedDay = async (): Promise<void> => {
        const refreshOperations: Promise<unknown>[] = [refreshMonthSummary()];

        if (selectedDate.value !== null) {
            refreshOperations.push(fetchDayData(selectedDate.value));
        }

        await Promise.allSettled(refreshOperations);
    };

    return {
        selectedDate,
        dayShifts,
        dayDailyWorkSchedule,
        dayErrorMessageKey,
        isLoadingDay,
        isSavingDay,
        removingBreakKey,
        openDay,
        closeDay,
        saveDay,
        removeShiftBreak,
    };
};
