import { useHttp } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    index as listWorkSchedules,
    replace,
} from '@/actions/App/Http/Controllers/Api/WorkScheduleController';
import {
    buildWeeklyScheduleRows,
    normalizeWeeklyScheduleRows,
    resolveEffectiveFrom,
} from '@/lib/weeklySchedule';
import type {
    WeeklyScheduleFormRow,
    WorkScheduleApiData,
} from '@/lib/weeklySchedule';

type WorkSchedulesResponse = {
    data: WorkScheduleApiData[];
};

type WeeklyScheduleForm = {
    effective_from: string;
    schedules: WeeklyScheduleFormRow[];
};

export type UseWeeklyWorkSchedulesReturn = {
    form: ReturnType<typeof useHttp<WeeklyScheduleForm, WorkSchedulesResponse>>;
    isLoading: typeof isLoading;
    errorMessageKey: typeof errorMessageKey;
    fetchWorkSchedules: () => Promise<void>;
    saveWorkSchedules: () => Promise<void>;
};

const isLoading = ref(false);
const errorMessageKey = ref<string | null>(null);

export const useWeeklyWorkSchedules = (
    timeZone: string,
): UseWeeklyWorkSchedulesReturn => {
    const readHttp = useHttp();
    const effectiveFrom = resolveEffectiveFrom(timeZone);
    const form = useHttp<WeeklyScheduleForm, WorkSchedulesResponse>(
        'WeeklyScheduleForm',
        {
            effective_from: effectiveFrom,
            schedules: buildWeeklyScheduleRows([], effectiveFrom),
        },
    );

    const fetchWorkSchedules = async (): Promise<void> => {
        isLoading.value = true;
        errorMessageKey.value = null;

        try {
            const response = (await readHttp.submit(
                listWorkSchedules(),
            )) as WorkSchedulesResponse;
            const currentEffectiveFrom = resolveEffectiveFrom(timeZone);

            form.effective_from = currentEffectiveFrom;
            form.schedules = buildWeeklyScheduleRows(
                response.data,
                currentEffectiveFrom,
            );
        } catch {
            errorMessageKey.value = 'weekly_schedule.load_error';
        } finally {
            isLoading.value = false;
        }
    };

    const saveWorkSchedules = async (): Promise<void> => {
        errorMessageKey.value = null;

        form.transform((data) => ({
            effective_from: data.effective_from,
            schedules: normalizeWeeklyScheduleRows(data.schedules),
        }));

        try {
            const response = await form.submit(replace(), {
                onHttpException: () => {
                    errorMessageKey.value = 'weekly_schedule.save_error';
                },
                onNetworkError: () => {
                    errorMessageKey.value = 'weekly_schedule.save_error';
                },
            });

            if (response?.data !== undefined) {
                form.schedules = buildWeeklyScheduleRows(
                    response.data,
                    form.effective_from,
                );
            }
        } catch {
            errorMessageKey.value = 'weekly_schedule.save_error';
        } finally {
            form.transform((data) => data);
        }
    };

    return {
        form,
        isLoading,
        errorMessageKey,
        fetchWorkSchedules,
        saveWorkSchedules,
    };
};
