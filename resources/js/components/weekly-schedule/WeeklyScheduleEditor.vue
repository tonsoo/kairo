<script setup lang="ts">
import { computed } from 'vue';
import DashboardPanel from '@/components/dashboard/DashboardPanel.vue';
import WeeklyScheduleRow from '@/components/weekly-schedule/WeeklyScheduleRow.vue';
import { i18n } from '@/lib/i18n';
import type { WeeklyScheduleFormRow } from '@/lib/weeklySchedule';

const rows = defineModel<WeeklyScheduleFormRow[]>('rows', { required: true });

const props = withDefaults(defineProps<{
    effectiveFrom: string;
    timezone: string;
    errors: Record<string, string>;
    isLoading: boolean;
    isSubmitting: boolean;
    isDirty: boolean;
    recentlySuccessful?: boolean;
    errorMessageKey?: string | null;
}>(), {
    recentlySuccessful: false,
    errorMessageKey: null,
});

const saveLabel = computed(() => props.isSubmitting
    ? i18n.global.t('weekly_schedule.saving')
    : i18n.global.t('weekly_schedule.save'));

const emit = defineEmits<{
    submit: [];
}>();

function fieldError(index: number, field: 'expected_minutes' | 'starts_at' | 'ends_at'): string | null {
    return props.errors[`schedules.${index}.${field}`] ?? null;
}
</script>

<template>
    <DashboardPanel class="p-6">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-border pb-5">
            <div class="space-y-2">
                <h1 class="text-xl font-semibold text-foreground">{{ i18n.global.t('weekly_schedule.title') }}</h1>
                <p class="max-w-2xl text-sm text-muted-foreground">{{ i18n.global.t('weekly_schedule.description') }}</p>
                <div class="flex flex-wrap gap-3 text-xs uppercase tracking-[0.18em] text-muted-foreground">
                    <span>{{ i18n.global.t('weekly_schedule.effective_from') }}: {{ effectiveFrom }}</span>
                    <span>{{ i18n.global.t('weekly_schedule.timezone') }}: {{ timezone }}</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span
                    v-if="recentlySuccessful"
                    class="rounded-full border border-teal-500/30 bg-teal-500/10 px-3 py-1 text-xs font-medium text-teal-700 dark:text-teal-300"
                >
                    {{ i18n.global.t('weekly_schedule.saved') }}
                </span>
                <button
                    type="button"
                    class="rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="isLoading || isSubmitting || !isDirty"
                    @click="emit('submit')"
                >
                    {{ saveLabel }}
                </button>
            </div>
        </div>

        <p v-if="errorMessageKey" class="mt-4 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-700 dark:text-rose-200">
            {{ i18n.global.t(errorMessageKey) }}
        </p>

        <p v-if="isLoading" class="mt-6 text-sm text-muted-foreground">
            {{ i18n.global.t('weekly_schedule.loading') }}
        </p>

        <div v-else class="mt-6 space-y-4">
            <WeeklyScheduleRow
                v-for="(entry, index) in rows"
                :key="entry.weekday"
                v-model:row="rows[index]"
                :expected-time-error="fieldError(index, 'expected_minutes')"
                :starts-at-error="fieldError(index, 'starts_at')"
                :ends-at-error="fieldError(index, 'ends_at')"
            />
        </div>
    </DashboardPanel>
</template>
