<script setup lang="ts">
import { Trash2 } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { ShiftInRange } from '@/composables/useShiftsInRange';
import {
    formatDateTimeLocalValue,
    getClientDateTimeAtomFromLocalValue,
} from '@/lib/clientDateTime';
import { i18n } from '@/lib/i18n';
import type { DashboardLocale } from '@/lib/i18n';
import { formatDurationMinutes } from '@/lib/time';

const props = defineProps<{
    shift: ShiftInRange;
    locale: DashboardLocale;
    isSaving: boolean;
    isDeleting: boolean;
}>();

const emit = defineEmits<{
    save: [payload: { shiftId: number; startedAt: string; endedAt: string | null }];
    delete: [shiftId: number];
}>();

const startedAt = ref('');
const endedAt = ref('');
const localErrorKey = ref<string | null>(null);

const originalStartedAt = computed(() => formatDateTimeLocalValue(props.shift.started_at));
const originalEndedAt = computed(() =>
    props.shift.ended_at === null ? '' : formatDateTimeLocalValue(props.shift.ended_at),
);
const isDirty = computed(() =>
    startedAt.value !== originalStartedAt.value || endedAt.value !== originalEndedAt.value,
);

watch(
    () => props.shift,
    (shift) => {
        startedAt.value = formatDateTimeLocalValue(shift.started_at);
        endedAt.value = shift.ended_at === null ? '' : formatDateTimeLocalValue(shift.ended_at);
        localErrorKey.value = null;
    },
    { immediate: true, deep: true },
);

function saveShift(): void {
    if (endedAt.value !== '' && endedAt.value <= startedAt.value) {
        localErrorKey.value = 'history.dialog.invalid_period';

        return;
    }

    localErrorKey.value = null;

    emit('save', {
        shiftId: props.shift.id,
        startedAt: getClientDateTimeAtomFromLocalValue(startedAt.value),
        endedAt: endedAt.value === ''
            ? null
            : getClientDateTimeAtomFromLocalValue(endedAt.value),
    });
}
</script>

<template>
    <article class="space-y-4 rounded-2xl border border-border bg-card p-4">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div class="space-y-1">
                <p class="text-sm font-medium text-foreground">
                    {{ i18n.global.t('history.dialog.shift') }} #{{ props.shift.id }}
                </p>
                <p class="text-xs text-muted-foreground">
                    <span v-if="props.shift.duration_minutes !== null">
                        {{ formatDurationMinutes(props.shift.duration_minutes, { suffix: true }) }}
                    </span>
                    <span v-else>
                        {{ i18n.global.t('history.dialog.ongoing') }}
                    </span>
                </p>
            </div>

            <Button
                variant="destructive"
                size="sm"
                class="rounded-full"
                :disabled="props.isDeleting || props.isSaving"
                @click="emit('delete', props.shift.id)"
            >
                <Trash2 class="size-4" />
                <span>
                    {{ props.isDeleting
                        ? i18n.global.t('history.dialog.deleting')
                        : i18n.global.t('history.dialog.delete') }}
                </span>
            </Button>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="space-y-2 text-sm text-muted-foreground">
                <span>{{ i18n.global.t('history.dialog.start') }}</span>
                <Input
                    v-model="startedAt"
                    type="datetime-local"
                    class="border-border bg-background text-foreground"
                />
            </label>

            <label class="space-y-2 text-sm text-muted-foreground">
                <span>{{ i18n.global.t('history.dialog.end') }}</span>
                <Input
                    v-model="endedAt"
                    type="datetime-local"
                    class="border-border bg-background text-foreground"
                />
            </label>
        </div>

        <p
            v-if="localErrorKey"
            class="rounded-xl border border-rose-500/20 bg-rose-500/10 px-3 py-2 text-sm text-rose-700 dark:text-rose-200"
        >
            {{ i18n.global.t(localErrorKey) }}
        </p>

        <div class="flex justify-end">
            <Button
                size="sm"
                class="rounded-full"
                :disabled="! isDirty || props.isSaving || props.isDeleting"
                @click="saveShift"
            >
                {{ props.isSaving
                    ? i18n.global.t('history.dialog.saving')
                    : i18n.global.t('history.dialog.save') }}
            </Button>
        </div>
    </article>
</template>
