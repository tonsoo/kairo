import type { Ref } from 'vue';
import { computed, reactive, ref } from 'vue';
import {
    getCurrentClientLocalDate,
    getCurrentClientTimezone,
} from '@/lib/clientDateTime';
import type { DashboardLocale } from '@/lib/i18n';
import { i18n } from '@/lib/i18n';
import type { ShiftExportFormatOption } from '@/lib/shiftExport';
import { downloadShiftExportFile } from '@/lib/shiftExport';

type ShiftExportDialogErrors = {
    type: string | null;
    from: string | null;
    to: string | null;
};

type UseShiftExportDialogOptions = {
    locale: Ref<DashboardLocale>;
    formats: Ref<ShiftExportFormatOption[]>;
    initialFrom: Ref<string>;
    initialTo: Ref<string>;
    editableRange: Ref<boolean>;
    closeDialog: () => void;
};

export const useShiftExportDialog = ({
    locale,
    formats,
    initialFrom,
    initialTo,
    editableRange,
    closeDialog,
}: UseShiftExportDialogOptions) => {
    const selectedType = ref('');
    const from = ref('');
    const to = ref('');
    const isDownloading = ref(false);
    const generalErrorMessage = ref<string | null>(null);
    const errors = reactive<ShiftExportDialogErrors>({
        type: null,
        from: null,
        to: null,
    });

    const formattedRange = computed(
        () =>
            `${formatShiftExportDate(initialFrom.value, locale.value)} - ${formatShiftExportDate(initialTo.value, locale.value)}`,
    );

    const resetForm = (): void => {
        selectedType.value = formats.value[0]?.key ?? '';
        from.value = initialFrom.value;
        to.value = initialTo.value;
        resetErrors();
    };

    const handleDownload = async (): Promise<void> => {
        resetErrors();

        if (!validate()) {
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

        if (!result.success) {
            generalErrorMessage.value =
                result.message ?? i18n.global.t('exports.error.generic');
            errors.type = result.errors.type ?? null;
            errors.from = result.errors.from ?? null;
            errors.to = result.errors.to ?? null;

            return;
        }

        closeDialog();
    };

    const validate = (): boolean => {
        let isValid = true;
        const today = getCurrentClientLocalDate();

        if (selectedType.value.length === 0) {
            errors.type = i18n.global.t('exports.error.type_required');
            isValid = false;
        }

        if (from.value.length === 0) {
            errors.from = i18n.global.t('exports.error.from_required');
            isValid = false;
        }

        if (to.value.length === 0) {
            errors.to = i18n.global.t('exports.error.to_required');
            isValid = false;
        }

        if (!isValid) {
            return false;
        }

        if (to.value < from.value) {
            errors.to = i18n.global.t('exports.error.range_order');
            isValid = false;
        }

        if (from.value > today) {
            errors.from = i18n.global.t('exports.error.range_future');
            isValid = false;
        }

        if (to.value > today) {
            errors.to = i18n.global.t('exports.error.range_future');
            isValid = false;
        }

        if (
            editableRange.value
            && isShiftExportRangeLongerThanSixMonths(from.value, to.value)
        ) {
            errors.to = i18n.global.t('exports.error.range_limit');
            isValid = false;
        }

        return isValid;
    };

    const resetErrors = (): void => {
        generalErrorMessage.value = null;
        errors.type = null;
        errors.from = null;
        errors.to = null;
    };

    return {
        selectedType,
        from,
        to,
        isDownloading,
        generalErrorMessage,
        errors,
        formattedRange,
        resetForm,
        handleDownload,
    };
};

function isShiftExportRangeLongerThanSixMonths(
    fromDate: string,
    toDate: string,
): boolean {
    const startsAt = new Date(`${fromDate}T00:00:00`);
    const endsAt = new Date(`${toDate}T00:00:00`);
    const latestAllowedDate = new Date(`${fromDate}T00:00:00`);

    latestAllowedDate.setMonth(latestAllowedDate.getMonth() + 6);

    return (
        endsAt > latestAllowedDate
        || startsAt.toString() === 'Invalid Date'
        || endsAt.toString() === 'Invalid Date'
    );
}

function formatShiftExportDate(
    value: string,
    locale: DashboardLocale,
): string {
    const [year, month, day] = value.split('-').map(Number);
    const date = new Date(Date.UTC(year, month - 1, day));

    return new Intl.DateTimeFormat(locale, {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        timeZone: 'UTC',
    }).format(date);
}
