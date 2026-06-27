<script setup lang="ts">
import { computed, watch } from 'vue';
import GoalHoursSelect from '@/components/weekly-schedule/GoalHoursSelect.vue';
import { getDashboardLocale, i18n } from '@/lib/i18n';
import {
    isWeekendWeekday,
    weekdayLabel,
} from '@/lib/weeklySchedule';
import type {
    WeeklyScheduleFormRow,
    WorkScheduleType,
} from '@/lib/weeklySchedule';

const row = defineModel<WeeklyScheduleFormRow>('row', { required: true });

defineProps<{
    expectedTimeError?: string | null;
    startsAtError?: string | null;
    endsAtError?: string | null;
}>();

const locale = getDashboardLocale();

const dayTypeOptions: Array<{ value: WorkScheduleType; labelKey: string }> = [
    { value: 'day_off', labelKey: 'weekly_schedule.type.day_off' },
    { value: 'total_time', labelKey: 'weekly_schedule.type.total_time' },
    { value: 'time_range', labelKey: 'weekly_schedule.type.time_range' },
];

const label = computed(() => weekdayLabel(row.value.weekday, locale));
const subtitle = computed(() => i18n.global.t(
    isWeekendWeekday(row.value.weekday)
        ? 'weekly_schedule.day.weekend'
        : 'weekly_schedule.day.weekday',
    locale,
));

watch(() => row.value.type, (type) => {
    if (type === 'day_off') {
        row.value.expected_time = '';
        row.value.starts_at = '';
        row.value.ends_at = '';

        return;
    }

    if (type === 'total_time') {
        row.value.starts_at = '';
        row.value.ends_at = '';

        return;
    }

    row.value.expected_time = '';
});
</script>

<template>
    <div class="grid gap-5 rounded-2xl border border-[#2e2f30] bg-[#1f2022] p-5 xl:grid-cols-[11rem_minmax(0,18rem)_minmax(0,1fr)]">
        <div class="space-y-1">
            <p class="text-sm font-medium text-slate-100">{{ label }}</p>
            <p class="text-xs text-slate-500">{{ subtitle }}</p>
        </div>

        <div class="space-y-2">
            <span class="text-sm text-slate-300">{{ i18n.global.t('weekly_schedule.field.day_type') }}</span>
            <div class="grid grid-cols-3 gap-2">
                <button
                    v-for="option in dayTypeOptions"
                    :key="option.value"
                    type="button"
                    class="rounded-xl border px-3 py-2.5 text-xs font-medium transition"
                    :class="row.type === option.value
                        ? 'border-teal-500/60 bg-teal-500/12 text-teal-200'
                        : 'border-[#3a3b3c] bg-[#18191a] text-slate-300 hover:border-slate-500 hover:text-slate-100'"
                    @click="row.type = option.value"
                >
                    {{ i18n.global.t(option.labelKey) }}
                </button>
            </div>
        </div>

        <div class="min-w-0">
            <label
                v-if="row.type === 'total_time'"
                class="block space-y-2 text-sm text-slate-300"
            >
                <span>{{ i18n.global.t('weekly_schedule.field.goal_hours') }}</span>
                <GoalHoursSelect v-model="row.expected_time" />
                <p v-if="expectedTimeError" class="text-xs text-rose-300">{{ expectedTimeError }}</p>
            </label>

            <div
                v-else-if="row.type === 'time_range'"
                class="grid gap-4 rounded-2xl border border-[#2a2b2d] bg-[#18191a] p-4 md:grid-cols-2"
            >
                <label class="space-y-2 text-sm text-slate-300">
                    <span>{{ i18n.global.t('weekly_schedule.field.starts_at') }}</span>
                    <input
                        v-model="row.starts_at"
                        type="time"
                        step="300"
                        class="w-full rounded-xl border border-[#3a3b3c] bg-[#141517] px-3 py-2.5 text-sm text-slate-100 outline-none transition focus:border-teal-500"
                    />
                    <p v-if="startsAtError" class="text-xs text-rose-300">{{ startsAtError }}</p>
                </label>

                <label class="space-y-2 text-sm text-slate-300">
                    <span>{{ i18n.global.t('weekly_schedule.field.ends_at') }}</span>
                    <input
                        v-model="row.ends_at"
                        type="time"
                        step="300"
                        class="w-full rounded-xl border border-[#3a3b3c] bg-[#141517] px-3 py-2.5 text-sm text-slate-100 outline-none transition focus:border-teal-500"
                    />
                    <p v-if="endsAtError" class="text-xs text-rose-300">{{ endsAtError }}</p>
                </label>
            </div>

            <div
                v-else
                class="rounded-2xl border border-dashed border-[#3a3b3c] bg-[#18191a] px-4 py-4 text-sm text-slate-400"
            >
                {{ i18n.global.t('weekly_schedule.day_off_hint') }}
            </div>
        </div>
    </div>
</template>
