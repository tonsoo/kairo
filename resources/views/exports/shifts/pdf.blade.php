<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 20mm;
            size: A4 portrait;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111827;
            background: #ffffff;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .page {
            position: relative;
            padding-bottom: 28mm;
        }

        .mono {
            font-family: DejaVu Sans Mono, monospace;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #111827;
            border-collapse: collapse;
            margin-bottom: 36px;
            padding-bottom: 14px;
        }

        .header-layout {
            width: 100%;
            border-collapse: collapse;
        }

        .header-left {
            vertical-align: bottom;
        }

        .header-right {
            width: 42%;
            text-align: right;
            vertical-align: bottom;
        }

        .title {
            margin: 0 0 6px;
            font-size: 28px;
            line-height: 1.05;
            font-weight: 700;
            letter-spacing: -0.04em;
            text-transform: uppercase;
            color: #000000;
        }

        .brand {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.14em;
            color: #6b7280;
        }

        .meta-line {
            margin: 0;
            font-size: 12px;
            line-height: 1.8;
        }

        .meta-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #6b7280;
            margin-right: 8px;
        }

        .meta-value {
            font-weight: 600;
        }

        .export-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        .export-table thead th {
            padding: 12px 0;
            border-bottom: 2px solid #111827;
            color: #111827;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.05em;
            text-align: left;
        }

        .export-table tbody td {
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
            color: #111827;
            font-size: 14px;
        }

        .export-table tbody tr:last-child td {
            border-bottom: none;
        }

        .weekday-cell {
            width: 25%;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #6b7280;
        }

        .date-cell {
            width: 50%;
        }

        .hours-cell {
            width: 25%;
            text-align: right;
            font-weight: 600;
        }

        .summary-wrapper {
            width: 100%;
        }

        .summary-table {
            width: 48%;
            margin-left: auto;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 8px 0;
            font-size: 14px;
        }

        .summary-label {
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #6b7280;
        }

        .summary-value {
            text-align: right;
            font-weight: 600;
        }

        .summary-total td {
            border-top: 2px solid #111827;
            padding-top: 12px;
        }

        .summary-total .summary-label {
            font-size: 13px;
            color: #111827;
        }

        .summary-total .summary-value {
            font-size: 20px;
            font-weight: 700;
            color: #000000;
        }

        .footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            text-align: center;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #d1d5db;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <table class="header-layout">
                <tr>
                    <td class="header-left">
                        <h1 class="title">{{ $documentTitle }}</h1>
                        <div class="brand">SHIFTLY TRACKER</div>
                    </td>
                    <td class="header-right">
                        <p class="meta-line">
                            <span class="meta-label">{{ $periodLabel }}</span>
                            <span class="meta-value mono">{{ $startsAt }} - {{ $endsAt }}</span>
                        </p>
                        <p class="meta-line">
                            <span class="meta-label">{{ $timezoneLabel }}</span>
                            <span class="meta-value">{{ $timezone }}</span>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <table class="export-table">
            <thead>
                <tr>
                    <th>{{ $headings['weekday'] }}</th>
                    <th>{{ $headings['date'] }}</th>
                    <th style="text-align: right;">{{ $headings['duration'] }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dayRows as $day)
                    <tr>
                        <td class="weekday-cell">{{ $day['weekday'] }}</td>
                        <td class="date-cell mono">{{ $day['date'] }}</td>
                        <td class="hours-cell mono">{{ $day['duration'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-wrapper">
            <table class="summary-table">
                <tbody>
                    @foreach ($summaryRows as $summary)
                        <tr class="{{ $summary['is_total'] ? 'summary-total' : '' }}">
                            <td class="summary-label">{{ $summary['label'] }}</td>
                            <td class="summary-value mono">{{ $summary['duration'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">{{ $footerLabel }}</div>
</body>
</html>
