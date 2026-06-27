import { useHttp } from '@inertiajs/vue3';
import type { ComputedRef } from 'vue';
import { computed, ref } from 'vue';
import { end, resume as continueMethod, start } from '@/actions/App/Http/Controllers/Api/CurrentShiftActionsController';
import { useHoursSummary } from '@/composables/useHoursSummary';
import {
    getCurrentClientDateTimeAtom,
    getCurrentClientTimezone,
} from '@/lib/clientDateTime';
import { currentShiftState } from '@/routes/api/me';

export type CurrentShiftAction = 'start' | 'end' | 'continue';

export type CurrentShiftStateApiData = {
    next_action: CurrentShiftAction;
    local_date: string;
    has_shift_today: boolean;
    has_ongoing_shift: boolean;
    active_shift: {
        id: number;
        timezone: string;
        started_at: string;
        ended_at: string | null;
        duration_minutes: number | null;
    } | null;
    latest_shift: {
        id: number;
        timezone: string;
        started_at: string;
        ended_at: string | null;
        duration_minutes: number | null;
    } | null;
};

type CurrentShiftStateResponse = {
    data: CurrentShiftStateApiData;
};

export type UseCurrentShiftStateReturn = {
    currentShiftStateData: typeof currentShiftStateData;
    errorMessageKey: typeof errorMessageKey;
    isLoading: typeof isLoading;
    isSubmitting: typeof isSubmitting;
    buttonLabelKey: ComputedRef<string>;
    fetchCurrentShiftState: () => Promise<void>;
    submitNextAction: () => Promise<void>;
};

const currentShiftStateData = ref<CurrentShiftStateApiData | null>(null);
const errorMessageKey = ref<string | null>(null);
const isLoading = ref(false);
const isSubmitting = ref(false);

export const useCurrentShiftState = (): UseCurrentShiftStateReturn => {
    const readHttp = useHttp();
    const actionHttp = useHttp<{ at: string; timezone: string }>({
        at: '',
        timezone: '',
    });
    const { fetchHoursSummary } = useHoursSummary();

    const buttonLabelKey = computed<string>(() => {
        switch (currentShiftStateData.value?.next_action) {
            case 'end':
                return 'dashboard.shift.end';
            case 'continue':
                return 'dashboard.shift.continue';
            default:
                return 'dashboard.shift.start';
        }
    });

    const fetchCurrentShiftState = async (): Promise<void> => {
        isLoading.value = true;
        errorMessageKey.value = null;

        try {
            const response = (await readHttp.submit(
                currentShiftState({
                    query: {
                        at: getCurrentClientDateTimeAtom(),
                        timezone: getCurrentClientTimezone(),
                    },
                }),
            )) as CurrentShiftStateResponse;

            currentShiftStateData.value = response.data;
        } catch {
            errorMessageKey.value = 'dashboard.shift.error';
            currentShiftStateData.value = null;
        } finally {
            isLoading.value = false;
        }
    };

    const submitNextAction = async (): Promise<void> => {
        isSubmitting.value = true;

        try {
            actionHttp.at = getCurrentClientDateTimeAtom();
            actionHttp.timezone = getCurrentClientTimezone();

            await actionHttp.submit(resolveActionRequest());
            await refreshDashboardState();
        } catch {
            await refreshDashboardState();
        } finally {
            isSubmitting.value = false;
        }
    };

    const refreshDashboardState = async (): Promise<void> => {
        await Promise.allSettled([
            fetchCurrentShiftState(),
            fetchHoursSummary(),
        ]);
    };

    const resolveActionRequest = () => {
        switch (currentShiftStateData.value?.next_action) {
            case 'end':
                return end();
            case 'continue':
                return continueMethod();
            default:
                return start();
        }
    };

    return {
        currentShiftStateData,
        errorMessageKey,
        isLoading,
        isSubmitting,
        buttonLabelKey,
        fetchCurrentShiftState,
        submitNextAction,
    };
};
