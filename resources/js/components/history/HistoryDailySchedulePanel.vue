<script setup lang="ts">
import { Plus } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import GoalHoursSelect from '@/components/weekly-schedule/GoalHoursSelect.vue';
import type { HistoryDailyScheduleDraft } from '@/lib/history';
import { i18n } from '@/lib/i18n';
import type { WorkScheduleType } from '@/lib/weeklySchedule';

const draft = defineModel<HistoryDailyScheduleDraft>('draft', { required: true });

defineProps<{
    hasPersistedDailyWorkSchedule: boolean;
    isSavingDay: boolean;
}>();

const dayTypeOptions: Array<{ value: WorkScheduleType; labelKey: string }> = [
    { value: 'day_off', labelKey: 'weekly_schedule.type.day_off' },
    { value: 'total_time', labelKey: 'weekly_schedule.type.total_time' },
    { value: 'time_range', labelKey: 'weekly_schedule.type.time_range' },
];

function enableDailySchedule(): void {
    draft.value.enabled = true;
    draft.value.type = 'total_time';
    draft.value.expectedTime = '08:00';
}
</script>

<template>
    <section class="space-y-4 rounded-2xl border border-border bg-card p-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-medium text-foreground">
                    {{ i18n.global.t('history.dialog.daily_schedule') }}
                </h3>
                <p class="text-sm text-muted-foreground">
                    {{ i18n.global.t('history.dialog.daily_schedule_description') }}
                </p>
            </div>

            <Button
                v-if="!draft.enabled"
                type="button"
                size="sm"
                class="rounded-full"
                :disabled="isSavingDay"
                @click="enableDailySchedule"
            >
                <Plus class="mr-1 size-4" />
                {{ i18n.global.t('history.dialog.add_daily_schedule') }}
            </Button>
        </div>

        <div v-if="draft.enabled" class="space-y-4">
            <div class="grid grid-cols-3 gap-2">
                <button
                    v-for="option in dayTypeOptions"
                    :key="option.value"
                    type="button"
                    class="rounded-xl border px-3 py-2.5 text-xs font-medium transition"
                    :class="draft.type === option.value
                        ? 'border-teal-500/60 bg-teal-500/12 text-teal-700 dark:text-teal-200'
                        : 'border-border bg-background text-muted-foreground hover:border-ring/40 hover:text-foreground'"
                    :disabled="isSavingDay"
                    @click="draft.type = option.value"
                >
                    {{ i18n.global.t(option.labelKey) }}
                </button>
            </div>

            <label
                v-if="draft.type === 'total_time'"
                class="block space-y-2 text-sm text-muted-foreground"
            >
                <span>{{ i18n.global.t('weekly_schedule.field.goal_hours') }}</span>
                <GoalHoursSelect v-model="draft.expectedTime" />
            </label>

            <div
                v-else-if="draft.type === 'time_range'"
                class="grid gap-4 rounded-2xl border border-border bg-muted/30 p-4 md:grid-cols-2"
            >
                <label class="space-y-2 text-sm text-muted-foreground">
                    <span>{{ i18n.global.t('weekly_schedule.field.starts_at') }}</span>
                    <input
                        v-model="draft.startsAt"
                        type="time"
                        step="300"
                        class="w-full rounded-xl border border-border bg-background px-3 py-2.5 text-sm text-foreground outline-none transition focus:border-teal-500"
                    />
                </label>

                <label class="space-y-2 text-sm text-muted-foreground">
                    <span>{{ i18n.global.t('weekly_schedule.field.ends_at') }}</span>
                    <input
                        v-model="draft.endsAt"
                        type="time"
                        step="300"
                        class="w-full rounded-xl border border-border bg-background px-3 py-2.5 text-sm text-foreground outline-none transition focus:border-teal-500"
                    />
                </label>
            </div>

            <div
                v-else
                class="rounded-2xl border border-dashed border-border bg-muted/30 px-4 py-4 text-sm text-muted-foreground"
            >
                {{ i18n.global.t('weekly_schedule.day_off_hint') }}
            </div>
        </div>

        <div
            v-else
            class="rounded-2xl border border-dashed border-border bg-muted/30 px-4 py-4 text-sm text-muted-foreground"
        >
            {{ hasPersistedDailyWorkSchedule
                ? i18n.global.t('history.dialog.daily_schedule_description')
                : i18n.global.t('history.dialog.daily_schedule_empty') }}
        </div>
    </section>
</template>
