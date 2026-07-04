<?php

declare(strict_types=1);

use App\Models\Shift;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Support\Localization\LocalizedUrlGenerator;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

test('guests cannot download shift exports', function () {
    $this->get(route('shift-exports.download', [
        'type' => 'csv',
        'from' => '2026-05-04',
        'to' => '2026-05-05',
        'timezone' => 'UTC',
    ]))->assertRedirect(route('login'));
});

test('authenticated users can download a csv shift export', function () {
    $localizedUrlGenerator = app(LocalizedUrlGenerator::class);
    $user = User::factory()->create([
        'timezone' => 'UTC',
    ]);

    foreach (range(1, 5) as $weekday) {
        WorkSchedule::factory()->for($user)->create([
            'weekday' => $weekday,
            'effective_from' => '2026-05-01',
            'type' => 'total_time',
            'expected_minutes' => 480,
        ]);
    }

    Shift::factory()->for($user)->create([
        'started_at' => '2026-05-04 09:50:00',
        'ended_at' => '2026-05-04 19:40:00',
    ]);
    Shift::factory()->for($user)->create([
        'started_at' => '2026-05-05 09:00:00',
        'ended_at' => '2026-05-05 14:50:00',
    ]);

    $expectedContent = implode("\n", [
        'SEG,04/05,9:50h',
        'TER,05/05,5:50h',
        '',
        ',TOTAL,15:40h',
        ',Normais,13:50h',
        ',Extras,1:50h',
        ',Faltando,2:10h',
        '',
    ]);

    $this->actingAs($user)
        ->get($localizedUrlGenerator->url('shift-exports.download', 'pt-BR', [
            'type' => 'csv',
            'from' => '2026-05-04',
            'to' => '2026-05-05',
            'timezone' => 'UTC',
        ], absolute: false))
        ->assertOk()
        ->assertDownload('shifts-2026-05-04_2026-05-05.csv')
        ->assertStreamed()
        ->assertStreamedContent($expectedContent);
});

test('authenticated users can download an xlsx shift export', function () {
    $localizedUrlGenerator = app(LocalizedUrlGenerator::class);
    $user = User::factory()->create([
        'timezone' => 'UTC',
    ]);

    foreach (range(1, 5) as $weekday) {
        WorkSchedule::factory()->for($user)->create([
            'weekday' => $weekday,
            'effective_from' => '2026-05-01',
            'type' => 'total_time',
            'expected_minutes' => 480,
        ]);
    }

    Shift::factory()->for($user)->create([
        'started_at' => '2026-05-04 09:50:00',
        'ended_at' => '2026-05-04 19:40:00',
    ]);
    Shift::factory()->for($user)->create([
        'started_at' => '2026-05-05 09:00:00',
        'ended_at' => '2026-05-05 14:50:00',
    ]);

    $response = $this->actingAs($user)
        ->get($localizedUrlGenerator->url('shift-exports.download', 'pt-BR', [
            'type' => 'xlsx',
            'from' => '2026-05-04',
            'to' => '2026-05-05',
            'timezone' => 'UTC',
        ], absolute: false));

    $response
        ->assertOk()
        ->assertDownload('shifts-2026-05-04_2026-05-05.xlsx')
        ->assertStreamed();

    $path = tempnam(sys_get_temp_dir(), 'shift-export-');

    expect($path)->not->toBeFalse();

    if (! is_string($path)) {
        return;
    }

    file_put_contents($path, $response->streamedContent());

    $spreadsheet = (new XlsxReader)->load($path);
    $sheet = $spreadsheet->getActiveSheet();

    expect($sheet->getCell('A1')->getValue())->toBe('Exportação de horas')
        ->and($sheet->getCell('A5')->getValue())->toBe('Dia')
        ->and($sheet->getCell('A6')->getValue())->toBe('SEG')
        ->and($sheet->getCell('B6')->getValue())->toBe('04/05')
        ->and($sheet->getCell('C6')->getValue())->toBe('9:50h')
        ->and($sheet->getCell('B9')->getValue())->toBe('TOTAL')
        ->and($sheet->getCell('C9')->getValue())->toBe('15:40h');

    $spreadsheet->disconnectWorksheets();
    unlink($path);
});

test('authenticated users can download a pdf shift export', function () {
    $localizedUrlGenerator = app(LocalizedUrlGenerator::class);
    $user = User::factory()->create([
        'timezone' => 'UTC',
    ]);

    foreach (range(1, 5) as $weekday) {
        WorkSchedule::factory()->for($user)->create([
            'weekday' => $weekday,
            'effective_from' => '2026-05-01',
            'type' => 'total_time',
            'expected_minutes' => 480,
        ]);
    }

    Shift::factory()->for($user)->create([
        'started_at' => '2026-05-04 09:50:00',
        'ended_at' => '2026-05-04 19:40:00',
    ]);

    $response = $this->actingAs($user)
        ->get($localizedUrlGenerator->url('shift-exports.download', 'pt-BR', [
            'type' => 'pdf',
            'from' => '2026-05-04',
            'to' => '2026-05-04',
            'timezone' => 'UTC',
        ], absolute: false));

    $response
        ->assertOk()
        ->assertDownload('shifts-2026-05-04_2026-05-04.pdf')
        ->assertStreamed()
        ->assertHeader('content-type', 'application/pdf');

    expect($response->streamedContent())->toStartWith('%PDF-');
});

test('shift export rejects ranges longer than six months', function () {
    $user = User::factory()->create([
        'timezone' => 'UTC',
    ]);

    $this->actingAs($user)
        ->from(route('history'))
        ->get(route('shift-exports.download', [
            'type' => 'csv',
            'from' => '2026-01-01',
            'to' => '2026-08-01',
            'timezone' => 'UTC',
        ]))
        ->assertRedirect(route('history'))
        ->assertSessionHasErrors('to');
});
