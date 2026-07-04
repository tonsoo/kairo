<?php

declare(strict_types=1);

namespace App\Support\Metadata;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @implements Arrayable<string, mixed>
 */
readonly class OpenGraphMeta implements Arrayable, JsonSerializable
{
    /**
     * @param  array<int, string>  $alternateLocales
     */
    public function __construct(
        public string $type,
        public string $siteName,
        public string $title,
        public ?string $description,
        public string $url,
        public string $locale,
        public array $alternateLocales,
    ) {}

    /**
     * @return array{
     *     type: string,
     *     siteName: string,
     *     title: string,
     *     description: string|null,
     *     url: string,
     *     locale: string,
     *     alternateLocales: array<int, string>
     * }
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'siteName' => $this->siteName,
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url,
            'locale' => $this->locale,
            'alternateLocales' => $this->alternateLocales,
        ];
    }

    /**
     * @return array{
     *     type: string,
     *     siteName: string,
     *     title: string,
     *     description: string|null,
     *     url: string,
     *     locale: string,
     *     alternateLocales: array<int, string>
     * }
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
