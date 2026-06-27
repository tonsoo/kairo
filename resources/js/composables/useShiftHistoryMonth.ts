import { useHttp } from '@inertiajs/vue3';
import type { ComputedRef, Ref } from 'vue';
import { computed, ref } from 'vue';
import type { HoursSummaryApiData } from '@/composables/useHoursSummary';
import {
    getCurrentClientDateTimeAtom,
    getCurrentClientTimezone,
} from '@/lib/clientDateTime';
import {
    buildHistoryDaySummaries,
    getCurrentMonthStart,
    shiftMonthStart,
} from '@/lib/history';
import { hoursSummary } from '@/routes/api/me';

type HoursSummaryResponse = {
    data: {
        month: HoursSummaryApiData['month'];
    };
};

export type UseShiftHistoryMonthReturn = {
    monthSummary: Ref<HoursSummaryApiData['month'] | null>;
    selectedMonthStart: Ref<string>;
    errorMessageKey: Ref<string | null>;
    isLoadingMonth: Ref<boolean>;
    canGoToNextMonth: ComputedRef<boolean>;
    fetchMonthSummary: () => Promise<void>;
    showPreviousMonth: () => Promise<void>;
    showNextMonth: () => Promise<void>;
};

export const useShiftHistoryMonth = (
    selectedDate: Ref<string | null>,
    closeDay: () => void,
): UseShiftHistoryMonthReturn => {
    const readHttp = useHttp();
    const monthSummary = ref<HoursSummaryApiData['month'] | null>(null);
    const selectedMonthStart = ref(getCurrentMonthStart());
    const errorMessageKey = ref<string | null>(null);
    const isLoadingMonth = ref(false);
    const currentMonthStart = getCurrentMonthStart();

    const canGoToNextMonth = computed<boolean>(
        () => selectedMonthStart.value < currentMonthStart,
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
                && !buildHistoryDaySummaries(response.data.month.items).some(
                    (item) => item.date === selectedDate.value,
                )
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
        if (!canGoToNextMonth.value) {
            return;
        }

        selectedMonthStart.value = shiftMonthStart(selectedMonthStart.value, 1);
        closeDay();

        await fetchMonthSummary();
    };

    return {
        monthSummary,
        selectedMonthStart,
        errorMessageKey,
        isLoadingMonth,
        canGoToNextMonth,
        fetchMonthSummary,
        showPreviousMonth,
        showNextMonth,
    };
};
