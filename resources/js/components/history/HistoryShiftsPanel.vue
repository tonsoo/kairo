<script setup lang="ts">
import { Plus } from '@lucide/vue';
import HistoryShiftEditorRow from '@/components/history/HistoryShiftEditorRow.vue';
import { Button } from '@/components/ui/button';
import type { ShiftInRange } from '@/composables/useShiftsInRange';
import type { HistoryShiftDraft } from '@/lib/history';
import { i18n } from '@/lib/i18n';
import { formatDurationMinutes } from '@/lib/time';

const shiftDrafts = defineModel<HistoryShiftDraft[]>('shiftDrafts', { required: true });

const props = defineProps<{
    selectedDate: string | null;
    shifts: ShiftInRange[];
    isSavingDay: boolean;
    removingBreakKey: string | null;
    canRemoveBreaks: boolean;
    hasUnsavedShiftChanges: boolean;
}>();

const emit = defineEmits<{
    'remove-break': [payload: { previousShiftId: number; nextShiftId: number }];
}>();

let nextDraftIndex = 0;

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

function addShift(): void {
    if (props.selectedDate === null) {
        return;
    }

    nextDraftIndex += 1;
    shiftDrafts.value.push({
        id: null,
        key: `new-${nextDraftIndex}`,
        started_at: `${props.selectedDate}T09:00`,
        ended_at: `${props.selectedDate}T18:00`,
    });
}

function removeShift(index: number): void {
    shiftDrafts.value.splice(index, 1);
}
</script>

<template>
    <section class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-medium text-foreground">
                    {{ i18n.global.t('history.dialog.shifts') }}
                </h3>
                <p class="text-sm text-muted-foreground">
                    {{ i18n.global.t('history.dialog.shifts_description') }}
                </p>
            </div>

            <Button
                type="button"
                size="sm"
                class="rounded-full"
                :disabled="isSavingDay"
                @click="addShift"
            >
                <Plus class="mr-1 size-4" />
                {{ i18n.global.t('history.dialog.add_shift') }}
            </Button>
        </div>

        <div v-if="shiftDrafts.length === 0" class="rounded-2xl border border-dashed border-border px-4 py-10 text-center text-sm text-muted-foreground">
            {{ i18n.global.t('history.dialog.empty') }}
        </div>

        <div v-else class="max-h-[40vh] space-y-4 overflow-y-auto pr-1">
            <template v-for="(shift, index) in shiftDrafts" :key="shift.key">
                <HistoryShiftEditorRow
                    v-model:shift="shiftDrafts[index]"
                    :is-disabled="isSavingDay"
                    @remove="removeShift(index)"
                />
            </template>
        </div>

        <div v-if="!hasUnsavedShiftChanges">
            <template v-for="(shift, index) in shifts" :key="`break-${shift.id}`">
                <div
                    v-if="index < shifts.length - 1"
                    class="flex justify-center"
                >
                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-4 py-2 text-sm text-amber-700 transition-colors hover:bg-amber-500/20 dark:text-amber-100"
                        :disabled="!canRemoveBreaks || removingBreakKey === `${shift.id}:${shifts[index + 1].id}`"
                        @click="emit('remove-break', {
                            previousShiftId: shift.id,
                            nextShiftId: shifts[index + 1].id,
                        })"
                    >
                        <span>{{ i18n.global.t('history.dialog.break') }}</span>
                        <span class="font-medium">
                            {{ formatDurationMinutes(getBreakDurationMinutes(shift, shifts[index + 1]), { suffix: true }) }}
                        </span>
                        <span>
                            {{ removingBreakKey === `${shift.id}:${shifts[index + 1].id}`
                                ? i18n.global.t('history.dialog.removing_break')
                                : i18n.global.t('history.dialog.remove_break') }}
                        </span>
                    </button>
                </div>
            </template>
        </div>
    </section>
</template>
