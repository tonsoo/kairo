import { useHttp } from '@inertiajs/vue3';
import { ref } from 'vue';
import { index as listShifts } from '@/actions/App/Http/Controllers/Api/ShiftController';

export type MonthJourneyShift = {
    id: number;
    timezone: string;
    started_at: string;
    ended_at: string | null;
    duration_minutes: number | null;
};

type MonthJourneyShiftsResponse = {
    data: MonthJourneyShift[];
};

export type UseMonthJourneyShiftsReturn = {
    shifts: typeof shifts;
    errorMessageKey: typeof errorMessageKey;
    isLoading: typeof isLoading;
    fetchMonthJourneyShifts: (from: string, to: string) => Promise<void>;
};

const shifts = ref<MonthJourneyShift[]>([]);
const errorMessageKey = ref<string | null>(null);
const isLoading = ref(false);

export const useMonthJourneyShifts = (): UseMonthJourneyShiftsReturn => {
    const http = useHttp();

    const fetchMonthJourneyShifts = async (from: string, to: string): Promise<void> => {
        isLoading.value = true;
        errorMessageKey.value = null;

        try {
            const response = (await http.submit(
                listShifts({ query: { from, to } }),
            )) as MonthJourneyShiftsResponse;

            shifts.value = response.data;
        } catch {
            errorMessageKey.value = 'dashboard.hours.error';
            shifts.value = [];
        } finally {
            isLoading.value = false;
        }
    };

    return {
        shifts,
        errorMessageKey,
        isLoading,
        fetchMonthJourneyShifts,
    };
};
