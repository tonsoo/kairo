<?php

declare(strict_types=1);

namespace App\Support\Metadata;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @implements Arrayable<string, string>
 */
readonly class AlternateLink implements Arrayable, JsonSerializable
{
    public function __construct(
        public string $locale,
        public string $url,
    ) {}

    /**
     * @return array{locale: string, url: string}
     */
    public function toArray(): array
    {
        return [
            'locale' => $this->locale,
            'url' => $this->url,
        ];
    }

    /**
     * @return array{locale: string, url: string}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
