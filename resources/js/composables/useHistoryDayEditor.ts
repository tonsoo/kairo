import type { ComputedRef, Ref } from 'vue';
import { computed, ref, watch } from 'vue';
import type { SaveHistoryDayPayload } from '@/composables/useShiftHistoryDay';
import type { ShiftInRange } from '@/composables/useShiftsInRange';
import {
    formatDateTimeLocalValue,
    getClientDateTimeAtomFromLocalValue,
} from '@/lib/clientDateTime';
import type {
    DailyWorkScheduleApiData,
    HistoryDailyScheduleDraft,
    HistoryDayEditorTab,
    HistoryShiftDraft,
} from '@/lib/history';
import {
    durationToMinutes,
    minutesToDuration,
} from '@/lib/weeklySchedule';

export type UseHistoryDayEditorOptions = {
    open: boolean;
    selectedDate: string | null;
    shifts: ShiftInRange[];
    dailyWorkSchedule: DailyWorkScheduleApiData | null;
    isSavingDay: boolean;
};

export type UseHistoryDayEditorReturn = {
    activeTab: Ref<HistoryDayEditorTab>;
    shiftDrafts: Ref<HistoryShiftDraft[]>;
    dailyScheduleDraft: Ref<HistoryDailyScheduleDraft>;
    localErrorKey: Ref<string | null>;
    hasPersistedDailyWorkSchedule: ComputedRef<boolean>;
    hasUnsavedShiftChanges: ComputedRef<boolean>;
    hasUnsavedDailyScheduleChanges: ComputedRef<boolean>;
    hasUnsavedChanges: ComputedRef<boolean>;
    canRemoveBreaks: ComputedRef<boolean>;
    buildSavePayload: () => SaveHistoryDayPayload | null;
};

const emptyDailyScheduleDraft = (): HistoryDailyScheduleDraft => ({
    enabled: false,
    type: 'day_off',
    expectedTime: '',
    startsAt: '',
    endsAt: '',
});

