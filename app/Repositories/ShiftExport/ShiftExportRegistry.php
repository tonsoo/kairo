<?php

declare(strict_types=1);

namespace App\Repositories\ShiftExport;

use App\Domain\Shift\Enums\ShiftExportType;
use LogicException;

final readonly class ShiftExportRegistry
{
    /**
     * @var array<string, ShiftExportRepository>
     */
    private array $repositories;

    public function __construct(ShiftExportRepository ...$repositories)
    {
        $mappedRepositories = [];

        foreach ($repositories as $repository) {
            $key = $repository->type()->value;

            if (array_key_exists($key, $mappedRepositories)) {
                throw new LogicException("Shift export repository [{$key}] is already registered.");
            }

            $mappedRepositories[$key] = $repository;
        }

        $this->repositories = $mappedRepositories;
    }

    /**
     * @return list<ShiftExportRepository>
     */
    public function all(): array
    {
        return array_values($this->repositories);
    }

    public function get(ShiftExportType $type): ShiftExportRepository
    {
        if (! array_key_exists($type->value, $this->repositories)) {
            throw new LogicException("Shift export repository [{$type->value}] is not registered.");
        }

        return $this->repositories[$type->value];
    }
}
