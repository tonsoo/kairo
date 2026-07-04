<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            $meta = is_array($page['props']['meta'] ?? null) ? $page['props']['meta'] : null;
        @endphp

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ data_get($meta, 'title', config('app.name', 'Laravel')) }}</title>
            @if (($description = data_get($meta, 'description')) !== null)
                <meta head-key="description" name="description" content="{{ $description }}">
            @endif
            <meta head-key="robots" name="robots" content="{{ data_get($meta, 'robots', 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1') }}">
            <meta head-key="application-name" name="application-name" content="{{ data_get($meta, 'applicationName', config('app.name', 'Laravel')) }}">
            <link head-key="canonical" rel="canonical" href="{{ data_get($meta, 'canonical', url()->current()) }}">
            @foreach (data_get($meta, 'alternates', []) as $alternate)
                <link
                    head-key="alternate-{{ $alternate['locale'] }}"
                    rel="alternate"
                    hreflang="{{ $alternate['locale'] }}"
                    href="{{ $alternate['url'] }}"
                >
            @endforeach
            <meta head-key="og-type" property="og:type" content="{{ data_get($meta, 'openGraph.type', 'website') }}">
            <meta head-key="og-site-name" property="og:site_name" content="{{ data_get($meta, 'openGraph.siteName', config('app.name', 'Laravel')) }}">
            <meta head-key="og-title" property="og:title" content="{{ data_get($meta, 'openGraph.title', data_get($meta, 'title', config('app.name', 'Laravel'))) }}">
            @if (($openGraphDescription = data_get($meta, 'openGraph.description')) !== null)
                <meta head-key="og-description" property="og:description" content="{{ $openGraphDescription }}">
            @endif
            <meta head-key="og-url" property="og:url" content="{{ data_get($meta, 'openGraph.url', url()->current()) }}">
            <meta head-key="og-locale" property="og:locale" content="{{ data_get($meta, 'openGraph.locale', str_replace('-', '_', app()->getLocale())) }}">
            @foreach (data_get($meta, 'openGraph.alternateLocales', []) as $alternateLocale)
                <meta
                    head-key="og-locale-alternate-{{ $alternateLocale }}"
                    property="og:locale:alternate"
                    content="{{ $alternateLocale }}"
                >
            @endforeach
            <meta head-key="twitter-card" name="twitter:card" content="{{ data_get($meta, 'twitter.card', 'summary') }}">
            <meta head-key="twitter-title" name="twitter:title" content="{{ data_get($meta, 'twitter.title', data_get($meta, 'title', config('app.name', 'Laravel'))) }}">
            @if (($twitterDescription = data_get($meta, 'twitter.description')) !== null)
                <meta head-key="twitter-description" name="twitter:description" content="{{ $twitterDescription }}">
            @endif
            @if (($structuredData = data_get($meta, 'structuredData')) !== null)
                <script head-key="structured-data" type="application/ld+json">{!! $structuredData !!}</script>
            @endif
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
