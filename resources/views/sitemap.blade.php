@php echo '<?xml version="1.0" encoding="UTF-8"?>'; @endphp
@php echo '<?xml-stylesheet type="text/css" href="https://www.xml-sitemaps.com/css/sitemap.css"?>'; @endphp
<urlset
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:fo="http://www.w3.org/1999/XSL/Format"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
    xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
    <!-- created with Free Online Sitemap Generator www.xml-sitemaps.com -->


    @foreach ($urls as $url)
        <url>
            <loc>{{ $url['loc'] }}</loc>
            <lastmod>{{ $url['lastmod'] }}</lastmod>
            @foreach ($url['alternates'] as $alternate)
                <xhtml:link
                    rel="alternate"
                    hreflang="{{ $alternate['locale'] }}"
                    href="{{ $alternate['url'] }}"
                />
            @endforeach
        </url>
    @endforeach

</urlset>
