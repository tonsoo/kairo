<?php

declare(strict_types=1);

namespace App\Repositories\ShiftExport;

use App\Domain\Shift\DTOs\ShiftExportData;
use App\Domain\Shift\Enums\ShiftExportType;
use App\Traits\UsesShiftExportLabels;
use App\Traits\UsesShiftExportSummaryRows;
use Dompdf\Dompdf;
use Dompdf\Options;

final class PdfShiftExportRepository implements ShiftExportRepository
{
    use UsesShiftExportLabels, UsesShiftExportSummaryRows;

    public function type(): ShiftExportType
    {
        return ShiftExportType::Pdf;
    }

    public function labelKey(): string
    {
        return 'exports.type.pdf';
    }

    public function extension(): string
    {
        return 'pdf';
    }

    public function mimeType(): string
    {
        return 'application/pdf';
    }

    public function export(ShiftExportData $data): string
    {
        $options = new Options([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
        ]);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('exports.shifts.pdf', $this->viewData($data))->render());
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(ShiftExportData $data): array
    {
        return [
            'documentTitle' => self::exportTitle(),
            'periodLabel' => self::exportPeriodHeading(),
            'timezoneLabel' => self::exportTimezoneHeading(),
            'footerLabel' => self::exportFooterLabel(),
            'headings' => self::exportHeadings(),
            'dayRows' => $data->days,
            'summaryRows' => self::buildSummaryRows($data),
            'startsAt' => $data->startsAt->format('d/m/Y'),
            'endsAt' => $data->endsAt->format('d/m/Y'),
            'timezone' => $data->timezone,
        ];
    }
}
