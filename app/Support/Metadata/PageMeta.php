<?php

declare(strict_types=1);

namespace App\Support\Metadata;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @implements Arrayable<string, mixed>
 */
readonly class PageMeta implements Arrayable, JsonSerializable
{
    /**
     * @param  array<int, AlternateLink>  $alternates
     */
    public function __construct(
        public string $title,
        public ?string $description,
        public string $robots,
        public string $applicationName,
        public string $canonical,
        public array $alternates,
        public OpenGraphMeta $openGraph,
        public TwitterMeta $twitter,
        public ?string $structuredData,
    ) {}

    /**
     * @return array{
     *     title: string,
     *     description: string|null,
     *     robots: string,
     *     applicationName: string,
     *     canonical: string,
     *     alternates: array<int, array{locale: string, url: string}>,
     *     openGraph: array{
     *         type: string,
     *         siteName: string,
     *         title: string,
     *         description: string|null,
     *         url: string,
     *         locale: string,
     *         alternateLocales: array<int, string>
     *     },
     *     twitter: array{card: string, title: string, description: string|null},
     *     structuredData: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'robots' => $this->robots,
            'applicationName' => $this->applicationName,
            'canonical' => $this->canonical,
            'alternates' => array_map(
                static fn (AlternateLink $alternate): array => $alternate->toArray(),
                $this->alternates,
            ),
            'openGraph' => $this->openGraph->toArray(),
            'twitter' => $this->twitter->toArray(),
            'structuredData' => $this->structuredData,
        ];
    }

    /**
     * @return array{
     *     title: string,
     *     description: string|null,
     *     robots: string,
     *     applicationName: string,
     *     canonical: string,
     *     alternates: array<int, array{locale: string, url: string}>,
     *     openGraph: array{
     *         type: string,
     *         siteName: string,
     *         title: string,
     *         description: string|null,
     *         url: string,
     *         locale: string,
     *         alternateLocales: array<int, string>
     *     },
     *     twitter: array{card: string, title: string, description: string|null},
     *     structuredData: string|null
     * }
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
