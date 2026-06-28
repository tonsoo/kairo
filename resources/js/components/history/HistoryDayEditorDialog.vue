<script setup lang="ts">
import { computed } from 'vue';
import HistoryShiftEditorRow from '@/components/history/HistoryShiftEditorRow.vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import type { ShiftInRange } from '@/composables/useShiftsInRange';
import {
    formatHistoryDayHeading,
    formatHistoryDaySubheading,
} from '@/lib/history';
import type { HistoryDaySummary } from '@/lib/history';
import { i18n } from '@/lib/i18n';
import type { DashboardLocale } from '@/lib/i18n';
import { formatDurationMinutes } from '@/lib/time';

const props = defineProps<{
    open: boolean;
    locale: DashboardLocale;
    selectedDate: string | null;
    selectedDaySummary: HistoryDaySummary | null;
    shifts: ShiftInRange[];
    isLoading: boolean;
    savingShiftId: number | null;
    deletingShiftId: number | null;
    removingBreakKey: string | null;
    errorMessageKey: string | null;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    'save-shift': [payload: { shiftId: number; startedAt: string; endedAt: string | null }];
    'delete-shift': [shiftId: number];
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

function getBreakDurationMinutes(
    previousShift: ShiftInRange,
    nextShift: ShiftInRange,
): number {
    if (previousShift.ended_at === null) {
        return 0;
    }

    const previousEndedAt = new Date(previousShift.ended_at).getTime();
    const nextStartedAt = new Date(nextShift.started_at).getTime();

    return Math.max(0, Math.floor((nextStartedAt - previousEndedAt) / 60000));
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
                    v-if="props.isLoading"
                    class="rounded-2xl border border-border bg-muted/40 px-4 py-3 text-sm text-muted-foreground"
                >
                    {{ i18n.global.t('history.dialog.loading') }}
                </p>

                <div v-else-if="props.shifts.length === 0" class="rounded-2xl border border-dashed border-border px-4 py-10 text-center text-sm text-muted-foreground">
                    {{ i18n.global.t('history.dialog.empty') }}
                </div>

                <div v-else class="max-h-[65vh] space-y-4 overflow-y-auto pr-1">
                    <template v-for="(shift, index) in props.shifts" :key="shift.id">
                        <HistoryShiftEditorRow
                            :shift="shift"
                            :locale="props.locale"
                            :is-saving="props.savingShiftId === shift.id"
                            :is-deleting="props.deletingShiftId === shift.id"
                            @save="emit('save-shift', $event)"
                            @delete="emit('delete-shift', $event)"
                        />

                        <div
                            v-if="index < props.shifts.length - 1"
                            class="flex justify-center"
                        >
                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-4 py-2 text-sm text-amber-700 transition-colors hover:bg-amber-500/20 dark:text-amber-100"
                                :disabled="props.removingBreakKey === `${shift.id}:${props.shifts[index + 1].id}`"
                                @click="emit('remove-break', {
                                    previousShiftId: shift.id,
                                    nextShiftId: props.shifts[index + 1].id,
                                })"
                            >
                                <span>{{ i18n.global.t('history.dialog.break') }}</span>
                                <span class="font-medium">
                                    {{ formatDurationMinutes(getBreakDurationMinutes(shift, props.shifts[index + 1]), { suffix: true }) }}
                                </span>
                                <span>
                                    {{ props.removingBreakKey === `${shift.id}:${props.shifts[index + 1].id}`
                                        ? i18n.global.t('history.dialog.removing_break')
                                        : i18n.global.t('history.dialog.remove_break') }}
                                </span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
