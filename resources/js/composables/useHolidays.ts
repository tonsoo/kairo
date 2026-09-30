import { useHttp } from '@inertiajs/vue3';
import type { Ref } from 'vue';
import { ref } from 'vue';
import {
    index,
    updateFollow,
} from '@/actions/App/Http/Controllers/Api/HolidayController';
import { shiftMonthStart } from '@/lib/history';

export type HolidayApiData = {
    id: number;
    date: string;
    country_code: string;
    is_national: boolean;
    name: string;
    is_followed: boolean;
};

type HolidaysResponse = {
    data: HolidayApiData[];
};

export type UseHolidaysReturn = {
    holidays: Ref<HolidayApiData[]>;
    selectedMonthStart: Ref<string>;
    selectedCountryCode: Ref<string>;
    isLoading: Ref<boolean>;
    isSavingHolidayId: Ref<number | null>;
    errorMessageKey: Ref<string | null>;
    fetchHolidays: () => Promise<void>;
    showPreviousMonth: () => Promise<void>;
    showNextMonth: () => Promise<void>;
    setCountryCode: (countryCode: string) => Promise<void>;
    toggleFollow: (holiday: HolidayApiData) => Promise<void>;
};

export const useHolidays = (
    initialMonth: string,
    initialCountryCode: string | null,
): UseHolidaysReturn => {
    const readHttp = useHttp();
    const followForm = useHttp<{ followed: boolean }, HolidaysResponse>('HolidayFollowForm', {
        followed: false,
    });
    const holidays = ref<HolidayApiData[]>([]);
    const selectedMonthStart = ref(initialMonth);
    const selectedCountryCode = ref(initialCountryCode ?? '');
    const isLoading = ref(false);
    const isSavingHolidayId = ref<number | null>(null);
    const errorMessageKey = ref<string | null>(null);

    const fetchHolidays = async (): Promise<void> => {
        isLoading.value = true;
        errorMessageKey.value = null;

        try {
            const response = (await readHttp.submit(index({
                query: {
                    month: selectedMonthStart.value,
                    country_code: selectedCountryCode.value || undefined,
                },
            }))) as HolidaysResponse;

            holidays.value = response.data;
        } catch {
            errorMessageKey.value = 'holidays.load_error';
            holidays.value = [];
        } finally {
            isLoading.value = false;
        }
    };

    const showPreviousMonth = async (): Promise<void> => {
        selectedMonthStart.value = shiftMonthStart(selectedMonthStart.value, -1);
        await fetchHolidays();
    };

    const showNextMonth = async (): Promise<void> => {
        selectedMonthStart.value = shiftMonthStart(selectedMonthStart.value, 1);
        await fetchHolidays();
    };

    const setCountryCode = async (countryCode: string): Promise<void> => {
        selectedCountryCode.value = countryCode;
        await fetchHolidays();
    };

    const toggleFollow = async (holiday: HolidayApiData): Promise<void> => {
        const originalFollowed = holiday.is_followed;

        isSavingHolidayId.value = holiday.id;
        errorMessageKey.value = null;
        holiday.is_followed = !holiday.is_followed;
        followForm.followed = holiday.is_followed;

        try {
            await followForm.submit(updateFollow({ holiday: holiday.id }));
        } catch {
            holiday.is_followed = originalFollowed;
            errorMessageKey.value = 'holidays.save_error';
        } finally {
            isSavingHolidayId.value = null;
        }
    };

    return {
        holidays,
        selectedMonthStart,
        selectedCountryCode,
        isLoading,
        isSavingHolidayId,
        errorMessageKey,
        fetchHolidays,
        showPreviousMonth,
        showNextMonth,
        setCountryCode,
        toggleFollow,
    };
};
