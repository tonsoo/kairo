<script setup lang="ts">
import { computed } from 'vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    buildGoalHoursDuration,
    buildGoalHoursMinutesOptions,
    goalHoursOptions,
    parseGoalHoursDuration,
} from '@/lib/goalHours';
import { i18n } from '@/lib/i18n';

const value = defineModel<string | number | null>({ required: true });

const minutesOptions = computed(() => {
    return buildGoalHoursMinutesOptions(selectedHours.value);
});

const selectedHours = computed({
    get: () => parseGoalHoursDuration(value.value)?.hours ?? '',
    set: (nextHours: string) => {
        const current = parseGoalHoursDuration(value.value);

        value.value = buildGoalHoursDuration(nextHours, current?.minutes ?? '00');
    },
});
const selectedMinutes = computed({
    get: () => parseGoalHoursDuration(value.value)?.minutes ?? '',
    set: (nextMinutes: string) => {
        const current = parseGoalHoursDuration(value.value);

        value.value = buildGoalHoursDuration(
            current?.hours ?? '00',
            nextMinutes,
        );
    },
});
</script>

<template>
    <div class="space-y-2">
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="space-y-2">
                <span class="text-xs font-medium tracking-[0.18em] text-slate-500 uppercase">
                    {{ i18n.global.t('weekly_schedule.field.goal_hours_hours') }}
                </span>
                <Select v-model="selectedHours">
                    <SelectTrigger class="h-11 w-full rounded-xl border-[#3a3b3c] bg-[#18191a] px-3 text-sm text-slate-100">
                        <SelectValue :placeholder="i18n.global.t('weekly_schedule.field.goal_hours_hours')" />
                    </SelectTrigger>
                    <SelectContent class="border-[#2e2f30] bg-[#18191a] text-slate-100">
                        <SelectItem
                            v-for="hoursOption in goalHoursOptions"
                            :key="hoursOption"
                            :value="hoursOption"
                            class="focus:bg-white/8 focus:text-slate-100"
                        >
                            {{ hoursOption }}h
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="space-y-2">
                <span class="text-xs font-medium tracking-[0.18em] text-slate-500 uppercase">
                    {{ i18n.global.t('weekly_schedule.field.goal_hours_minutes') }}
                </span>
                <Select v-model="selectedMinutes">
                    <SelectTrigger class="h-11 w-full rounded-xl border-[#3a3b3c] bg-[#18191a] px-3 text-sm text-slate-100">
                        <SelectValue :placeholder="i18n.global.t('weekly_schedule.field.goal_hours_minutes')" />
                    </SelectTrigger>
                    <SelectContent class="border-[#2e2f30] bg-[#18191a] text-slate-100">
                        <SelectItem
                            v-for="minutesOption in minutesOptions"
                            :key="minutesOption"
                            :value="minutesOption"
                            class="focus:bg-white/8 focus:text-slate-100"
                        >
                            {{ minutesOption }}m
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>
    </div>
</template>
