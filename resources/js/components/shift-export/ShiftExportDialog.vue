<script setup lang="ts">
import { toRef, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { useShiftExportDialog } from '@/components/shift-export/useShiftExportDialog';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { i18n } from '@/lib/i18n';
import type { DashboardLocale } from '@/lib/i18n';
import type { ShiftExportFormatOption } from '@/lib/shiftExport';

const props = defineProps<{
    open: boolean;
    locale: DashboardLocale;
    titleKey: string;
    descriptionKey: string;
    formats: ShiftExportFormatOption[];
    initialFrom: string;
    initialTo: string;
    editableRange: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
}>();

const {
    selectedType,
    from,
    to,
    isDownloading,
    generalErrorMessage,
    errors,
    formattedRange,
    resetForm,
    handleDownload,
} = useShiftExportDialog({
    locale: toRef(props, 'locale'),
    formats: toRef(props, 'formats'),
    initialFrom: toRef(props, 'initialFrom'),
    initialTo: toRef(props, 'initialTo'),
    editableRange: toRef(props, 'editableRange'),
    closeDialog: () => emit('update:open', false),
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            resetForm();
        }
    },
    { immediate: true },
);
</script>

<template>
    <Dialog :open="props.open" @update:open="emit('update:open', $event)">
        <DialogContent class="border-[#2f3033] bg-[#161719] text-slate-100 sm:max-w-lg">
            <DialogHeader class="space-y-2 text-left">
                <DialogTitle class="text-xl font-semibold text-slate-50">
                    {{ i18n.global.t(props.titleKey) }}
                </DialogTitle>
                <DialogDescription class="text-slate-400">
                    {{ i18n.global.t(props.descriptionKey) }}
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-5">
                <p
                    v-if="generalErrorMessage"
                    class="rounded-2xl border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-200"
                >
                    {{ generalErrorMessage }}
                </p>

                <div class="grid gap-2">
                    <Label for="shift-export-type">{{ i18n.global.t('exports.field.type') }}</Label>
                    <Select v-model="selectedType">
                        <SelectTrigger id="shift-export-type" class="h-11 w-full rounded-xl border-[#3a3b3c] bg-[#18191a] px-3 text-sm text-slate-100">
                            <SelectValue :placeholder="i18n.global.t('exports.field.type_placeholder')" />
                        </SelectTrigger>
                        <SelectContent class="border-[#2e2f30] bg-[#18191a] text-slate-100">
                            <SelectItem
                                v-for="format in props.formats"
                                :key="format.key"
                                :value="format.key"
                                class="focus:bg-white/8 focus:text-slate-100"
                            >
                                {{ i18n.global.t(format.label_key) }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.type ?? undefined" />
                </div>

                <div v-if="props.editableRange" class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="shift-export-from">{{ i18n.global.t('exports.field.from') }}</Label>
                        <Input id="shift-export-from" v-model="from" type="date" class="border-[#3a3b3c] bg-[#18191a] text-slate-100" />
                        <InputError :message="errors.from ?? undefined" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="shift-export-to">{{ i18n.global.t('exports.field.to') }}</Label>
                        <Input id="shift-export-to" v-model="to" type="date" class="border-[#3a3b3c] bg-[#18191a] text-slate-100" />
                        <InputError :message="errors.to ?? undefined" />
                    </div>
                </div>

                <div v-else class="space-y-2 rounded-2xl border border-[#313234] bg-[#18191a] px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">
                        {{ i18n.global.t('exports.field.fixed_range') }}
                    </p>
                    <p class="text-sm text-slate-100">
                        {{ formattedRange }}
                    </p>
                </div>
            </div>

            <DialogFooter class="gap-2 sm:justify-end">
                <Button
                    type="button"
                    variant="ghost"
                    class="border border-[#313234] bg-[#18191a] text-slate-300 hover:bg-[#242526] hover:text-slate-100"
                    @click="emit('update:open', false)"
                >
                    {{ i18n.global.t('shared.actions.cancel') }}
                </Button>
                <Button type="button" :disabled="isDownloading || props.formats.length === 0" @click="void handleDownload()">
                    {{ isDownloading
                        ? i18n.global.t('exports.action.downloading')
                        : i18n.global.t('exports.action.download') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
