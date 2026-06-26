import { useHttp } from '@inertiajs/vue3';
import { ref } from 'vue';
import { hoursSummary } from '@/routes/api/me';

export type HoursSummaryItem = {
    date: string;
    worked_minutes: number;
    regular_minutes: number;
    extra_minutes: number;
    missing_minutes: number;
};

export type HoursSummaryApiData = {
    generated_at: string;
    timezone: string;
    balance: {
        balance_minutes: number;
        positive_minutes: number;
        negative_minutes: number;
    };
    today: {
        date: string;
        worked_minutes: number;
        paused_minutes: number;
        expected_minutes: number;
        regular_minutes: number;
        extra_minutes: number;
        missing_minutes: number;
    };
    semester: {
        starts_at: string;
        ends_at: string;
        items: HoursSummaryItem[];
    };
    month: {
        starts_at: string;
        ends_at: string;
        balance_minutes: number;
        items: HoursSummaryItem[];
    };
};

type HoursSummaryResponse = {
    data: HoursSummaryApiData;
};

export type UseHoursSummaryReturn = {
    hoursSummaryData: typeof hoursSummaryData;
    errorMessageKey: typeof errorMessageKey;
    isLoading: typeof isLoading;
    fetchHoursSummary: () => Promise<void>;
};

const hoursSummaryData = ref<HoursSummaryApiData | null>(null);
const errorMessageKey = ref<string | null>(null);
const isLoading = ref(false);

export const useHoursSummary = (): UseHoursSummaryReturn => {
    const http = useHttp();

    const fetchHoursSummary = async (): Promise<void> => {
        isLoading.value = true;
        errorMessageKey.value = null;

        try {
            const response = (await http.submit(
                hoursSummary(),
            )) as HoursSummaryResponse;

            hoursSummaryData.value = response.data;
        } catch {
            errorMessageKey.value = 'dashboard.hours.error';
            hoursSummaryData.value = null;
        } finally {
            isLoading.value = false;
        }
    };

    return {
        hoursSummaryData,
        errorMessageKey,
        isLoading,
        fetchHoursSummary,
    };
};
