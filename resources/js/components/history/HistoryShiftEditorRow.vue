<script setup lang="ts">
import { Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { HistoryShiftDraft } from '@/lib/history';
import { i18n } from '@/lib/i18n';
import { formatDurationMinutes } from '@/lib/time';

const shift = defineModel<HistoryShiftDraft>('shift', { required: true });

defineProps<{
    isDisabled: boolean;
}>();

const emit = defineEmits<{
    remove: [];
}>();

const durationLabel = computed(() => {
    if (shift.value.ended_at === '') {
        return i18n.global.t('history.dialog.ongoing');
    }

    const startedAt = new Date(shift.value.started_at).getTime();
    const endedAt = new Date(shift.value.ended_at).getTime();

    if (Number.isNaN(startedAt) || Number.isNaN(endedAt) || endedAt <= startedAt) {
        return i18n.global.t('history.dialog.invalid_period');
    }

    return formatDurationMinutes(Math.floor((endedAt - startedAt) / 60000), {
        suffix: true,
    });
});
</script>

<template>
    <article class="space-y-4 rounded-2xl border border-border bg-card p-4">
        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
            <div class="space-y-1">
                <p class="text-sm font-medium text-foreground">
                    {{ shift.id === null
                        ? i18n.global.t('history.dialog.new_shift')
                        : `${i18n.global.t('history.dialog.shift')} #${shift.id}` }}
                </p>
                <p class="text-xs text-muted-foreground">
                    {{ durationLabel }}
                </p>
            </div>

            <Button
                variant="destructive"
                size="sm"
                class="rounded-full"
                :disabled="isDisabled"
                @click="emit('remove')"
            >
                <Trash2 class="size-4" />
                <span>{{ i18n.global.t('history.dialog.remove_shift') }}</span>
            </Button>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <label class="space-y-2 text-sm text-muted-foreground">
                <span>{{ i18n.global.t('history.dialog.start') }}</span>
                <Input
                    v-model="shift.started_at"
                    type="datetime-local"
                    class="border-border bg-background text-foreground"
                    :disabled="isDisabled"
                />
            </label>

            <label class="space-y-2 text-sm text-muted-foreground">
                <span>{{ i18n.global.t('history.dialog.end') }}</span>
                <Input
                    v-model="shift.ended_at"
                    type="datetime-local"
                    class="border-border bg-background text-foreground"
                    :disabled="isDisabled"
                />
            </label>
        </div>
    </article>
</template>
