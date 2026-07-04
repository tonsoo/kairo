<?php

declare(strict_types=1);

namespace App\Support\Metadata;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @implements Arrayable<string, string|null>
 */
readonly class TwitterMeta implements Arrayable, JsonSerializable
{
    public function __construct(
        public string $card,
        public string $title,
        public ?string $description,
    ) {}

    /**
     * @return array{card: string, title: string, description: string|null}
     */
    public function toArray(): array
    {
        return [
            'card' => $this->card,
            'title' => $this->title,
            'description' => $this->description,
        ];
    }

    /**
     * @return array{card: string, title: string, description: string|null}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
