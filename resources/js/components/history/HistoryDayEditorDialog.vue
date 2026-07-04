<script setup lang="ts">
import { computed } from 'vue';
import HistoryDailySchedulePanel from '@/components/history/HistoryDailySchedulePanel.vue';
import HistoryDayEditorTabs from '@/components/history/HistoryDayEditorTabs.vue';
import HistoryShiftsPanel from '@/components/history/HistoryShiftsPanel.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useHistoryDayEditor } from '@/composables/useHistoryDayEditor';
import type { ShiftInRange } from '@/composables/useShiftsInRange';
import {
    formatHistoryDayHeading,
    formatHistoryDaySubheading,
} from '@/lib/history';
import type {
    DailyWorkScheduleApiData,
    HistoryDaySummary,
} from '@/lib/history';
import { i18n } from '@/lib/i18n';
import type { DashboardLocale } from '@/lib/i18n';
import { formatDurationMinutes } from '@/lib/time';
import type { WorkScheduleType } from '@/lib/weeklySchedule';

const props = defineProps<{
    open: boolean;
    locale: DashboardLocale;
    selectedDate: string | null;
    selectedDaySummary: HistoryDaySummary | null;
    shifts: ShiftInRange[];
    dailyWorkSchedule: DailyWorkScheduleApiData | null;
    isLoading: boolean;
    isSavingDay: boolean;
    removingBreakKey: string | null;
    errorMessageKey: string | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'save-day': [payload: {
        shifts: Array<{ id: number | null; startedAt: string; endedAt: string | null }>;
        dailyWorkSchedule: {
            type: WorkScheduleType;
            expectedMinutes: number | null;
            startsAt: string | null;
            endsAt: string | null;
        } | null;
    }];
    'remove-break': [payload: { previousShiftId: number; nextShiftId: number }];
}>();

const dayHeading = computed(() =>
    props.selectedDate === null
        ? ''
        : formatHistoryDayHeading(props.selectedDate, props.locale),
);
const daySubheading = computed(() =>
    props.selectedDate === null
        ? ''
        : formatHistoryDaySubheading(props.selectedDate, props.locale),
);

const {
    activeTab,
    shiftDrafts,
    dailyScheduleDraft,
    localErrorKey,
    hasPersistedDailyWorkSchedule,
    hasUnsavedShiftChanges,
    hasUnsavedChanges,
    canRemoveBreaks,
    buildSavePayload,
} = useHistoryDayEditor(props);

function saveDay(): void {
    const payload = buildSavePayload();

    if (payload === null) {
        return;
    }

    emit('save-day', payload);
}
</script>

<template>
    <Dialog :open="props.open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-4xl border-border bg-background pt-12 text-foreground sm:max-w-4xl">
            <DialogHeader class="space-y-3 text-left">
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div class="space-y-1">
                        <DialogTitle class="text-xl font-semibold text-foreground">
                            {{ dayHeading }}
                        </DialogTitle>
                        <DialogDescription class="text-muted-foreground">
                            {{ daySubheading }} •
                            {{ i18n.global.t('history.dialog.description') }}
                        </DialogDescription>
                    </div>

                    <div
                        v-if="props.selectedDaySummary !== null"
                        class="rounded-full border border-teal-500/20 bg-teal-500/10 px-4 py-2 text-sm text-teal-700 dark:text-teal-200"
                    >
                        {{ i18n.global.t('history.day.worked') }}
                        <span class="ml-2 font-semibold text-foreground">
                            {{ formatDurationMinutes(props.selectedDaySummary.workedMinutes, { suffix: true }) }}
                        </span>
                    </div>
                </div>
            </DialogHeader>

            <div class="space-y-4">
                <p
                    v-if="props.errorMessageKey"
                    class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-700 dark:text-rose-200"
                >
                    {{ i18n.global.t(props.errorMessageKey) }}
                </p>

                <p
                    v-if="localErrorKey"
                    class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-700 dark:text-rose-200"
                >
                    {{ i18n.global.t(localErrorKey) }}
                </p>

                <p
                    v-if="props.isLoading"
                    class="rounded-2xl border border-border bg-muted/40 px-4 py-3 text-sm text-muted-foreground"
                >
                    {{ i18n.global.t('history.dialog.loading') }}
                </p>

                <div v-else class="space-y-6">
                    <HistoryDayEditorTabs
                        v-model:active-tab="activeTab"
                        :is-saving-day="props.isSavingDay"
                    />

                    <HistoryDailySchedulePanel
                        v-if="activeTab === 'daily-schedule'"
                        v-model:draft="dailyScheduleDraft"
                        :has-persisted-daily-work-schedule="hasPersistedDailyWorkSchedule"
                        :is-saving-day="props.isSavingDay"
                    />

                    <HistoryShiftsPanel
                        v-else
                        v-model:shift-drafts="shiftDrafts"
                        :selected-date="props.selectedDate"
                        :shifts="props.shifts"
                        :is-saving-day="props.isSavingDay"
                        :removing-break-key="props.removingBreakKey"
                        :can-remove-breaks="canRemoveBreaks"
                        :has-unsaved-shift-changes="hasUnsavedShiftChanges"
                        @remove-break="emit('remove-break', $event)"
                    />
                </div>
            </div>

            <DialogFooter class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                <p v-if="hasUnsavedChanges" class="text-sm text-muted-foreground sm:mr-auto">
                    {{ i18n.global.t('history.dialog.unsaved_changes') }}
                </p>
                <Button
                    type="button"
                    class="rounded-full"
                    :disabled="props.isLoading || props.isSavingDay || !hasUnsavedChanges"
                    @click="saveDay"
                >
                    {{ props.isSavingDay
                        ? i18n.global.t('history.dialog.saving_day')
                        : i18n.global.t('history.dialog.save_day') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
