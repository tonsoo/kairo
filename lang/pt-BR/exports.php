<?php

declare(strict_types=1);

return [
    'type' => [
        'csv' => 'CSV',
        'xlsx' => 'Excel',
        'pdf' => 'PDF',
    ],
    'shift' => [
        'title' => 'Exportação de horas',
        'sheet_name' => 'Horas',
        'period' => 'Período',
        'timezone' => 'Fuso horário',
        'footer' => 'Gerado por Kairo',
        'headings' => [
            'weekday' => 'Dia',
            'date' => 'Data',
            'duration' => 'Horas',
        ],
        'summary' => [
            'total' => 'TOTAL',
            'regular' => 'Normais',
            'extra' => 'Extras',
            'missing' => 'Faltando',
        ],
    ],
];
