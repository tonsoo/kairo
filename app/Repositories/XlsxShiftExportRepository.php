<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Domain\Shift\DTOs\ShiftExportData;
use App\Domain\Shift\Enums\ShiftExportType;
use App\Support\Exports\ShiftExportFormatter;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

final class XlsxShiftExportRepository implements ShiftExportRepository
{
    public function type(): ShiftExportType
    {
        return ShiftExportType::Xlsx;
    }

    public function labelKey(): string
    {
        return 'exports.type.xlsx';
    }

    public function extension(): string
    {
        return 'xlsx';
    }

    public function mimeType(): string
    {
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }

    public function export(ShiftExportData $data, string $locale): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle(ShiftExportFormatter::sheetName($locale));

        $sheet->mergeCells('A1:C1');
        $sheet->setCellValueExplicit('A1', ShiftExportFormatter::title($locale), DataType::TYPE_STRING);
        $sheet->mergeCells('A2:C2');
        $sheet->setCellValueExplicit('A2', ShiftExportFormatter::periodLabel($data, $locale), DataType::TYPE_STRING);
        $sheet->mergeCells('A3:C3');
        $sheet->setCellValueExplicit('A3', ShiftExportFormatter::timezoneLabel($data, $locale), DataType::TYPE_STRING);

        $headings = ShiftExportFormatter::headings($locale);
        $sheet->setCellValueExplicit('A5', $headings['weekday'], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B5', $headings['date'], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C5', $headings['duration'], DataType::TYPE_STRING);

        $row = 6;

        foreach (ShiftExportFormatter::buildDayRows($data, $locale) as $day) {
            $sheet->setCellValueExplicit("A{$row}", $day['weekday'], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("B{$row}", $day['date'], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$row}", $day['duration'], DataType::TYPE_STRING);
            $row++;
        }

        $row++;

        foreach (ShiftExportFormatter::buildSummaryRows($data, $locale) as $summary) {
            $sheet->setCellValueExplicit("B{$row}", $summary['label'], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$row}", $summary['duration'], DataType::TYPE_STRING);
            $row++;
        }

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2:A3')->getFont()->setSize(10);
        $sheet->getStyle('A5:C5')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'F8FAFC'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F766E'],
            ],
        ]);
        $sheet->getStyle("A5:C".($row - 1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("C6:C".($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("A5:C".($row - 1))->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(16);

        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $spreadsheet->disconnectWorksheets();

        if (! is_string($content)) {
            throw new RuntimeException('Unable to create XLSX shift export.');
        }

        return $content;
    }
}
