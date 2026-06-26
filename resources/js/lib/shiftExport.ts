import { download as downloadShiftExportRoute } from '@/routes/shift-exports';

export type ShiftExportFormatOption = {
    key: string;
    label_key: string;
};

export type ShiftExportDownloadPayload = {
    type: string;
    from: string;
    to: string;
    timezone: string;
};

export type ShiftExportDownloadResult =
    | {
        success: true;
    }
    | {
        success: false;
        message: string | null;
        errors: Partial<Record<'from' | 'to' | 'type', string>>;
    };

export async function downloadShiftExportFile(
    payload: ShiftExportDownloadPayload,
): Promise<ShiftExportDownloadResult> {
    const response = await fetch(
        downloadShiftExportRoute({ query: payload }).url,
        {
            headers: {
                Accept: 'application/json',
            },
            credentials: 'same-origin',
        },
    );

    if (! response.ok) {
        const fallbackResult: ShiftExportDownloadResult = {
            success: false,
            message: null,
            errors: {},
        };
        const contentType = response.headers.get('content-type') ?? '';

        if (! contentType.includes('application/json')) {
            return fallbackResult;
        }

        const data = (await response.json()) as {
            message?: string;
            errors?: Partial<Record<'from' | 'to' | 'type', string[]>>;
        };

        return {
            success: false,
            message: data.message ?? null,
            errors: {
                from: data.errors?.from?.[0],
                to: data.errors?.to?.[0],
                type: data.errors?.type?.[0],
            },
        };
    }

    const blob = await response.blob();
    const downloadUrl = URL.createObjectURL(blob);
    const link = document.createElement('a');

    link.href = downloadUrl;
    link.download = resolveDownloadFilename(response.headers.get('content-disposition'));
    document.body.append(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(downloadUrl);

    return {
        success: true,
    };
}

function resolveDownloadFilename(contentDisposition: string | null): string {
    if (contentDisposition === null) {
        return 'hours-tracker-export.txt';
    }

    const matchedFilename = contentDisposition.match(/filename="?([^";]+)"?/i);

    if (matchedFilename === null) {
        return 'hours-tracker-export.txt';
    }

    return matchedFilename[1] ?? 'hours-tracker-export.txt';
}
