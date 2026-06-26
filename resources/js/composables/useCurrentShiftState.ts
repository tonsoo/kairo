import { useHttp } from '@inertiajs/vue3';
import type { ComputedRef } from 'vue';
import { computed, ref } from 'vue';
import { continueMethod, end, start } from '@/actions/App/Http/Controllers/Api/ShiftController';
import { useHoursSummary } from '@/composables/useHoursSummary';
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
    const http = useHttp();
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
            const response = (await http.submit(
                currentShiftState(),
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
            await http.submit(resolveActionRequest());
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
