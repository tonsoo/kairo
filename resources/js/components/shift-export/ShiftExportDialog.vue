<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
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
import {
    getCurrentClientLocalDate,
    getCurrentClientTimezone,
} from '@/lib/clientDateTime';
import { translateDashboard } from '@/lib/dashboardTranslations';
import type { DashboardLocale } from '@/lib/dashboardTranslations';
import { downloadShiftExportFile } from '@/lib/shiftExport';
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

const selectedType = ref('');
const from = ref('');
const to = ref('');
const isDownloading = ref(false);
const generalErrorMessage = ref<string | null>(null);
const errors = reactive<{
    type: string | null;
    from: string | null;
    to: string | null;
}>({
    type: null,
    from: null,
    to: null,
});

const formattedRange = computed(() => {
    return `${formatLocalDate(props.initialFrom, props.locale)} - ${formatLocalDate(props.initialTo, props.locale)}`;
});

watch(
    () => props.open,
    (isOpen) => {
        if (! isOpen) {
            return;
        }

        selectedType.value = props.formats[0]?.key ?? '';
        from.value = props.initialFrom;
        to.value = props.initialTo;
        resetErrors();
    },
    { immediate: true },
);

async function handleDownload(): Promise<void> {
    resetErrors();

    if (! validate()) {
        return;
    }

    isDownloading.value = true;

    const result = await downloadShiftExportFile({
        type: selectedType.value,
        from: from.value,
        to: to.value,
        timezone: getCurrentClientTimezone(),
    });

    isDownloading.value = false;

    if (! result.success) {
        generalErrorMessage.value = result.message ?? translateDashboard('exports.error.generic', props.locale);
        errors.type = result.errors.type ?? null;
        errors.from = result.errors.from ?? null;
        errors.to = result.errors.to ?? null;

        return;
    }

    emit('update:open', false);
}

function validate(): boolean {
    let isValid = true;
    const today = getCurrentClientLocalDate();

    if (selectedType.value.length === 0) {
        errors.type = translateDashboard('exports.error.type_required', props.locale);
        isValid = false;
    }

    if (from.value.length === 0) {
        errors.from = translateDashboard('exports.error.from_required', props.locale);
        isValid = false;
    }

    if (to.value.length === 0) {
        errors.to = translateDashboard('exports.error.to_required', props.locale);
        isValid = false;
    }

    if (! isValid) {
        return false;
    }

    if (to.value < from.value) {
        errors.to = translateDashboard('exports.error.range_order', props.locale);
        isValid = false;
    }

    if (from.value > today) {
        errors.from = translateDashboard('exports.error.range_future', props.locale);
        isValid = false;
    }

    if (to.value > today) {
        errors.to = translateDashboard('exports.error.range_future', props.locale);
        isValid = false;
    }

    if (props.editableRange && isRangeLongerThanSixMonths(from.value, to.value)) {
        errors.to = translateDashboard('exports.error.range_limit', props.locale);
        isValid = false;
    }

    return isValid;
}

function resetErrors(): void {
    generalErrorMessage.value = null;
    errors.type = null;
    errors.from = null;
    errors.to = null;
}

function isRangeLongerThanSixMonths(fromDate: string, toDate: string): boolean {
    const startsAt = new Date(`${fromDate}T00:00:00`);
    const endsAt = new Date(`${toDate}T00:00:00`);
    const latestAllowedDate = new Date(`${fromDate}T00:00:00`);

    latestAllowedDate.setMonth(latestAllowedDate.getMonth() + 6);

    return endsAt > latestAllowedDate || startsAt.toString() === 'Invalid Date' || endsAt.toString() === 'Invalid Date';
}

function formatLocalDate(value: string, locale: DashboardLocale): string {
    const [year, month, day] = value.split('-').map(Number);
    const date = new Date(Date.UTC(year, month - 1, day));

    return new Intl.DateTimeFormat(locale, {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        timeZone: 'UTC',
    }).format(date);
}
</script>

<template>
    <Dialog :open="props.open" @update:open="emit('update:open', $event)">
        <DialogContent class="border-[#2f3033] bg-[#161719] text-slate-100 sm:max-w-lg">
            <DialogHeader class="space-y-2 text-left">
                <DialogTitle class="text-xl font-semibold text-slate-50">
                    {{ translateDashboard(props.titleKey, props.locale) }}
                </DialogTitle>
                <DialogDescription class="text-slate-400">
                    {{ translateDashboard(props.descriptionKey, props.locale) }}
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
                    <Label for="shift-export-type">{{ translateDashboard('exports.field.type', props.locale) }}</Label>
                    <Select v-model="selectedType">
                        <SelectTrigger id="shift-export-type" class="h-11 w-full rounded-xl border-[#3a3b3c] bg-[#18191a] px-3 text-sm text-slate-100">
                            <SelectValue :placeholder="translateDashboard('exports.field.type_placeholder', props.locale)" />
                        </SelectTrigger>
                        <SelectContent class="border-[#2e2f30] bg-[#18191a] text-slate-100">
                            <SelectItem
                                v-for="format in props.formats"
                                :key="format.key"
                                :value="format.key"
                                class="focus:bg-white/8 focus:text-slate-100"
                            >
                                {{ translateDashboard(format.label_key, props.locale) }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="errors.type ?? undefined" />
                </div>

                <div v-if="props.editableRange" class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="shift-export-from">{{ translateDashboard('exports.field.from', props.locale) }}</Label>
                        <Input id="shift-export-from" v-model="from" type="date" class="border-[#3a3b3c] bg-[#18191a] text-slate-100" />
                        <InputError :message="errors.from ?? undefined" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="shift-export-to">{{ translateDashboard('exports.field.to', props.locale) }}</Label>
                        <Input id="shift-export-to" v-model="to" type="date" class="border-[#3a3b3c] bg-[#18191a] text-slate-100" />
                        <InputError :message="errors.to ?? undefined" />
                    </div>
                </div>

                <div v-else class="space-y-2 rounded-2xl border border-[#313234] bg-[#18191a] px-4 py-3">
                    <p class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">
                        {{ translateDashboard('exports.field.fixed_range', props.locale) }}
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
                    {{ translateDashboard('shared.actions.cancel', props.locale) }}
                </Button>
                <Button type="button" :disabled="isDownloading || props.formats.length === 0" @click="void handleDownload()">
                    {{ isDownloading
                        ? translateDashboard('exports.action.downloading', props.locale)
                        : translateDashboard('exports.action.download', props.locale) }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