export function useHistoryDayEditor(
    options: UseHistoryDayEditorOptions,
): UseHistoryDayEditorReturn {
    const shiftDrafts = ref<HistoryShiftDraft[]>([]);
    const dailyScheduleDraft = ref<HistoryDailyScheduleDraft>(emptyDailyScheduleDraft());
    const activeTab = ref<HistoryDayEditorTab>('shifts');
    const localErrorKey = ref<string | null>(null);

    const hasPersistedDailyWorkSchedule = computed(() => options.dailyWorkSchedule !== null);
    const hasUnsavedShiftChanges = computed(() => {
        const original = JSON.stringify(
            options.shifts.map(
                (shift) => ({
                    id: shift.id,
                    started_at: formatDateTimeLocalValue(shift.started_at),
                    ended_at: shift.ended_at === null ? '' : formatDateTimeLocalValue(shift.ended_at),
                }),
            ),
        );
        const draft = JSON.stringify(
            shiftDrafts.value.map(
                (shift) => ({
                    id: shift.id,
                    started_at: shift.started_at,
                    ended_at: shift.ended_at,
                }),
            ),
        );

        return original !== draft;
    });
    const hasUnsavedDailyScheduleChanges = computed(() => {
        if (!dailyScheduleDraft.value.enabled) {
            return false;
        }

        if (options.dailyWorkSchedule === null) {
            return true;
        }

        return dailyScheduleDraft.value.type !== options.dailyWorkSchedule.type
            || dailyScheduleDraft.value.expectedTime !== minutesToDuration(options.dailyWorkSchedule.expected_minutes)
            || dailyScheduleDraft.value.startsAt !== (options.dailyWorkSchedule.starts_at ?? '')
            || dailyScheduleDraft.value.endsAt !== (options.dailyWorkSchedule.ends_at ?? '');
    });
    const hasUnsavedChanges = computed(() =>
        hasUnsavedShiftChanges.value || hasUnsavedDailyScheduleChanges.value,
    );
    const canRemoveBreaks = computed(() =>
        !hasUnsavedShiftChanges.value && !options.isSavingDay,
    );

    watch(
        () => [options.shifts, options.dailyWorkSchedule, options.open] as const,
        () => {
            if (!options.open) {
                return;
            }

            shiftDrafts.value = options.shifts.map(
                (shift) => ({
                    id: shift.id,
                    key: `shift-${shift.id}`,
                    started_at: formatDateTimeLocalValue(shift.started_at),
                    ended_at: shift.ended_at === null ? '' : formatDateTimeLocalValue(shift.ended_at),
                }),
            );

            dailyScheduleDraft.value = options.dailyWorkSchedule === null
                ? emptyDailyScheduleDraft()
                : {
                    enabled: true,
                    type: options.dailyWorkSchedule.type,
                    expectedTime: minutesToDuration(options.dailyWorkSchedule.expected_minutes),
                    startsAt: options.dailyWorkSchedule.starts_at ?? '',
                    endsAt: options.dailyWorkSchedule.ends_at ?? '',
                };

            activeTab.value = 'shifts';
            localErrorKey.value = null;
        },
        { immediate: true, deep: true },
    );

    watch(
        () => dailyScheduleDraft.value.type,
        (value) => {
            if (!dailyScheduleDraft.value.enabled) {
                return;
            }

            if (value === 'day_off') {
                dailyScheduleDraft.value.expectedTime = '';
                dailyScheduleDraft.value.startsAt = '';
                dailyScheduleDraft.value.endsAt = '';

                return;
            }

            if (value === 'total_time') {
                dailyScheduleDraft.value.startsAt = '';
                dailyScheduleDraft.value.endsAt = '';

                return;
            }

            dailyScheduleDraft.value.expectedTime = '';
        },
    );

    const buildSavePayload = (): SaveHistoryDayPayload | null => {
        for (const shift of shiftDrafts.value) {
            if (shift.started_at === '') {
                localErrorKey.value = 'history.dialog.action_error';

                return null;
            }

            if (shift.ended_at !== '' && shift.ended_at <= shift.started_at) {
                localErrorKey.value = 'history.dialog.invalid_period';

                return null;
            }
        }

        if (dailyScheduleDraft.value.enabled) {
            if (
                dailyScheduleDraft.value.type === 'total_time'
                && durationToMinutes(dailyScheduleDraft.value.expectedTime) === null
            ) {
                localErrorKey.value = 'history.dialog.invalid_daily_schedule';

                return null;
            }

            if (
                dailyScheduleDraft.value.type === 'time_range'
                && (
                    dailyScheduleDraft.value.startsAt === ''
                    || dailyScheduleDraft.value.endsAt === ''
                    || dailyScheduleDraft.value.endsAt <= dailyScheduleDraft.value.startsAt
                )
            ) {
                localErrorKey.value = 'history.dialog.invalid_daily_schedule';

                return null;
            }
        }

        localErrorKey.value = null;

        return {
            shifts: shiftDrafts.value.map(
                (shift) => ({
                    id: shift.id,
                    startedAt: getClientDateTimeAtomFromLocalValue(shift.started_at),
                    endedAt: shift.ended_at === '' ? null : getClientDateTimeAtomFromLocalValue(shift.ended_at),
                }),
            ),
            dailyWorkSchedule: !dailyScheduleDraft.value.enabled
                ? null
                : {
                    type: dailyScheduleDraft.value.type,
                    expectedMinutes: dailyScheduleDraft.value.type === 'total_time'
                        ? durationToMinutes(dailyScheduleDraft.value.expectedTime)
                        : null,
                    startsAt: dailyScheduleDraft.value.type === 'time_range'
                        ? dailyScheduleDraft.value.startsAt
                        : null,
                    endsAt: dailyScheduleDraft.value.type === 'time_range'
                        ? dailyScheduleDraft.value.endsAt
                        : null,
                },
        };
    };

    return {
        activeTab,
        shiftDrafts,
        dailyScheduleDraft,
        localErrorKey,
        hasPersistedDailyWorkSchedule,
        hasUnsavedShiftChanges,
        hasUnsavedDailyScheduleChanges,
        hasUnsavedChanges,
        canRemoveBreaks,
        buildSavePayload,
    };
}
