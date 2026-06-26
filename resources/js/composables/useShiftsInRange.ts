import { useHttp } from '@inertiajs/vue3';
import type { Ref } from 'vue';
import { ref } from 'vue';
import { index as listShifts } from '@/actions/App/Http/Controllers/Api/ShiftController';

export type ShiftInRange = {
    id: number;
    timezone: string;
    started_at: string;
    ended_at: string | null;
    duration_minutes: number | null;
};

type ShiftsInRangeResponse = {
    data: ShiftInRange[];
};

export type UseShiftsInRangeReturn = {
    shifts: Ref<ShiftInRange[]>;
    errorMessageKey: Ref<string | null>;
    isLoading: Ref<boolean>;
    fetchShiftsInRange: (from: string, to: string) => Promise<void>;
};

export const useShiftsInRange = (): UseShiftsInRangeReturn => {
    const http = useHttp();
    const shifts = ref<ShiftInRange[]>([]);
    const errorMessageKey = ref<string | null>(null);
    const isLoading = ref(false);

    const fetchShiftsInRange = async (from: string, to: string): Promise<void> => {
        isLoading.value = true;
        errorMessageKey.value = null;

        try {
            const response = (await http.submit(
                listShifts({ query: { from, to } }),
            )) as ShiftsInRangeResponse;

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
        fetchShiftsInRange,
    };
};
